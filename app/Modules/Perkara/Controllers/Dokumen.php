<?php
namespace App\Modules\Perkara\Controllers;

use App\Controllers\BaseController;

class Dokumen extends BaseController
{
    /**
     * Download file via controller (file di writable/uploads)
     * Cek otorisasi sebelum kirim file
     */
    public function download(int $id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('dokumen')->where('id', $id)->get()->getRowArray();
        if (!$row) return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');

        // Cek otorisasi
        if (!$this->canAccess($row)) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengakses dokumen ini.');
        }

        $fullPath = WRITEPATH . 'uploads/' . $row['file_path'];
        if (!is_file($fullPath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        return $this->response->download($fullPath, null)
            ->setFileName($row['file_name']);
    }

    protected function canAccess(array $doc): bool
    {
        $role   = session()->get('primary_role');
        $userId = session()->get('user_id');

        // Admin, panitera, majelis: akses semua
        if (in_array($role, ['admin','panitera','majelis'], true)) return true;

        // Pengadu/teradu: hanya dokumen perkaranya & bukan private milik orang lain
        $db = \Config\Database::connect();
        $isPihak = $db->table('perkara_pihak')
            ->where('perkara_id', $doc['perkara_id'])
            ->where('user_id', $userId)
            ->countAllResults() > 0;

        if (!$isPihak) return false;

        // Dokumen private (kesimpulan) hanya untuk uploader
        if ($doc['is_private'] && $doc['uploader_id'] != $userId) return false;

        return true;
    }
}