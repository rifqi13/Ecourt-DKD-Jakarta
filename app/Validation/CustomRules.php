<?php
namespace App\Validation;

class CustomRules
{
    public function valid_nia(string $str, string $fields = null, array $data = [], ?string &$error = null): bool
    {
        if (!preg_match('/^\d{2}\.\d{5}$/', $str)) {
            $error = 'Format NIA harus NN.NNNNN (contoh: 12.34567).';
            return false;
        }
        return true;
    }

    public function unique_disposisi(string $str, string $fields = null, array $data = [], ?string &$error = null): bool
    {
        $model = new \App\Modules\SuratMasuk\Models\SuratMasukModel();
        if ($model->isDisposisiTaken($str)) {
            $error = 'Nomor disposisi sudah digunakan.';
            return false;
        }
        return true;
    }

    /**
     * Password policy: min length + wajib huruf & angka
     */
    public function strong_password(string $str, string $fields = null, array $data = [], ?string &$error = null): bool
    {
        $minLength = (int) setting('password_min_length', 8);
        $wajibKombinasi = (bool) setting('password_wajib_kombinasi', true);

        if (strlen($str) < $minLength) {
            $error = "Password minimal {$minLength} karakter.";
            return false;
        }

        if ($wajibKombinasi) {
            if (!preg_match('/[A-Za-z]/', $str)) {
                $error = 'Password harus mengandung huruf.';
                return false;
            }
            if (!preg_match('/\d/', $str)) {
                $error = 'Password harus mengandung angka.';
                return false;
            }
        }
        return true;
    }
}