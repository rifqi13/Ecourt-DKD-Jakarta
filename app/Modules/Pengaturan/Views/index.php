<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header"><i class="bi bi-gear"></i> Pengaturan Sistem</div>
    <div class="card-body">
        <form method="post" action="<?= base_url('pengaturan/save') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nama Kantor</label>
                <input type="text" name="nama_kantor" class="form-control"
                       value="<?= esc($items['nama_kantor']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email Admin</label>
                <input type="email" name="email_admin" class="form-control"
                       value="<?= esc($items['email_admin']) ?>">
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="wajib_verifikasi_email"
                       id="wve" value="1" <?= $items['wajib_verifikasi_email'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="wve">
                    <strong>Wajib Verifikasi Email</strong>
                    <br><small class="text-muted">Jika aktif, pengadu baru harus verifikasi email sebelum bisa login.</small>
                </label>
            </div>

            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>