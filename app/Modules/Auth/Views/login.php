<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card">
    <div class="text-center mb-4">
        <i class="bi bi-bank2 text-primary" style="font-size: 56px;"></i>
        <h4 class="mt-2 fw-bold">E-Court Elektronik</h4>
        <p class="text-muted small">Sistem Peradilan Elektronik</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Username / Email</label>
            <input type="text" name="username" class="form-control" required autofocus
                value="<?= old('username') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="pwd" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary"
                    onclick="document.getElementById('pwd').type = document.getElementById('pwd').type === 'password' ? 'text' : 'password'">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <!-- Sebelum tombol login, tambahkan -->
        <div class="text-end mb-2">
            <a href="<?= base_url('lupa-password') ?>" class="small text-decoration-none">
                Lupa password?
            </a>
        </div>
        
        <button class="btn btn-primary w-100 py-2 mb-3">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>

        <div class="text-center small">
            Belum punya akun? <a href="<?= base_url('register') ?>">Daftar sebagai Pengadu</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>