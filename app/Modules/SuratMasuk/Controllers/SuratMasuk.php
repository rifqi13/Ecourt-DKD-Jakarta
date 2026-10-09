<?php
namespace App\Modules\SuratMasuk\Controllers;

use App\Controllers\BaseController;
use App\Modules\SuratMasuk\Models\SuratMasukModel;

class SuratMasuk extends BaseController
{
    protected SuratMasukModel $model;

    public function __construct()
    {
        $this->model = new SuratMasukModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');
        $status  = $this->request->getGet('status');

        $builder = $this->model
            ->select('sm.*, u.nama AS pembuat, p.nama AS pengadu')
            ->from('surat_masuk sm')
            ->join('users u', 'u.id = sm.created_by', 'left')
            ->join('users p', 'p.id = sm.pengadu_id', 'left')
            ->orderBy('sm.tgl_terima', 'DESC');

        if ($keyword) {
            $builder->groupStart()
                ->like('sm.no_agenda', $keyword)
                ->orLike('sm.no_disposisi', $keyword)
                ->orLike('sm.asal_surat', $keyword)
                ->orLike('sm.perihal', $keyword)
            ->groupEnd();
        }
        if ($status) $builder->where('sm.status', $status);

        return view('App\Modules\SuratMasuk\Views\index', [
            'title'   => 'Surat Masuk',
            'list'    => $builder->paginate(15),
            'pager'   => $this->model->pager,
            'keyword' => $keyword,
            'status'  => $status,
        ]);
    }

    public function create()
    {
        $noAgenda = $this->model->generateNoAgenda();
        return view('App\Modules\SuratMasuk\Views\form', [
            'title'         => 'Registrasi Surat Masuk',
            'no_agenda'     => $noAgenda,
            'no_disposisi'  => $noAgenda,
            'jenis_list'    => ['fisik' => 'Fisik', 'online' => 'Online'],
        ]);
    }

    public function store()
    {
        $rules = [
            'no_disposisi' => 'required|max_length[30]|unique_disposisi',
            'no_surat'     => 'permit_empty|max_length[100]',
            'tgl_surat'    => 'permit_empty|valid_date',
            'tgl_terima'   => 'required|valid_date',
            'asal_surat'   => 'required|max_length[200]',
            'perihal'      => 'required|max_length[255]',
            'file_surat'   => 'permit_empty|uploaded[file_surat]|max_size[file_surat,10240]|ext_in[file_surat,pdf,doc,docx]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' | ', $this->validator->getErrors()));
        }

        $filePath = null;
        $file = $this->request->getFile('file_surat');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/surat_masuk', $newName);
            $filePath = 'surat_masuk/' . $newName;
        }

        $this->model->insert([
            'no_agenda'    => $this->request->getPost('no_agenda') ?: $this->model->generateNoAgenda(),
            'no_disposisi' => $this->request->getPost('no_disposisi'),
            'no_surat'     => $this->request->getPost('no_surat'),
            'tgl_surat'    => $this->request->getPost('tgl_surat') ?: null,
            'tgl_terima'   => $this->request->getPost('tgl_terima'),
            'asal_surat'   => $this->request->getPost('asal_surat'),
            'perihal'      => $this->request->getPost('perihal'),
            'file_surat'   => $filePath,
            'jenis'        => $this->request->getPost('jenis') ?? 'fisik',
            'status'       => 'baru',
            'created_by'   => session()->get('user_id'),
        ]);

        return redirect()->to('/surat-masuk')
            ->with('success', 'Surat masuk berhasil diregistrasi.');
    }

    public function show(int $id)
    {
        $row = $this->model
            ->select('sm.*, u.nama AS pembuat, p.nama AS pengadu')
            ->from('surat_masuk sm')
            ->join('users u', 'u.id = sm.created_by', 'left')
            ->join('users p', 'p.id = sm.pengadu_id', 'left')
            ->where('sm.id', $id)->get()->getRowArray();

        if (!$row) return redirect()->to('/surat-masuk')->with('error', 'Data tidak ditemukan.');

        return view('App\Modules\SuratMasuk\Views\detail', [
            'title' => 'Detail Surat Masuk',
            'row'   => $row,
        ]);
    }
}