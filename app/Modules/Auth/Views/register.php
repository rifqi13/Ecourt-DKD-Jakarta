<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card" style="max-width:600px;">
    <div class="text-center mb-4">
        <i class="bi bi-person-plus text-primary" style="font-size: 48px;"></i>
        <h4 class="mt-2 fw-bold">Daftar sebagai Pengadu</h4>
        <p class="text-muted small">Isi data diri Anda dengan lengkap</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('register') ?>">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" required value="<?= old('nama') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Instansi <span class="text-danger">*</span></label>
                <input type="text" name="instansi" class="form-control" required value="<?= old('instansi') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required value="<?= old('email') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" class="form-control" required value="<?= old('no_hp') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" required value="<?= old('username') ?>">
                <small class="text-muted">Huruf & angka, min 4 karakter</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="8">
                <small class="text-muted">Min 8 karakter, kombinasi huruf & angka</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" name="password2" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Alamat Bersurat <span class="text-danger">*</span></label>
                <textarea name="alamat" class="form-control" rows="2" required><?= old('alamat') ?></textarea>
            </div>
        </div>

        <hr class="my-4">

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="setuju" id="setuju" value="1" required>
            <label class="form-check-label small" for="setuju">
                Saya menyatakan <strong>bersedia menggunakan layanan e-Court Elektronik</strong>
                dan menjalankan seluruh proses persidangan secara elektronik sesuai peraturan yang berlaku.
                Saya juga menyatakan data yang saya isi adalah benar.
            </label>
        </div>

        <button class="btn btn-primary w-100 py-2 mb-3">
            <i class="bi bi-check-circle"></i> Daftar Sekarang
        </button>

        <div class="text-center small">
            Sudah punya akun? <a href="<?= base_url('login') ?>">Login di sini</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>