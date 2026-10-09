<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'E-Court') ?> — E-Court Elektronik</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">

    <style>
        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 80px 0;
        }
        .navbar-public {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        footer.public-footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 30px 0;
            margin-top: 60px;
        }
    </style>
</head>
<body>

<!-- NAVBAR PUBLIC -->
<nav class="navbar navbar-expand-lg navbar-public">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= base_url('/') ?>">
            <i class="bi bi-bank2"></i> E-Court Elektronik
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPub">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPub">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/') ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('informasi') ?>">Informasi</a>
                </li>
                <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm ms-lg-2" href="<?= base_url('logout') ?>">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('login') ?>">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary btn-sm" href="<?= base_url('register') ?>">
                            <i class="bi bi-person-plus"></i> Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<?= $this->renderSection('content') ?>

<!-- FOOTER -->
<footer class="public-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-white"><i class="bi bi-bank2"></i> E-Court Elektronik</h6>
                <p class="small mb-0">
                    Sistem Pengaduan & Persidangan Elektronik<br>
                    Dewan Kehormatan Daerah — PERADI
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small mb-0">
                    &copy; <?= date('Y') ?> DKD PERADI. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>