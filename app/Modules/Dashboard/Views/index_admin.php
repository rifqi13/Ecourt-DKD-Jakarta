<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Perkara',   'value'=>$stats['total_perkara'],   'icon'=>'folder2-open',    'color'=>'primary'],
        ['label'=>'Perkara Aktif',   'value'=>$stats['perkara_aktif'],   'icon'=>'hourglass-split', 'color'=>'warning'],
        ['label'=>'Menunggu Verif',  'value'=>$stats['menunggu_verif'],  'icon'=>'clipboard-check', 'color'=>'info'],
        ['label'=>'Menunggu Bayar',  'value'=>$stats['menunggu_bayar'],  'icon'=>'credit-card',     'color'=>'danger'],
    ];
    foreach ($cards as $c): ?>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon bg-<?= $c['color'] ?> bg-opacity-10 text-<?= $c['color'] ?>">
                        <i class="bi bi-<?= $c['icon'] ?>"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small"><?= $c['label'] ?></div>
                        <div class="fs-4 fw-bold"><?= $c['value'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-info-circle"></i> Selamat datang, <?= esc(session()->get('nama')) ?></div>
    <div class="card-body">
        <p class="mb-0">Anda login sebagai <strong>Administrator</strong>. Anda memiliki akses penuh ke seluruh modul.</p>
    </div>
</div>

<?= $this->endSection() ?>