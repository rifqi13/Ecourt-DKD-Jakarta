<?php
namespace App\Modules\User\Controllers;

use App\Controllers\BaseController;
use App\Modules\User\Models\UserModel;

class User extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $role   = session()->get('primary_role');
        $filter = $this->request->getGet('role');
        $status = $this->request->getGet('status');
        $q      = $this->request->getGet('q');

        $builder = $this->userModel->where('deleted_at', null)->orderBy('created_at', 'DESC');

        // Panitera hanya bisa lihat pengadu & teradu
        if ($role === 'panitera') {
            $builder->whereIn('primary_role', ['pengadu','teradu']);
        }

        if ($filter) $builder->where('primary_role', $filter);
        if ($status) $builder->where('status', $status);
        if ($q) {
            $builder->groupStart()
                ->like('nama', $q)->orLike('email', $q)
                ->orLike('username', $q)->orLike('nia', $q)
            ->groupEnd();
        }

        return view('App\Modules\User\Views\index', [
            'title'  => 'Kelola User',
            'list'   => $builder->paginate(20),
            'pager'  => $this->userModel->pager,
            'role'   => $role,
            'filter' => $filter,
            'status' => $status,
            'q'      => $q,
        ]);
    }

    public function create()
    {
        $role = session()->get('primary_role');
        $allowedRoles = $role === 'admin'
            ? ['admin','panitera','majelis','pengadu','teradu']
            : ['pengadu','teradu'];

        return view('App\Modules\User\Views\form', [
            'title'        => 'Tambah User',
            'allowedRoles' => $allowedRoles,
            'user'         => null,
        ]);
    }

    public function store()
    {
        $role = session()->get('primary_role');
        $targetRole = $this->request->getPost('primary_role');

        // Panitera hanya boleh buat pengadu & teradu
        if ($role === 'panitera' && !in_array($targetRole, ['pengadu','teradu'])) {
            return redirect()->back()->with('error', 'Anda tidak berhak membuat role ini.');
        }

        $rules = [
            'nama'         => 'required|min_length[3]|max_length[150]',
            'email'        => 'required|valid_email|is_unique[users.email]',
            'username'     => 'required|min_length[4]|max_length[100]|alpha_numeric|is_unique[users.username]',
            'primary_role' => 'required|in_list[admin,panitera,majelis,pengadu,teradu]',
            'password'     => 'required|strong_password',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' | ', $this->validator->getErrors()));
        }

        // Validasi NIA jika teradu
        $nia = $this->request->getPost('nia');
        if ($targetRole === 'teradu' && $nia && !preg_match('/^\d{2}\.\d{5}$/', $nia)) {
            return redirect()->back()->withInput()
                ->with('error', 'Format NIA harus NN.NNNNN.');
        }

        $this->userModel->insert([
            'nama'                 => $this->request->getPost('nama'),
            'email'                => $this->request->getPost('email'),
            'username'             => strtolower($this->request->getPost('username')),
            'password'             => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'primary_role'         => $targetRole,
            'instansi'             => $this->request->getPost('instansi'),
            'no_hp'                => $this->request->getPost('no_hp'),
            'alamat'               => $this->request->getPost('alamat'),
            'nia'                  => $nia ?: null,
            'is_advokat'           => $targetRole === 'teradu' ? 1 : 0,
            'must_change_password' => $this->request->getPost('must_change_password') ? 1 : 0,
            'setuju_ecourt'        => 1,
            'tgl_setuju'           => date('Y-m-d H:i:s'),
            'status'               => 'aktif',
            'email_verified_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return redirect()->to('/users')->with('error', 'User tidak ditemukan.');

        $role = session()->get('primary_role');
        if ($role === 'panitera' && !in_array($user['primary_role'], ['pengadu','teradu'])) {
            return redirect()->to('/users')->with('error', 'Anda tidak berhak mengedit user ini.');
        }

        $allowedRoles = $role === 'admin'
            ? ['admin','panitera','majelis','pengadu','teradu']
            : ['pengadu','teradu'];

        return view('App\Modules\User\Views\form', [
            'title'        => 'Edit User',
            'allowedRoles' => $allowedRoles,
            'user'         => $user,
        ]);
    }

    public function update(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return redirect()->to('/users')->with('error', 'User tidak ditemukan.');

        $role = session()->get('primary_role');
        if ($role === 'panitera' && !in_array($user['primary_role'], ['pengadu','teradu'])) {
            return redirect()->to('/users')->with('error', 'Anda tidak berhak mengedit user ini.');
        }

        $rules = [
            'nama'         => 'required|min_length[3]|max_length[150]',
            'email'        => "required|valid_email|is_unique[users.email,id,{$id}]",
            'username'     => "required|min_length[4]|max_length[100]|alpha_numeric|is_unique[users.username,id,{$id}]",
            'primary_role' => 'required|in_list[admin,panitera,majelis,pengadu,teradu]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' | ', $this->validator->getErrors()));
        }

        $targetRole = $this->request->getPost('primary_role');
        if ($role === 'panitera' && !in_array($targetRole, ['pengadu','teradu'])) {
            return redirect()->back()->with('error', 'Anda tidak berhak menetapkan role ini.');
        }

        $data = [
            'nama'         => $this->request->getPost('nama'),
            'email'        => $this->request->getPost('email'),
            'username'     => strtolower($this->request->getPost('username')),
            'primary_role' => $targetRole,
            'instansi'     => $this->request->getPost('instansi'),
            'no_hp'        => $this->request->getPost('no_hp'),
            'alamat'       => $this->request->getPost('alamat'),
            'nia'          => $this->request->getPost('nia') ?: null,
        ];

        // Update password jika diisi
        $pwd = $this->request->getPost('password');
        if ($pwd) {
            $validator = \Config\Services::validation();
            if (!$validator->check($pwd, 'strong_password')) {
                return redirect()->back()->with('error', 'Password tidak memenuhi syarat.');
            }
            $data['password'] = password_hash($pwd, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/users')->with('success', 'User berhasil diubah.');
    }

    public function delete(int $id)
    {
        $role = session()->get('primary_role');
        if ($role !== 'admin') {
            return redirect()->to('/users')->with('error', 'Hanya admin yang bisa hapus user.');
        }
        if ($id == session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Tidak bisa hapus akun sendiri.');
        }

        $this->userModel->delete($id);
        return redirect()->to('/users')->with('success', 'User dihapus.');
    }

    /**
     * Ubah status user (aktif/nonaktif) — berguna untuk verifikasi manual
     */
    public function toggleStatus(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return redirect()->to('/users')->with('error', 'User tidak ditemukan.');

        $newStatus = $user['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $data = ['status' => $newStatus];

        // Kalau diaktifkan manual, tandai email verified juga
        if ($newStatus === 'aktif' && !$user['email_verified_at']) {
            $data['email_verified_at'] = date('Y-m-d H:i:s');
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/users')->with('success',
            "Status user diubah menjadi {$newStatus}.");
    }

    /**
     * Kirim ulang email verifikasi
     */
    public function resendVerification(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return redirect()->to('/users')->with('error', 'User tidak ditemukan.');

        if ($user['email_verified_at']) {
            return redirect()->to('/users')->with('info', 'Email sudah terverifikasi.');
        }

        $token = bin2hex(random_bytes(32));
        $this->userModel->update($id, [
            'email_verification_token'     => $token,
            'email_verification_sent_at'   => date('Y-m-d H:i:s'),
        ]);

        $link = base_url('verify-email/' . $token);
        $body = template_email('Verifikasi Email', "
            <h3>Halo, {$user['nama']}</h3>
            <p>Silakan verifikasi email Anda dengan klik tombol berikut:</p>
            <p style='text-align:center;margin:24px 0;'>
                <a href='{$link}' style='background:#1e40af;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;'>
                    Verifikasi Email
                </a>
            </p>
        ");
        kirim_email($user['email'], 'Verifikasi Email — E-Court', $body, $id, $user['nama']);

        return redirect()->to('/users')->with('success', 'Email verifikasi dikirim ulang.');
    }
}