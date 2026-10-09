<?php
// app/Helpers/register_helper.php

if (!function_exists('generateNoRegisterSementara')) {
    function generateNoRegisterSementara(int $perkaraId, string $kodeInput): string
    {
        $db = \Config\Database::connect();
        $tahun = date('Y');
        $count = $db->table('perkara')
            ->where('YEAR(created_at)', $tahun)
            ->where('no_register_sementara IS NOT NULL')
            ->countAllResults() + 1;
        $urut = str_pad($count, 3, '0', STR_PAD_LEFT);
        return "{$urut}/DKD/DKI/{$kodeInput}-{$tahun}";
    }
}

if (!function_exists('generateNoRegisterPerkara')) {
    function generateNoRegisterPerkara(): string
    {
        $db = \Config\Database::connect();
        $tahun = date('Y');
        $count = $db->table('perkara')
            ->where('YEAR(created_at)', $tahun)
            ->where('no_register_perkara IS NOT NULL')
            ->countAllResults() + 1;
        return "No.{$count}/PERADI/DKD-JAKARTA/{$tahun}";
    }
}