<?php
namespace App\Modules\Pengaturan\Controllers;

use App\Controllers\BaseController;

class Pengaturan extends BaseController
{
    public function index()
    {
        return view('App\Modules\Pengaturan\Views\index', [
            'title'  => 'Pengaturan Sistem',
            'items'  => [
                'wajib_verifikasi_email' => setting('wajib_verifikasi_email', false),
                'nama_kantor'            => setting('nama_kantor', ''),
                'email_admin'            => setting('email_admin', ''),
            ],
        ]);
    }

    public function save()
    {
        set_setting('wajib_verifikasi_email',
            $this->request->getPost('wajib_verifikasi_email') ? 1 : 0, 'bool');
        set_setting('nama_kantor', $this->request->getPost('nama_kantor'), 'string');
        set_setting('email_admin', $this->request->getPost('email_admin'), 'string');

        return redirect()->to('/pengaturan')->with('success', 'Pengaturan disimpan.');
    }
}