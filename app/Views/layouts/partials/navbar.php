<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url('dashboard') ?>">
            <i class="bi bi-bank2"></i> E-Court Elektronik
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navTop">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navTop">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-person-circle"></i>
                        <?= esc(session()->get('nama') ?? 'Guest') ?>
                        <span class="badge bg-light text-primary ms-1">
                            <?= ucfirst(session()->get('primary_role') ?? '-') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="bi bi-person"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('ganti-password') ?>"><i class="bi bi-key"></i> Ganti Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>