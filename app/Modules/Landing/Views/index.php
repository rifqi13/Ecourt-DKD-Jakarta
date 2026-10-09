<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<section class="hero text-white text-center py-5">
    <div class="container">
        <h1 class="fw-bold display-5">E-Court Elektronik</h1>
        <p class="lead">Sistem Pengaduan & Persidangan Elektronik yang Cepat, Transparan, dan Akuntabel</p>
        <a href="<?= base_url('register') ?>" class="btn btn-light btn-lg mt-3">
            <i class="bi bi-person-plus"></i> Daftar sebagai Pengadu
        </a>
        <a href="<?= base_url('login') ?>" class="btn btn-outline-light btn-lg mt-3">
            <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 p-4">
                    <i class="bi bi-file-earmark-text text-primary fs-1"></i>
                    <h5 class="mt-3">Pengaduan Online</h5>
                    <p class="text-muted">Ajukan pengaduan secara elektronik dengan bukti digital.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 p-4">
                    <i class="bi bi-calendar-event text-primary fs-1"></i>
                    <h5 class="mt-3">Jadwal Terintegrasi</h5>
                    <p class="text-muted">Pantau jadwal sidang secara real-time dalam bentuk linimasa.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 p-4">
                    <i class="bi bi-shield-check text-primary fs-1"></i>
                    <h5 class="mt-3">Aman & Transparan</h5>
                    <p class="text-muted">Setiap proses tercatat dengan audit log lengkap.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>