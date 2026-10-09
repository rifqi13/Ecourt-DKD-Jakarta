<?php
namespace App\Modules\Perkara\Models;

use CodeIgniter\Model;

class PerkaraPihakModel extends Model
{
    protected $table         = 'perkara_pihak';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'perkara_id','user_id','tipe','instansi','alamat','no_hp','nia','created_at',
    ];
}