<?php

if (!function_exists('current_user')) {
    function current_user(): array
    {
        $s = session();
        return [
            'id'           => $s->get('user_id'),
            'nama'         => $s->get('nama'),
            'username'     => $s->get('username'),
            'email'        => $s->get('email'),
            'primary_role' => $s->get('primary_role'),
            'is_advokat'   => $s->get('is_advokat'),
        ];
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return (bool) session()->get('isLoggedIn');
    }
}

if (!function_exists('has_role')) {
    function has_role(string ...$roles): bool
    {
        $role = session()->get('primary_role');
        return in_array($role, $roles, true);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Cek permission dari DB (role_permissions)
     * $aksi = 'view'|'create'|'edit'|'delete'|'approve'
     */
    function has_permission(string $modul, string $aksi = 'view'): bool
    {
        static $cache = [];
        $role = session()->get('primary_role');
        if (!$role) return false;

        $key = "{$role}|{$modul}|{$aksi}";
        if (isset($cache[$key])) return $cache[$key];

        $db = \Config\Database::connect();
        $row = $db->table('role_permissions rp')
            ->select('rp.can_' . $aksi)
            ->join('roles r', 'r.id = rp.role_id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('r.slug', $role)
            ->where('p.modul', $modul)
            ->get()->getRowArray();

        $cache[$key] = $row ? (bool) $row['can_' . $aksi] : false;
        return $cache[$key];
    }
}