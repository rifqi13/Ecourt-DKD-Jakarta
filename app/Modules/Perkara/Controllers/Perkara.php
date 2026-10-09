<?php
namespace App\Modules\Perkara\Controllers;

use App\Controllers\BaseController;
use App\Modules\Perkara\Models\PerkaraModel;
use App\Modules\Perkara\Models\PerkaraPihakModel;
use App\Modules\User\Models\UserModel;
use App\Modules\SuratMasuk\Models\SuratMasukModel;

class Perkara extends BaseController
{
    protected PerkaraModel $perkaraModel;
    protected PerkaraPihakModel $pihakModel;
    protected UserModel $userModel;
    protected SuratMasukModel $smModel;

    public function __construct()
    {
        $this->perkaraModel = new PerkaraModel();
        $this->pihakModel   = new PerkaraPihakModel();
        $this->userModel    = new UserModel();
        $this->smModel      = new SuratMasukModel();
    }

    public function index()
    {
        $role   = session()->get('primary_role');
        $userId = session()->get('user_id');
        $filter = $this->request->getGet('status');

        $builder = $this->perkaraModel
            ->select('p.*, jp.nama AS nama_jenis,
                      pg.nama AS nama_pengadu, td.nama AS nama_teradu,
                      u.nama AS nama_panitera')
            ->from('perkara p')
            ->join('jenis_perkara jp', 'jp.id = p.jenis_perkara_id', 'left')
            ->join('users u', 'u.id = p.panitera_id', 'left')
            ->join('perkara_pihak ppg', 'ppg.perkara_id = p.id AND ppg.tipe = "pengadu"', 'left')
            ->join('users pg', 'pg.id = ppg.user_id', 'left')
            ->join('perkara_pihak ppt', 'ppt.perkara_id = p.id AND ppt.tipe = "teradu"', 'left')
            ->join('users td', 'td.id = ppt.user_id', 'left')
            ->where('p.deleted_at', null)
            ->orderBy('p.created_at', 'DESC');

        // Filter berdasarkan role
        if ($role === 'pengadu') {
            $builder->where('ppg.user_id', $userId);
        } elseif ($role === 'teradu') {
            $builder->where('ppt.user_id', $userId);
        } elseif ($role === 'majelis') {
            $builder->groupStart()
                ->where('p.majelis_hearing_id', $userId)
                ->orWhere('p.id IN (SELECT perkara_id FROM perkara_majelis WHERE user_id = ' . (int)$userId . ' AND is_aktif = 1)', null, false)
            ->groupEnd();
        }

        if ($filter) $builder->where('p.status', $filter);

        return view('App\Modules\Perkara\Views\index', [
            'title'  => 'Daftar Perkara',
            'list'   => $builder->paginate(15),
            'pager'  => $this->perkaraModel->pager,
            'role'   => $role,
            'filter' => $filter,
        ]);
    }

    // ============================================================
    // WIZARD: Buat Pengaduan (4 step)
    // ============================================================
    public function create()
    {
        $draftId = session()->get('perkara_draft_id');

        // Bersihkan draft lama yang sudah > 24 jam
        if ($draftId) {
            $draft = $this->perkaraModel->find($draftId);
            if (!$draft || $draft['status'] !== 'draft') {
                session()->remove('perkara_draft_id');
                $draftId = null;
            }
        }

        return view('App\Modules\Perkara\Views\wizard', [
            'title'   => 'Buat Pengaduan Baru',
            'draftId' => $draftId,
        ]);
    }

    // Step 1: Simpan identitas pengadu + teradu
    public function storeIdentitas()
    {
        $rules = [
            // Pengadu
            'pengadu_nama'      => 'required|max_length[150]',
            'pengadu_instansi'  => 'required|max_length[200]',
            'pengadu_no_hp'     => 'required|max_length[20]',
            'pengadu_alamat'    => 'required',
            'pengadu_email'     => 'required|valid_email|max_length[150]',
            // Teradu
            'teradu_nama'       => 'required|max_length[150]',
            'teradu_alamat'     => 'required',
            'teradu_nia'        => 'permit_empty|valid_nia',
            'teradu_email'      => 'permit_empty|valid_email|max_length[150]',
            // Perkara
            'perihal'           => 'required|max_length[255]',
            'jenis_perkara_id'  => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $userId = session()->get('user_id');
            $draftId = $this->request->getPost('draft_id');

            // Buat atau update draft perkara
            if ($draftId) {
                $perkaraId = (int)$draftId;
                $this->perkaraModel->update($perkaraId, [
                    'jenis_perkara_id' => $this->request->getPost('jenis_perkara_id'),
                    'jenis_perkara'    => 'DKD',
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);
            } else {
                $perkaraId = $this->perkaraModel->insert([
                    'jenis_perkara_id' => $this->request->getPost('jenis_perkara_id'),
                    'jenis_perkara'    => 'DKD',
                    'status'           => 'draft',
                    'nilai_perkara'    => 5000000.00,
                    'tgl_pengaduan'    => date('Y-m-d H:i:s'),
                    'created_by'       => $userId,
                ], true);
                session()->set('perkara_draft_id', $perkaraId);
            }

            // Simpan/hapus pihak lama, lalu insert ulang (simple upsert)
            $this->pihakModel->where('perkara_id', $perkaraId)->delete();

            // === Pengadu ===
            // Pastikan pengadu = user yang login, atau buat baru kalau panitera yang input
            $pengaduUserId = $userId;
            if (session()->get('primary_role') !== 'pengadu') {
                // Panitera input atas nama pengadu → cek/create user pengadu
                $pengaduUser = $this->userModel->where('email', $this->request->getPost('pengadu_email'))->first();
                if ($pengaduUser) {
                    $pengaduUserId = $pengaduUser['id'];
                } else {
                    $pwd = 'Ecourt' . random_int(1000, 9999);
                    $pengaduUserId = $this->userModel->insert([
                        'nama'                 => $this->request->getPost('pengadu_nama'),
                        'email'                => $this->request->getPost('pengadu_email'),
                        'username'             => $this->userModel->generateUsernameTeradu(), // reuse generator
                        'password'             => password_hash($pwd, PASSWORD_BCRYPT),
                        'primary_role'         => 'pengadu',
                        'instansi'             => $this->request->getPost('pengadu_instansi'),
                        'no_hp'                => $this->request->getPost('pengadu_no_hp'),
                        'alamat'               => $this->request->getPost('pengadu_alamat'),
                        'must_change_password' => 1,
                        'status'               => 'aktif',
                    ], true);
                }
            }

            $this->pihakModel->insert([
                'perkara_id' => $perkaraId,
                'user_id'    => $pengaduUserId,
                'tipe'       => 'pengadu',
                'instansi'   => $this->request->getPost('pengadu_instansi'),
                'alamat'     => $this->request->getPost('pengadu_alamat'),
                'no_hp'      => $this->request->getPost('pengadu_no_hp'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // === Teradu (auto-create akun) ===
            $teraduAkun = createAkunTeradu([
                'nama'      => $this->request->getPost('teradu_nama'),
                'email'     => $this->request->getPost('teradu_email'),
                'nia'       => $this->request->getPost('teradu_nia'),
                'alamat'    => $this->request->getPost('teradu_alamat'),
                'instansi'  => $this->request->getPost('teradu_instansi'),
                'no_hp'     => $this->request->getPost('teradu_no_hp'),
                'no_peradi' => $this->request->getPost('teradu_no_peradi'),
            ]);

            $this->pihakModel->insert([
                'perkara_id' => $perkaraId,
                'user_id'    => $teraduAkun['user']['id'],
                'tipe'       => 'teradu',
                'instansi'   => $this->request->getPost('teradu_instansi'),
                'alamat'     => $this->request->getPost('teradu_alamat'),
                'no_hp'      => $this->request->getPost('teradu_no_hp'),
                'nia'        => $this->request->getPost('teradu_nia'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Update perihal di perkara
            $this->perkaraModel->update($perkaraId, [
                'kode_input' => $this->request->getPost('perihal'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }

            return $this->response->setJSON([
                'status'          => 'success',
                'perkara_id'      => $perkaraId,
                'teradu_akun'     => [
                    'username' => $teraduAkun['user']['username'],
                    'password' => $teraduAkun['password_plain'],
                    'is_new'   => $teraduAkun['is_new'],
                ],
                'message'         => 'Identitas berhasil disimpan.',
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'storeIdentitas: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ]);
        }
    }

    // Step 2: Upload file pengaduan
    public function uploadPengaduan()
    {
        $draftId = session()->get('perkara_draft_id');
        if (!$draftId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Draft tidak ditemukan.']);
        }

        $file = $this->request->getFile('file_pengaduan');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid.']);
        }

        if ($file->getSize() > 20 * 1024 * 1024) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Maksimal 20 MB.']);
        }
        if (!in_array(strtolower($file->getExtension()), ['pdf', 'doc', 'docx'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format: PDF/DOC/DOCX.']);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/pengaduan', $newName);

        $db = \Config\Database::connect();
        $db->table('dokumen')->insert([
            'perkara_id'  => $draftId,
            'uploader_id' => session()->get('user_id'),
            'kategori'    => 'pengaduan',
            'tipe_pihak'  => 'pengadu',
            'file_path'   => 'pengaduan/' . $newName,
            'file_name'   => $file->getClientName(),
            'file_type'   => $file->getClientMimeType(),
            'file_size'   => $file->getSize(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'File pengaduan berhasil diupload.',
        ]);
    }

    // Step 3: Upload bukti (boleh banyak)
    public function uploadBukti()
    {
        $draftId = session()->get('perkara_draft_id');
        if (!$draftId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Draft tidak ditemukan.']);
        }

        $judul = $this->request->getPost('judul');
        $file  = $this->request->getFile('file_bukti');

        if (!$judul || !$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Judul dan file wajib.']);
        }

        $ext = strtolower($file->getExtension());
        $allowedExt = ['pdf','doc','docx','jpg','jpeg','png','mp4'];
        if (!in_array($ext, $allowedExt)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format tidak diizinkan.']);
        }

        $maxSize = $ext === 'mp4' ? 100 * 1024 * 1024 : 20 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ukuran maks ' . ($maxSize / 1024 / 1024) . ' MB.',
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/bukti', $newName);

        $db = \Config\Database::connect();

        // Hitung nomor urut bukti pengadu (P-1, P-2, ...)
        $count = $db->table('dokumen')
            ->where('perkara_id', $draftId)
            ->where('kategori', 'bukti')
            ->where('tipe_pihak', 'pengadu')
            ->countAllResults();
        $kodeBukti = 'P-' . ($count + 1);

        $id = $db->table('dokumen')->insert([
            'perkara_id'  => $draftId,
            'uploader_id' => session()->get('user_id'),
            'kategori'    => 'bukti',
            'tipe_pihak'  => 'pengadu',
            'judul'       => '[' . $kodeBukti . '] ' . $judul,
            'file_path'   => 'bukti/' . $newName,
            'file_name'   => $file->getClientName(),
            'file_type'   => $file->getClientMimeType(),
            'file_size'   => $file->getSize(),
            'created_at'  => date('Y-m-d H:i:s'),
        ], true);

        return $this->response->setJSON([
            'status'  => 'success',
            'kode'    => $kodeBukti,
            'id'      => $id,
            'message' => 'Bukti ' . $kodeBukti . ' berhasil diupload.',
        ]);
    }

    // Hapus bukti (sebelum submit)
    public function hapusBukti(int $dokumenId)
    {
        $draftId = session()->get('perkara_draft_id');
        $db = \Config\Database::connect();
        $row = $db->table('dokumen')
            ->where('id', $dokumenId)
            ->where('perkara_id', $draftId)
            ->where('kategori', 'bukti')
            ->get()->getRowArray();

        if (!$row) return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ditemukan.']);

        // Hapus file fisik
        $fullPath = WRITEPATH . 'uploads/' . $row['file_path'];
        if (is_file($fullPath)) @unlink($fullPath);

        $db->table('dokumen')->where('id', $dokumenId)->delete();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Bukti dihapus.']);
    }

    // List bukti di draft (untuk render di step 3)
    public function listBukti()
    {
        $draftId = session()->get('perkara_draft_id');
        if (!$draftId) return $this->response->setJSON(['status' => 'success', 'data' => []]);

        $db = \Config\Database::connect();
        $rows = $db->table('dokumen')
            ->where('perkara_id', $draftId)
            ->where('kategori', 'bukti')
            ->where('tipe_pihak', 'pengadu')
            ->orderBy('id', 'ASC')->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    // Step 4: Submit final
    public function submit()
    {
        $draftId = session()->get('perkara_draft_id');
        if (!$draftId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Draft tidak ditemukan.']);
        }

        $db = \Config\Database::connect();

        // Validasi minimal: file pengaduan harus ada
        $adaPengaduan = $db->table('dokumen')
            ->where('perkara_id', $draftId)
            ->where('kategori', 'pengaduan')
            ->countAllResults() > 0;

        if (!$adaPengaduan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File pengaduan wajib diupload.',
            ]);
        }

        // === Generate surat masuk (untuk pencatatan) ===
        $noAgenda = $this->smModel->generateNoAgenda();
        $smId = $this->smModel->insert([
            'no_agenda'    => $noAgenda,
            'no_disposisi' => $noAgenda,
            'tgl_terima'   => date('Y-m-d'),
            'asal_surat'   => 'Pengadu',
            'perihal'      => 'Pengaduan Perkara DKD',
            'status'       => 'jadi_perkara',
            'jenis'        => 'online',
            'created_by'   => session()->get('user_id'),
            'pengadu_id'   => session()->get('user_id'),
        ], true);

        // === Update perkara ===
        $kodeInput = str_pad((string) $this->smModel->getInsertID(), 4, '0', STR_PAD_LEFT) . '-' . date('Y');
        $this->perkaraModel->update($draftId, [
            'surat_masuk_id' => $smId,
            'kode_input'     => $kodeInput,
            'status'         => 'menunggu_verifikasi',
            'tgl_pengaduan'  => date('Y-m-d H:i:s'),
        ]);

        // === Notifikasi ke panitera ===
        $panitera = $this->userModel->where('primary_role', 'panitera')->where('status', 'aktif')->findAll();
        foreach ($panitera as $p) {
            $db->table('notifikasi')->insert([
                'user_id'    => $p['id'],
                'tipe'       => 'verifikasi_perkara',
                'judul'      => 'Pengaduan Baru',
                'pesan'      => 'Ada pengaduan baru menunggu verifikasi: #' . $draftId,
                'link'       => '/perkara/verifikasi/' . $draftId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Bersihkan draft session
        session()->remove('perkara_draft_id');

        return $this->response->setJSON([
            'status'     => 'success',
            'perkara_id' => $draftId,
            'message'    => 'Pengaduan berhasil dikirim. Menunggu verifikasi panitera.',
        ]);
    }

    // ============================================================
    // DETAIL PERKARA
    // ============================================================
    public function show(int $id)
    {
        $perkara = $this->perkaraModel->getDetail($id);
        if (!$perkara) {
            return redirect()->to('/perkara')->with('error', 'Perkara tidak ditemukan.');
        }

        // Cek otorisasi: pengadu/teradu hanya bisa lihat perkaranya sendiri
        $role   = session()->get('primary_role');
        $userId = session()->get('user_id');
        if (in_array($role, ['pengadu', 'teradu'])) {
            $isPihak = ($perkara['pengadu']['user_id'] ?? null) == $userId
                    || ($perkara['teradu']['user_id']  ?? null) == $userId;
            if (!$isPihak) {
                return redirect()->to('/perkara')->with('error', 'Anda tidak berhak melihat perkara ini.');
            }
        }

        // Ambil dokumen berdasarkan role
        $db = \Config\Database::connect();
        $dokumenQuery = $db->table('dokumen')->where('perkara_id', $id);

        // Kesimpulan: private — hanya own
        if (in_array($role, ['pengadu', 'teradu'])) {
            $dokumenQuery->groupStart()
                ->where('is_private', 0)
                ->orGroupStart()
                    ->where('is_private', 1)
                    ->where('uploader_id', $userId)
                ->groupEnd()
            ->groupEnd();
        }

        $dokumen = $dokumenQuery->orderBy('created_at', 'ASC')->get()->getResultArray();

        return view('App\Modules\Perkara\Views\detail', [
            'title'   => 'Detail Perkara',
            'perkara' => $perkara,
            'dokumen' => $dokumen,
            'role'    => $role,
        ]);
    }

    // ============================================================
    // VERIFIKASI PANITERA (hanya cek kelengkapan)
    // ============================================================
    public function verifikasiForm(int $id)
    {
        $perkara = $this->perkaraModel->getDetail($id);
        if (!$perkara) return redirect()->to('/perkara');

        $db = \Config\Database::connect();
        $dokumen = $db->table('dokumen')->where('perkara_id', $id)->get()->getResultArray();

        return view('App\Modules\Perkara\Views\verifikasi', [
            'title'   => 'Verifikasi Pengaduan',
            'perkara' => $perkara,
            'dokumen' => $dokumen,
        ]);
    }

    public function verifikasi(int $id)
    {
        $perkara = $this->perkaraModel->find($id);
        if (!$perkara || $perkara['status'] !== 'menunggu_verifikasi') {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        // Generate No Register Sementara: 030/DKD/DKI/1729-2025
        $noReg = $this->perkaraModel->generateNoRegisterSementara('DKD', $perkara['kode_input']);

        $this->perkaraModel->update($id, [
            'no_register_sementara' => $noReg,
            'status'                => 'register_sementara',
            'catatan_verifikasi'    => $this->request->getPost('catatan'),
            'verified_by'           => session()->get('user_id'),
            'verified_at'           => date('Y-m-d H:i:s'),
            'panitera_id'           => session()->get('user_id'),
        ]);

        // Notifikasi ke pengadu
        $db = \Config\Database::connect();
        $pengadu = $db->table('perkara_pihak pp')
            ->join('users u', 'u.id = pp.user_id')
            ->where('pp.perkara_id', $id)
            ->where('pp.tipe', 'pengadu')
            ->get()->getRowArray();

        if ($pengadu) {
            $db->table('notifikasi')->insert([
                'user_id'    => $pengadu['id'],
                'tipe'       => 'register_sementara',
                'judul'      => 'Pengaduan Terverifikasi',
                'pesan'      => 'Perkara Anda telah diverifikasi. No. Register Sementara: ' . $noReg,
                'link'       => '/perkara/show/' . $id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/perkara/show/' . $id)
            ->with('success', 'Verifikasi berhasil. No. Register Sementara: ' . $noReg);
    }
}