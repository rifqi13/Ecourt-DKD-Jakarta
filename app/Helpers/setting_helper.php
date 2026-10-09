<?php

if (!function_exists('setting')) {
    /**
     * Ambil nilai pengaturan dari DB dengan cache
     */
    function setting(string $key, $default = null)
    {
        static $cache = null;
        if ($cache === null) {
            $cache = cache('pengaturan');
            if ($cache === null) {
                $db = \Config\Database::connect();
                $rows = $db->table('pengaturan')->get()->getResultArray();
                $cache = [];
                foreach ($rows as $r) {
                    $cache[$r['key']] = match($r['tipe']) {
                        'int'  => (int)$r['value'],
                        'bool' => (bool)(int)$r['value'],
                        'json' => json_decode($r['value'], true),
                        default => $r['value'],
                    };
                }
                cache()->save('pengaturan', $cache, 3600);
            }
        }
        return $cache[$key] ?? $default;
    }
}

if (!function_exists('set_setting')) {
    function set_setting(string $key, $value, string $tipe = 'string'): bool
    {
        $db = \Config\Database::connect();
        $val = $tipe === 'json' ? json_encode($value) : (string)$value;

        $exists = $db->table('pengaturan')->where('key', $key)->countAllResults() > 0;
        if ($exists) {
            $db->table('pengaturan')->where('key', $key)->update([
                'value'      => $val,
                'tipe'       => $tipe,
                'updated_by' => session()->get('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('pengaturan')->insert([
                'key'        => $key,
                'value'      => $val,
                'tipe'       => $tipe,
                'updated_by' => session()->get('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        cache()->delete('pengaturan');
        return true;
    }
}