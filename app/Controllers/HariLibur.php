<?php
namespace App\Modules\HariLibur\Controllers;

use App\Controllers\BaseController;

class HariLibur extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $data['libur'] = $db->table('hari_libur')
            ->orderBy('tanggal', 'DESC')
            ->get()->getResultArray();

        return view('App\Modules\HariLibur\Views\index', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->table('hari_libur')->insert([
            'tanggal'    => $this->request->getPost('tanggal'),
            'keterangan' => $this->request->getPost('keterangan'),
            'sumber'     => $this->request->getPost('sumber'),
            'is_kerja'   => (int)$this->request->getPost('is_kerja'),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Clear cache biar helper langsung update
        cache()->delete("jam_kerja_1");
        cache()->delete("jam_kerja_2");
        // ... atau cache()->clean()

        return redirect()->to('/hari-libur')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }
}