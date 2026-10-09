<?php

if (!function_exists('createAkunTeradu')) {
    /**
     * Auto-create akun teradu atau reuse jika sudah ada.
     * Return: ['user' => array, 'password_plain' => string|null, 'is_new' => bool]
     */
    function createAkunTeradu(array $data): array
    {
        $userModel = new \App\Modules\User\Models\UserModel();

        // Cek berdasarkan NIA atau email
        $existing = $userModel->findByNiaOrEmail($data['nia'] ?? null, $data['email'] ?? null);

        if ($existing) {
            return [
                'user'          => $existing,
                'password_plain'=> null,
                'is_new'        => false,
            ];
        }

        $passwordPlain = $userModel->generateDefaultPassword();
        $username      = $userModel->generateUsernameTeradu();

        $userModel->insert([
            'nama'                 => $data['nama'],
            'email'                => $data['email'] ?? ($username . '@ecourt.local'),
            'username'             => $username,
            'password'             => password_hash($passwordPlain, PASSWORD_BCRYPT),
            'primary_role'         => 'teradu',
            'instansi'             => $data['instansi'] ?? null,
            'no_hp'                => $data['no_hp'] ?? null,
            'alamat'               => $data['alamat'] ?? null,
            'nia'                  => $data['nia'] ?? null,
            'is_advokat'           => 1, // Karena teradu wajib advokat
            'no_peradi'            => $data['no_peradi'] ?? null,
            'must_change_password' => 1,
            'setuju_ecourt'        => 0,
            'status'               => 'pending',
        ]);

        return [
            'user'          => $userModel->find($userModel->getInsertID()),
            'password_plain'=> $passwordPlain,
            'is_new'        => true,
        ];
    }
}