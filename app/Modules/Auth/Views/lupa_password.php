<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card">
    <div class="text-center mb-4">
        <i class="bi bi-key text-warning" style="font-size: 48px;"></i>
        <h4 class="mt-2 fw-bold">Lupa Password?</h4>
        <p class="text-muted small">Masukkan email terdaftar, kami akan mengirimkan link reset.</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('lupa-password') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Email Terdaftar</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <button class="btn btn-warning w-100 py-2 mb-3">
            <i class="bi bi-envelope"></i> Kirim Link Reset
        </button>
        <div class="text-center small">
            Ingat password? <a href="<?= base_url('login') ?>">Kembali ke Login</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>