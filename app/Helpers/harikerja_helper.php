<?php
// app/Helpers/harikerja_helper.php

if (!function_exists('tambahHariKerja')) {
    function tambahHariKerja(string $tglMulai, int $hariKerja): string
    {
        $db = \Config\Database::connect();
        $libur = array_column(
            $db->table('hari_libur')->select('tanggal')->get()->getResultArray(),
            'tanggal'
        );
        $date = new \DateTime($tglMulai);
        $count = 0;
        while ($count < $hariKerja) {
            $date->modify('+1 day');
            $dow = (int)$date->format('N'); // 1=Senin, 7=Minggu
            if ($dow >= 6) continue; // Sabtu/Minggu
            if (in_array($date->format('Y-m-d'), $libur)) continue;
            $count++;
        }
        return $date->format('Y-m-d');
    }

    if (!function_exists('getJamKerjaByDay')) {
    /**
     * Ambil konfigurasi jam kerja untuk hari tertentu (1-7)
     */
    function getJamKerjaByDay(int $dayOfWeek): ?array
    {
        $cache = \Config\Services::cache();
        $key = "jam_kerja_{$dayOfWeek}";
        $data = $cache->get($key);
        if ($data === null) {
            $db = \Config\Database::connect();
            $data = $db->table('jam_kerja')->where('hari', $dayOfWeek)->get()->getRowArray();
            $cache->save($key, $data, 3600);
        }
        return $data ?: null;
    }
}

if (!function_exists('isHariLibur')) {
    /**
     * Cek apakah tanggal tertentu libur (baik dari tabel hari_libur
     * maupun karena bukan hari kerja di jam_kerja)
     */
    function isHariLibur(string $tanggal): bool
    {
        $db = \Config\Database::connect();

        // Cek override di hari_libur
        $row = $db->table('hari_libur')->where('tanggal', $tanggal)->get()->getRowArray();
        if ($row) {
            // Jika is_kerja=1, artinya hari pengganti kerja
            return (int)$row['is_kerja'] === 0;
        }

        // Cek konfigurasi jam_kerja
        $dow = (int)(new \DateTime($tanggal))->format('N');
        $jk = getJamKerjaByDay($dow);
        return !$jk || (int)$jk['is_kerja'] === 0;
    }
}

if (!function_exists('nextWorkingTime')) {
    /**
     * Cari waktu kerja berikutnya (jika jam di luar jam kerja)
     */
    function nextWorkingTime(\DateTime $dt): \DateTime
    {
        $maxLoop = 366; // guard

        for ($i = 0; $i < $maxLoop; $i++) {
            $tgl = $dt->format('Y-m-d');
            $dow = (int)$dt->format('N');
            $jk  = getJamKerjaByDay($dow);

            if (!isHariLibur($tgl) && $jk && (int)$jk['is_kerja'] === 1) {
                $jam     = $dt->format('H:i:s');
                $mulai   = $jk['jam_mulai'];
                $selesai = $jk['jam_selesai'];

                // Sebelum jam buka → set ke jam buka
                if ($jam < $mulai) {
                    $dt->setTime(
                        (int)substr($mulai,0,2),
                        (int)substr($mulai,3,2),
                        (int)substr($mulai,6,2)
                    );
                    return $dt;
                }

                // Sesudah jam tutup atau di jam istirahat → lanjut hari berikutnya
                $istM = $jk['istirahat_mulai'];
                $istS = $jk['istirahat_selesai'];

                if ($jam < $selesai) {
                    if ($istM && $istS && $jam >= $istM && $jam < $istS) {
                        // Di jam istirahat → pindah ke selesai istirahat
                        $dt->setTime(
                            (int)substr($istS,0,2),
                            (int)substr($istS,3,2),
                            (int)substr($istS,6,2)
                        );
                        return $dt;
                    }
                    return $dt; // Dalam jam kerja
                }
            }

            // Geser ke hari berikutnya jam 00:00
            $dt->modify('+1 day')->setTime(0,0,0);
        }

        return $dt;
    }
}

if (!function_exists('tambahHariKerja')) {
    /**
     * Tambahkan N hari kerja dari tanggal tertentu.
     *
     * @param string $tglMulai  Format 'Y-m-d H:i:s' atau 'Y-m-d'
     * @param int    $hariKerja N hari kerja
     * @return string 'Y-m-d H:i:s' hasil
     */
    function tambahHariKerja(string $tglMulai, int $hariKerja): string
    {
        $dt = new \DateTime($tglMulai);

        // Normalisasi: jika di luar jam kerja, mulai dari waktu kerja berikutnya
        $dt = nextWorkingTime($dt);

        if ($hariKerja === 0) {
            return $dt->format('Y-m-d H:i:s');
        }

        $maxLoop  = 3650; // guard 10 tahun
        $counter  = 0;
        $safeLoop = 0;

        while ($counter < $hariKerja && $safeLoop < $maxLoop) {
            $safeLoop++;

            $tgl = $dt->format('Y-m-d');
            $dow = (int)$dt->format('N');
            $jk  = getJamKerjaByDay($dow);

            if (!isHariLibur($tgl) && $jk && (int)$jk['is_kerja'] === 1) {
                // Hari ini hari kerja, tapi cek apakah masih ada sisa jam kerja
                $jam     = $dt->format('H:i:s');
                $selesai = $jk['jam_selesai'];

                if ($jam < $selesai) {
                    $counter++;
                    if ($counter >= $hariKerja) {
                        break;
                    }
                }
            }

            // Geser ke hari berikutnya, lalu normalisasi ke jam kerja
            $dt->modify('+1 day')->setTime(0,0,0);
            $dt = nextWorkingTime($dt);
        }

        return $dt->format('Y-m-d H:i:s');
    }
}

if (!function_exists('hitungHariKerjaAntara')) {
    /**
     * Hitung selisih hari kerja antara dua tanggal (untuk progress)
     */
    function hitungHariKerjaAntara(string $tglAwal, string $tglAkhir): int
    {
        $awal  = new \DateTime($tglAwal);
        $akhir = new \DateTime($tglAkhir);
        $count = 0;

        while ($awal <= $akhir) {
            if (!isHariLibur($awal->format('Y-m-d'))) {
                $count++;
            }
            $awal->modify('+1 day');
        }
        return $count;
    }
}
}