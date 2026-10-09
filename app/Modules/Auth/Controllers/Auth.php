<?php
namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('App\Modules\Auth\Views\login');
    }

    public function login()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required|min_length[6]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Input tidak valid.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Login by username atau email
        $user = $this->db->table('users')
            ->groupStart()
                ->where('username', $username)
                ->orWhere('email', $username)
            ->groupEnd()
            ->where('deleted_at', null)
            ->get()->getRowArray();

        if (!$user || !password_verify($password, $user['password'])) {
            // Log aktivitas gagal
            $this->logAktivitas(null, 'login_failed', 'auth', null, "Username: {$username}");
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        if ($user['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'Akun Anda belum aktif. Hubungi panitera.');
        }

        // Set session
        $sessionData = [
            'user_id'              => $user['id'],
            'nama'                 => $user['nama'],
            'username'             => $user['username'],
            'email'                => $user['email'],
            'primary_role'         => $user['primary_role'],
            'is_advokat'           => $user['is_advokat'],
            'must_change_password' => $user['must_change_password'],
            'isLoggedIn'           => true,
        ];
        session()->set($sessionData);

        // Update last_login
        $this->db->table('users')->where('id', $user['id'])->update([
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        $this->logAktivitas($user['id'], 'login', 'auth', null, 'Login berhasil');

        // Redirect wajib ganti password
        if ($user['must_change_password']) {
            return redirect()->to('/ganti-password')
                ->with('info', 'Silakan ganti password default Anda terlebih dahulu.');
        }

        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['nama']);
    }

    public function logout()
    {
        $this->logAktivitas(session()->get('user_id'), 'logout', 'auth');
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    protected function logAktivitas($userId, $aksi, $modul, $refId = null, $deskripsi = null)
    {
        $this->db->table('log_aktivitas')->insert([
            'user_id'    => $userId,
            'aksi'       => $aksi,
            'modul'      => $modul,
            'ref_id'     => $refId,
            'deskripsi'  => $deskripsi,
            'ip'         => $this->request->getIPAddress(),
            'user_agent' => substr((string)$this->request->getUserAgent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}