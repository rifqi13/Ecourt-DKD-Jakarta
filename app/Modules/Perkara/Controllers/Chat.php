<?php
namespace App\Modules\Perkara\Controllers;

use App\Controllers\BaseController;

class Chat extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * List chat per perkara (AJAX polling)
     */
    public function list(int $perkaraId)
    {
        if (!$this->canAccess($perkaraId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $afterId = (int) $this->request->getGet('after_id');

        $builder = $this->db->table('chat_perkara c')
            ->select('c.*, u.nama, u.primary_role')
            ->join('users u', 'u.id = c.user_id')
            ->where('c.perkara_id', $perkaraId)
            ->where('c.deleted_at', null)
            ->orderBy('c.created_at', 'ASC');

        if ($afterId) $builder->where('c.id >', $afterId);

        $rows = $builder->get()->getResultArray();

        // Tandai sudah dibaca oleh user ini
        $userId = session()->get('user_id');
        foreach ($rows as &$r) {
            $r['is_mine'] = ($r['user_id'] == $userId);
            if (!$r['is_mine']) {
                $this->markRead($r['id'], $userId);
            }
            if ($r['file_path']) {
                $r['file_url'] = base_url('dokumen/download-chat/' . $r['id']);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    /**
     * Kirim pesan
     */
    public function kirim(int $perkaraId)
    {
        if (!$this->canAccess($perkaraId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $pesan = trim($this->request->getPost('pesan'));
        $file  = $this->request->getFile('file_lampiran');

        if (!$pesan && (!$file || !$file->isValid())) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pesan atau file wajib.']);
        }
        if (strlen($pesan) > 1000) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pesan maks 1000 karakter.']);
        }

        $filePath = null;
        $fileName = null;
        $fileSize = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'File maks 10 MB.']);
            }
            $allowedExt = ['pdf','doc','docx','jpg','jpeg','png','mp4','zip'];
            if (!in_array(strtolower($file->getExtension()), $allowedExt)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Format file tidak diizinkan.']);
            }
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/chat', $newName);
            $filePath = 'chat/' . $newName;
            $fileName = $file->getClientName();
            $fileSize = $file->getSize();
        }

        $this->db->table('chat_perkara')->insert([
            'perkara_id' => $perkaraId,
            'user_id'    => session()->get('user_id'),
            'pesan'      => $pesan ?: '',
            'file_path'  => $filePath,
            'file_name'  => $fileName,
            'file_size'  => $fileSize,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $id = $this->db->insertID();

        // Notifikasi ke pihak lain
        $this->notifPihakLain($perkaraId, $pesan ?: '[Lampiran]');

        return $this->response->setJSON([
            'status' => 'success',
            'id'     => $id,
            'message'=> 'Pesan terkirim.',
        ]);
    }

    public function hapus(int $id)
    {
        $row = $this->db->table('chat_perkara')->where('id', $id)->get()->getRowArray();
        if (!$row) return $this->response->setJSON(['status' => 'error', 'message' => 'Pesan tidak ditemukan.']);

        if ($row['user_id'] != session()->get('user_id') && !has_role('admin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya bisa hapus pesan sendiri.']);
        }

        $this->db->table('chat_perkara')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pesan dihapus.']);
    }

    public function downloadChatFile(int $id)
    {
        $row = $this->db->table('chat_perkara')->where('id', $id)->get()->getRowArray();
        if (!$row || !$row['file_path']) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }
        if (!$this->canAccess($row['perkara_id'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $fullPath = WRITEPATH . 'uploads/' . $row['file_path'];
        if (!is_file($fullPath)) return redirect()->back()->with('error', 'File hilang.');

        return $this->response->download($fullPath, null)->setFileName($row['file_name']);
    }

    public function unreadCount(int $perkaraId)
    {
        if (!$this->canAccess($perkaraId)) {
            return $this->response->setJSON(['count' => 0]);
        }
        $userId = session()->get('user_id');
        $count = $this->db->table('chat_perkara')
            ->where('perkara_id', $perkaraId)
            ->where('user_id !=', $userId)
            ->where('deleted_at', null)
            ->where('id NOT IN (SELECT chat_id FROM chat_read WHERE user_id = ' . (int)$userId . ')', null, false)
            ->countAllResults();

        return $this->response->setJSON(['count' => $count]);
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================
    protected function canAccess(int $perkaraId): bool
    {
        $role   = session()->get('primary_role');
        $userId = session()->get('user_id');

        if (in_array($role, ['admin','panitera','majelis'], true)) return true;

        return $this->db->table('perkara_pihak')
            ->where('perkara_id', $perkaraId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }

    protected function markRead(int $chatId, int $userId): void
    {
        $exists = $this->db->table('chat_read')
            ->where('chat_id', $chatId)->where('user_id', $userId)
            ->countAllResults() > 0;
        if (!$exists) {
            $this->db->table('chat_read')->insert([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    protected function notifPihakLain(int $perkaraId, string $preview): void
    {
        $userId = session()->get('user_id');
        $pihak = $this->db->table('perkara_pihak')
            ->where('perkara_id', $perkaraId)
            ->where('user_id !=', $userId)
            ->get()->getResultArray();

        // Tambah panitera & majelis
        $perkara = $this->db->table('perkara')->where('id', $perkaraId)->get()->getRowArray();
        $extra = [];
        if (!empty($perkara['panitera_id'])) $extra[] = $perkara['panitera_id'];
        if (!empty($perkara['majelis_hearing_id'])) $extra[] = $perkara['majelis_hearing_id'];

        $recipients = array_unique(array_merge(
            array_column($pihak, 'user_id'),
            $extra
        ));

        $sender = $this->db->table('users')->where('id', $userId)->get()->getRowArray();

        foreach ($recipients as $uid) {
            if ($uid == $userId) continue;
            $this->db->table('notifikasi')->insert([
                'user_id'    => $uid,
                'tipe'       => 'chat',
                'judul'      => 'Pesan Baru — Perkara #' . $perkaraId,
                'pesan'      => substr($preview, 0, 100),
                'link'       => '/perkara/show/' . $perkaraId . '#tab-chat',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}