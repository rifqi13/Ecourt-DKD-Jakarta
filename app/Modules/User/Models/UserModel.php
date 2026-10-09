<?php
namespace App\Modules\User\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nama','email','username','password','primary_role',
        'instansi','no_hp','alamat','nik','nia','is_advokat','no_peradi',
        'foto','must_change_password','setuju_ecourt','tgl_setuju',
        'status','email_verified_at','last_login',
    ];

    /**
     * Cari user berdasarkan NIA atau email (untuk reuse akun teradu)
     */
    public function findByNiaOrEmail(?string $nia, ?string $email): ?array
    {
        if (!$nia && !$email) return null;

        return $this->groupStart()
                ->where('nia', $nia)
                ->orWhere('email', $email)
            ->groupEnd()
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Generate username unique: teradu001, teradu002, dst
     */
    public function generateUsernameTeradu(): string
    {
        $prefix = 'teradu';
        $last = $this->like('username', $prefix, 'after')
                     ->orderBy('id', 'DESC')
                     ->first();
        $n = 1;
        if ($last) {
            $n = (int) substr($last['username'], strlen($prefix)) + 1;
        }
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate password default: Ecourt + 4 digit
     */
    public function generateDefaultPassword(): string
    {
        return 'Ecourt' . random_int(1000, 9999);
    }
}