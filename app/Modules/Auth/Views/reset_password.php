<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card">
    <div class="text-center mb-4">
        <i class="bi bi-shield-lock text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-2 fw-bold">Reset Password</h4>
        <p class="text-muted small">Atur password baru Anda</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('reset-password/' . $token) ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control" required minlength="8">
            <small class="text-muted">Min 8 karakter</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password2" class="form-control" required>
        </div>
        <button class="btn btn-danger w-100 py-2">
            <i class="bi bi-save"></i> Simpan Password Baru
        </button>
    </form>
</div>

<?= $this->endSection() ?>