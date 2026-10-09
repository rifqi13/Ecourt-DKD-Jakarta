<?php $role = session()->get('primary_role'); ?>
<aside class="sidebar bg-white border-end" style="width:260px; min-height: calc(100vh - 56px);">
    <ul class="nav flex-column p-3">

        <li class="nav-item">
            <a class="nav-link <?= uri_string()==='dashboard'?'active':'' ?>" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <?php if (has_permission('surat_masuk','view')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('surat-masuk') ?>">
                <i class="bi bi-envelope-paper"></i> Surat Masuk
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('perkara','view')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('perkara') ?>">
                <i class="bi bi-folder2-open"></i> Perkara
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('pengaduan','create') && $role === 'pengadu'): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('perkara/create') ?>">
                <i class="bi bi-file-earmark-plus"></i> Buat Pengaduan
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('jadwal','view')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('jadwal') ?>">
                <i class="bi bi-calendar-event"></i> Jadwal Sidang
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('user','view')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('users') ?>">
                <i class="bi bi-people"></i> Kelola User
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('role','view') && $role === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('roles') ?>">
                <i class="bi bi-shield-lock"></i> Role & Permission
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item mt-3">
            <small class="text-muted px-2">PENGATURAN</small>
        </li>

        <?php if ($role === 'admin' || $role === 'panitera'): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('hari-libur') ?>">
                <i class="bi bi-calendar-x"></i> Hari Libur
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('jam-kerja') ?>">
                <i class="bi bi-clock-history"></i> Jam Kerja
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('template-surat') ?>">
                <i class="bi bi-file-text"></i> Template Surat
            </a>
        </li>
        <?php endif; ?>

    </ul>
</aside>