<?php
namespace App\Modules\Perkara\Models;

use CodeIgniter\Model;

class PerkaraModel extends Model
{
    protected $table         = 'perkara';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'surat_masuk_id','no_register_sementara','no_register_perkara',
        'kode_input','jenis_perkara','jenis_perkara_id','status',
        'catatan_verifikasi','verified_by','verified_at','alasan_tolak',
        'nilai_perkara','tgl_pengaduan','tgl_terima_teradu',
        'tgl_batas_jawaban','tgl_batas_jawaban_2','teradu_lepas_hak',
        'panitera_id','majelis_hearing_id','created_by',
    ];

    /**
     * Generate No Register Sementara: 030/DKD/DKI/1729-2025
     * $kodeJenis = DKD / DKP
     * $kodeInput = nomor disposisi (1729)
     */
    public function generateNoRegisterSementara(string $kodeJenis, string $kodeInput): string
    {
        $tahun = date('Y');
        $count = $this->where('YEAR(created_at)', $tahun)
                      ->where('no_register_sementara IS NOT NULL')
                      ->countAllResults() + 1;
        $urut = str_pad($count, 3, '0', STR_PAD_LEFT);
        return "{$urut}/{$kodeJenis}/DKI/{$kodeInput}-{$tahun}";
    }

    /**
     * Generate No Register Perkara: No.13/PERADI/DKD-JAKARTA/2025
     */
    public function generateNoRegisterPerkara(string $kodeJenis): string
    {
        $tahun = date('Y');
        $count = $this->where('YEAR(created_at)', $tahun)
                      ->where('no_register_perkara IS NOT NULL')
                      ->countAllResults() + 1;
        return "No.{$count}/PERADI/{$kodeJenis}-JAKARTA/{$tahun}";
    }

    /**
     * Ambil detail lengkap (join)
     */
    public function getDetail(int $id): ?array
    {
        $perkara = $this->select('p.*, jp.kode AS kode_jenis, jp.nama AS nama_jenis,
                                  sm.no_agenda, sm.no_disposisi, sm.no_surat, sm.tgl_surat,
                                  u.nama AS nama_panitera, mh.nama AS nama_majelis_hearing')
            ->from('perkara p')
            ->join('jenis_perkara jp', 'jp.id = p.jenis_perkara_id', 'left')
            ->join('surat_masuk sm', 'sm.id = p.surat_masuk_id', 'left')
            ->join('users u', 'u.id = p.panitera_id', 'left')
            ->join('users mh', 'mh.id = p.majelis_hearing_id', 'left')
            ->where('p.id', $id)
            ->where('p.deleted_at', null)
            ->get()->getRowArray();

        if (!$perkara) return null;

        // Ambil pihak
        $pihak = $this->db->table('perkara_pihak pp')
            ->select('pp.*, u.nama, u.email, u.username, u.is_advokat, u.no_peradi')
            ->join('users u', 'u.id = pp.user_id')
            ->where('pp.perkara_id', $id)
            ->get()->getResultArray();

        $perkara['pengadu'] = array_values(array_filter($pihak, fn($p) => $p['tipe'] === 'pengadu'))[0] ?? null;
        $perkara['teradu']  = array_values(array_filter($pihak, fn($p) => $p['tipe'] === 'teradu'))[0] ?? null;

        return $perkara;
    }
}