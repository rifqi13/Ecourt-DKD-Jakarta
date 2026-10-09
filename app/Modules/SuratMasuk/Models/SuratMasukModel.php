<?php
namespace App\Modules\SuratMasuk\Models;

use CodeIgniter\Model;

class SuratMasukModel extends Model
{
    protected $table         = 'surat_masuk';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'no_agenda','no_disposisi','no_surat','tgl_surat','tgl_terima',
        'asal_surat','perihal','file_surat','status','jenis',
        'created_by','pengadu_id',
    ];

    /**
     * Generate No Agenda: 1729-2025 (reset tiap tahun)
     * Format: {urut}-{tahun}
     * no_agenda dan no_disposisi pakai nomor yang SAMA
     */
    public function generateNoAgenda(): string
    {
        $tahun = date('Y');
        $row = $this->selectMax('CAST(SUBSTRING_INDEX(no_agenda, "-", 1) AS UNSIGNED)', 'max_no')
                    ->where('no_agenda LIKE', '%-' . $tahun)
                    ->get()->getRowArray();
        $next = ((int)($row['max_no'] ?? 0)) + 1;
        return str_pad($next, 4, '0', STR_PAD_LEFT) . '-' . $tahun;
    }

    public function isDisposisiTaken(string $noDisposisi, ?int $exceptId = null): bool
    {
        $builder = $this->where('no_disposisi', $noDisposisi);
        if ($exceptId) $builder->where('id !=', $exceptId);
        return $builder->countAllResults() > 0;
    }
}