<?php
namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\User\Models\UserModel;

class LupaPassword extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function form()
    {
        return view('App\Modules\Auth\Views\lupa_password', [
            'title' => 'Lupa Password',
        ]);
    }

    public function kirim()
    {
        $rules = ['email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid.');
        }

        $email = $this->request->getPost('email');
        $user  = $this->userModel->where('email', $email)->where('deleted_at', null)->first();

        // Selalu tampilkan pesan sukses (security by obscurity)
        if (!$user) {
            return redirect()->to('/lupa-password')->with('success',
                'Jika email terdaftar, kami telah mengirimkan link reset password.');
        }

        $token = bin2hex(random_bytes(32));
        $db = \Config\Database::connect();

        // Hapus token lama
        $db->table('password_resets')->where('user_id', $user['id'])->delete();

        $db->table('password_resets')->insert([
            'user_id'    => $user['id'],
            'token'      => hash('sha256', $token), // simpan hash-nya
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'ip'         => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $link = base_url('reset-password/' . $token);
        $body = template_email('Reset Password', "
            <h3>Halo, {$user['nama']}</h3>
            <p>Kami menerima permintaan reset password untuk akun Anda.</p>
            <p>Klik tombol di bawah untuk mengatur password baru:</p>
            <p style='text-align:center;margin:24px 0;'>
                <a href='{$link}' style='background:#dc2626;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;'>
                    Reset Password
                </a>
            </p>
            <p>Link berlaku <strong>1 jam</strong>. Jika Anda tidak meminta reset, abaikan email ini.</p>
        ");
        kirim_email($user['email'], 'Reset Password — E-Court', $body, $user['id']);

        return redirect()->to('/lupa-password')->with('success',
            'Jika email terdaftar, kami telah mengirimkan link reset password.');
    }

    public function resetForm(string $token)
    {
        $db = \Config\Database::connect();
        $row = $db->table('password_resets')
            ->where('token', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to('/lupa-password')->with('error',
                'Link reset tidak valid atau sudah kedaluwarsa.');
        }

        return view('App\Modules\Auth\Views\reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    public function resetProses(string $token)
    {
        $rules = [
            'password'  => 'required|min_length[8]',
            'password2' => 'required|matches[password]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error',
                implode(' | ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();
        $row = $db->table('password_resets')
            ->where('token', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to('/lupa-password')->with('error',
                'Link reset tidak valid atau sudah kedaluwarsa.');
        }

        // Update password
        $this->userModel->update($row['user_id'], [
            'password'             => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'must_change_password' => 0,
        ]);

        // Tandai token used
        $db->table('password_resets')->where('id', $row['id'])->update([
            'used_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/login')->with('success',
            'Password berhasil diubah. Silakan login dengan password baru.');
    }
}