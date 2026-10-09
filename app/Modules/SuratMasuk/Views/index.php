<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-envelope-paper"></i> Daftar Surat Masuk</span>
        <a href="<?= base_url('surat-masuk/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Registrasi Surat
        </a>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control"
                       placeholder="Cari no agenda / asal / perihal..." value="<?= esc($keyword) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach (['baru','diproses','jadi_perkara','arsip'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Agenda</th>
                        <th>Tgl Terima</th>
                        <th>Asal Surat</th>
                        <th>Perihal</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data.</td></tr>
                    <?php else: foreach ($list as $r): ?>
                        <tr>
                            <td><code><?= esc($r['no_agenda']) ?></code></td>
                            <td><?= date('d/m/Y', strtotime($r['tgl_terima'])) ?></td>
                            <td><?= esc($r['asal_surat']) ?></td>
                            <td><?= esc($r['perihal']) ?></td>
                            <td><span class="badge bg-secondary"><?= esc($r['jenis']) ?></span></td>
                            <td>
                                <?php
                                $badge = ['baru'=>'info','diproses'=>'warning','jadi_perkara'=>'success','arsip'=>'secondary'][$r['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td>
                                <a href="<?= base_url('surat-masuk/show/'.$r['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $pager->links() ?></div>
    </div>
</div>

<?= $this->endSection() ?>