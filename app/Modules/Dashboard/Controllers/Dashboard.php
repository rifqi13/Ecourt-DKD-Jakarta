<?php
namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = session()->get('primary_role');
        $userId = session()->get('user_id');

        $data = [
            'title' => 'Dashboard',
            'role'  => $role,
        ];

        // Statistik dasar per role
        switch ($role) {
            case 'admin':
            case 'panitera':
                $data['stats'] = [
                    'total_perkara'    => $this->db->table('perkara')->where('deleted_at', null)->countAllResults(),
                    'perkara_aktif'    => $this->db->table('perkara')->whereNotIn('status', ['selesai','ditolak'])->where('deleted_at', null)->countAllResults(),
                    'menunggu_verif'   => $this->db->table('perkara')->where('status','menunggu_verifikasi')->countAllResults(),
                    'menunggu_bayar'   => $this->db->table('perkara')->where('status','menunggu_pembayaran')->countAllResults(),
                    'total_user'       => $this->db->table('users')->where('deleted_at', null)->countAllResults(),
                ];
                break;

            case 'majelis':
                $data['stats'] = [
                    'perkara_ditugaskan' => $this->db->table('perkara_majelis')
                        ->where('user_id', $userId)->where('is_aktif', 1)->countAllResults(),
                    'bukti_pending' => $this->db->table('dokumen')
                        ->where('kategori','bukti')->where('status_approve','pending')->countAllResults(),
                ];
                break;

            case 'pengadu':
                $data['stats'] = [
                    'perkara_saya'  => $this->db->table('perkara_pihak')
                        ->where('user_id', $userId)->where('tipe','pengadu')->countAllResults(),
                    'perkara_aktif' => $this->db->table('perkara_pihak pp')
                        ->join('perkara p','p.id=pp.perkara_id')
                        ->where('pp.user_id', $userId)
                        ->where('pp.tipe','pengadu')
                        ->whereNotIn('p.status',['selesai','ditolak'])
                        ->countAllResults(),
                ];
                break;

            case 'teradu':
                $data['stats'] = [
                    'perkara_saya'  => $this->db->table('perkara_pihak')
                        ->where('user_id', $userId)->where('tipe','teradu')->countAllResults(),
                    'perkara_aktif' => $this->db->table('perkara_pihak pp')
                        ->join('perkara p','p.id=pp.perkara_id')
                        ->where('pp.user_id', $userId)
                        ->where('pp.tipe','teradu')
                        ->whereNotIn('p.status',['selesai','ditolak'])
                        ->countAllResults(),
                ];
                break;
        }

        return view("App\Modules\Dashboard\Views\index_{$role}", $data);
    }
}