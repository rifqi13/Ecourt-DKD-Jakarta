<?php
namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\User\Models\UserModel;

class Register extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function form()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('App\Modules\Auth\Views\register', [
            'title' => 'Daftar Pengadu',
        ]);
    }

    public function store()
    {
        $rules = [
            'nama'      => 'required|min_length[3]|max_length[150]',
            'email'     => 'required|valid_email|is_unique[users.email]',
            'username'  => 'required|min_length[4]|max_length[100]|alpha_numeric|is_unique[users.username]',
            'password'  => 'required|min_length[8]',
            'password2' => 'required|matches[password]',
            'instansi'  => 'required|max_length[200]',
            'no_hp'     => 'required|max_length[20]',
            'alamat'    => 'required',
            'setuju'    => 'required',
        ];

        $messages = [
            'email'    => ['is_unique' => 'Email sudah terdaftar.'],
            'username' => ['is_unique' => 'Username sudah digunakan.'],
            'password2'=> ['matches'   => 'Konfirmasi password tidak sama.'],
            'setuju'   => ['required'  => 'Anda harus menyetujui pernyataan e-Court.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' | ', $this->validator->getErrors()));
        }

        $wajibVerif = (bool) setting('wajib_verifikasi_email', false);
        $token      = $wajibVerif ? bin2hex(random_bytes(32)) : null;

        $userId = $this->userModel->insert([
            'nama'                 => $this->request->getPost('nama'),
            'email'                => $this->request->getPost('email'),
            'username'             => strtolower($this->request->getPost('username')),
            'password'             => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'primary_role'         => 'pengadu',
            'instansi'             => $this->request->getPost('instansi'),
            'no_hp'                => $this->request->getPost('no_hp'),
            'alamat'               => $this->request->getPost('alamat'),
            'must_change_password' => 0,
            'setuju_ecourt'        => 1,
            'tgl_setuju'           => date('Y-m-d H:i:s'),
            'status'               => $wajibVerif ? 'pending' : 'aktif',
            'email_verified_at'    => $wajibVerif ? null : date('Y-m-d H:i:s'),
            'email_verification_token' => $token,
        ], true);

        if ($wajibVerif && $token) {
            $link = base_url('verify-email/' . $token);
            $body = template_email('Verifikasi Email', "
                <h3>Halo, {$this->request->getPost('nama')}</h3>
                <p>Terima kasih telah mendaftar di E-Court Elektronik.</p>
                <p>Silakan klik tombol di bawah untuk memverifikasi email Anda:</p>
                <p style='text-align:center;margin:24px 0;'>
                    <a href='{$link}' style='background:#1e40af;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;'>
                        Verifikasi Email
                    </a>
                </p>
                <p>Atau salin link berikut: <br><small>{$link}</small></p>
                <p>Link berlaku 24 jam.</p>
            ");
            kirim_email($this->request->getPost('email'), 'Verifikasi Email — E-Court', $body, $userId);

            return redirect()->to('/login')->with('success',
                'Registrasi berhasil. Cek email Anda untuk verifikasi.');
        }

        return redirect()->to('/login')->with('success',
            'Registrasi berhasil. Silakan login.');
    }

    /**
     * Verifikasi email via link
     */
    public function verifyEmail(string $token)
    {
        $user = $this->userModel->where('email_verification_token', $token)->first();
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }

        $this->userModel->update($user['id'], [
            'email_verified_at'         => date('Y-m-d H:i:s'),
            'email_verification_token'  => null,
            'status'                    => 'aktif',
        ]);

        return redirect()->to('/login')->with('success',
            'Email berhasil diverifikasi. Silakan login.');
    }
}