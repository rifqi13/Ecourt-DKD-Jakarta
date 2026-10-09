<?= $this->extend('layouts/main') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/timeline.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header Perkara -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">
                    <?= esc($perkara['no_register_perkara'] ?: ($perkara['no_register_sementara'] ?: 'Draft #' . $perkara['id'])) ?>
                </h5>
                <p class="text-muted small mb-2">
                    <?= esc($perkara['nama_jenis'] ?: 'DKD') ?> —
                    Dibuat: <?= date('d/m/Y H:i', strtotime($perkara['created_at'])) ?>
                </p>
                <span class="badge bg-primary fs-6">
                    <?= strtoupper(str_replace('_', ' ', $perkara['status'])) ?>
                </span>
            </div>
            <div class="text-end">
                <?php if ($perkara['panitera_id']): ?>
                    <small class="text-muted d-block">Panitera</small>
                    <strong><?= esc($perkara['nama_panitera']) ?></strong>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- TABS -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-info">
        <i class="bi bi-info-circle"></i> Info</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-dokumen">
        <i class="bi bi-folder2"></i> Dokumen</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-jadwal">
        <i class="bi bi-calendar-event"></i> Jadwal & Linimasa</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-riwayat">
        <i class="bi bi-clock-history"></i> Riwayat</a></li>
    <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-chat">
        <i class="bi bi-chat-dots"></i> Diskusi
        <span class="badge bg-danger" id="chatBadge" style="display:none;">0</span>
    </a>
</li>
</ul>

<div class="tab-content">
    <!-- TAB INFO -->
    <div class="tab-pane fade show active" id="tab-info">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light"><i class="bi bi-person"></i> Pengadu</div>
                    <div class="card-body">
                        <?php if ($perkara['pengadu']): ?>
                            <p class="mb-1"><strong><?= esc($perkara['pengadu']['nama']) ?></strong></p>
                            <p class="mb-1 small"><i class="bi bi-building"></i> <?= esc($perkara['pengadu']['instansi']) ?></p>
                            <p class="mb-1 small"><i class="bi bi-telephone"></i> <?= esc($perkara['pengadu']['no_hp']) ?></p>
                            <p class="mb-0 small"><i class="bi bi-geo-alt"></i> <?= esc($perkara['pengadu']['alamat']) ?></p>
                        <?php else: ?><em class="text-muted">-</em><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light"><i class="bi bi-person-badge"></i> Teradu</div>
                    <div class="card-body">
                        <?php if ($perkara['teradu']): ?>
                            <p class="mb-1"><strong><?= esc($perkara['teradu']['nama']) ?></strong></p>
                            <p class="mb-1 small"><i class="bi bi-card-text"></i> NIA: <?= esc($perkara['teradu']['nia'] ?: '-') ?></p>
                            <p class="mb-1 small"><i class="bi bi-building"></i> <?= esc($perkara['teradu']['instansi'] ?? '-') ?></p>
                            <p class="mb-0 small"><i class="bi bi-geo-alt"></i> <?= esc($perkara['teradu']['alamat']) ?></p>
                        <?php else: ?><em class="text-muted">-</em><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB DOKUMEN -->
    <div class="tab-pane fade" id="tab-dokumen">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-folder2"></i> Dokumen Perkara</span>
                <?php if (has_permission('bukti','create') && $perkara['status'] === 'pembuktian_pengadu'): ?>
                    <a href="<?= base_url('bukti/upload/' . $perkara['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-cloud-upload"></i> Upload Bukti
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Pihak</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dokumen)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada dokumen.</td></tr>
                        <?php else: foreach ($dokumen as $d): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$d['kategori'])) ?></span></td>
                                <td><?= esc($d['judul'] ?: $d['file_name']) ?></td>
                                <td><?= esc($d['tipe_pihak'] ?: '-') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                <td>
                                    <?php if ($d['status_approve'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif ($d['status_approve'] === 'rejected'): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('dokumen/download/' . $d['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if (has_role('majelis') && $d['status_approve'] === 'pending' && $d['kategori'] === 'bukti'): ?>
                                        <a href="<?= base_url('majelis/approve-bukti/' . $d['id']) ?>"
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB JADWAL & LINIMASA -->
    <div class="tab-pane fade" id="tab-jadwal">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-calendar-event"></i> Linimasa Jadwal</span>
                <?php if (has_role('admin','panitera') && in_array($perkara['status'], ['register_sementara','hearing'])): ?>
                    <a href="<?= base_url('jadwal/create/' . $perkara['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Buat Jadwal
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($jadwal)): ?>
                    <p class="text-muted text-center py-4">Belum ada jadwal.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($jadwal as $j): ?>
                            <div class="timeline-item <?= $j['status'] === 'selesai' ? 'done' : ($j['status'] === 'dijadwalkan' ? 'pending' : '') ?>">
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?= esc($j['judul']) ?></h6>
                                            <span class="badge bg-<?= $j['status']==='selesai'?'success':'warning' ?>">
                                                <?= ucfirst($j['status']) ?>
                                            </span>
                                        </div>
                                        <p class="small text-muted mb-2">
                                            <i class="bi bi-clock"></i>
                                            <?= date('d M Y H:i', strtotime($j['tanggal_mulai'])) ?>
                                            <?php if ($j['lokasi']): ?>
                                                — <i class="bi bi-geo-alt"></i> <?= esc($j['lokasi']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($j['agenda']): ?>
                                            <p class="small mb-2"><?= nl2br(esc($j['agenda'])) ?></p>
                                        <?php endif; ?>
                                        <a href="<?= base_url('jadwal/show/' . $j['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Detail & Komentar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB RIWAYAT -->
    <div class="tab-pane fade" id="tab-riwayat">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> Riwayat Status</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Waktu</th><th>Aksi</th><th>Oleh</th><th>Catatan</th></tr></thead>
                    <tbody>
                    <?php
                    $db = \Config\Database::connect();
                    $log = $db->table('log_aktivitas')
                        ->where('modul', 'perkara')->where('ref_id', $perkara['id'])
                        ->orderBy('created_at', 'DESC')->get()->getResultArray();
                    foreach ($log as $l):
                        $u = $db->table('users')->where('id', $l['user_id'])->get()->getRowArray();
                    ?>
                        <tr>
                            <td><small><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></small></td>
                            <td><?= esc($l['aksi']) ?></td>
                            <td><?= esc($u['nama'] ?? 'System') ?></td>
                            <td><small><?= esc($l['deskripsi'] ?? '-') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Sebelum </div> terakhir, tambahkan: -->
<div class="tab-pane fade" id="tab-chat">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-chat-dots"></i> Diskusi Perkara
            <small class="text-muted ms-2">Komunikasi resmi antara pihak & panitera</small>
        </div>
        <div class="card-body p-0">
            <div id="chat-box" style="height:420px;overflow-y:auto;background:#f8fafc;padding:16px;">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-chat-square-text fs-1"></i>
                    <p class="mt-2 mb-0">Belum ada diskusi</p>
                </div>
            </div>

            <div class="p-3 border-top bg-white">
                <form id="chat-form" class="d-flex gap-2" onsubmit="return false;">
                    <input type="text" id="chat-input" class="form-control"
                           placeholder="Tulis pesan..." maxlength="1000" autocomplete="off">
                    <label class="btn btn-outline-secondary mb-0" title="Lampiran">
                        <i class="bi bi-paperclip"></i>
                        <input type="file" id="chat-file" hidden
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp4,.zip">
                    </label>
                    <button class="btn btn-primary" onclick="kirimPesan()">
                        <i class="bi bi-send"></i>
                    </button>
                </form>
                <small class="text-muted" id="file-info"></small>
            </div>
        </div>
    </div>
</div>
</div>

<?= $this->endSection() ?>

<!-- Tambahkan di section scripts: -->
<script>
const PERKARA_ID = <?= (int)$perkara['id'] ?>;
let lastChatId = 0;
let chatPolling;

function renderChat(data, append = true) {
    const box = document.getElementById('chat-box');
    if (!data.length && append && !box.querySelector('.chat-item')) {
        return; // biarkan placeholder
    }
    if (append && data.length) {
        const ph = box.querySelector('.text-center');
        if (ph) ph.remove();
    }
    data.forEach(d => {
        lastChatId = Math.max(lastChatId, parseInt(d.id));
        const side = d.is_mine ? 'text-end' : 'text-start';
        const bg   = d.is_mine ? 'bg-primary text-white' : 'bg-white border';
        const align = d.is_mine ? 'justify-content-end' : '';
        let fileHtml = '';
        if (d.file_path) {
            fileHtml = `<div class="mt-1"><a href="${d.file_url}" target="_blank" class="small ${d.is_mine?'text-white':'text-primary'}"><i class="bi bi-paperclip"></i> ${d.file_name}</a></div>`;
        }
        const html = `
            <div class="chat-item d-flex ${align} mb-2">
                <div style="max-width:75%;">
                    ${!d.is_mine ? `<small class="text-muted d-block mb-1"><strong>${d.nama}</strong> <span class="badge bg-secondary" style="font-size:10px;">${d.primary_role}</span></small>` : ''}
                    <div class="p-2 px-3 rounded-3 ${bg}">
                        <div style="white-space:pre-wrap;">${escapeHtml(d.pesan)}</div>
                        ${fileHtml}
                    </div>
                    <small class="text-muted" style="font-size:11px;">
                        ${new Date(d.created_at).toLocaleString('id-ID')}
                        ${d.is_mine ? `<a href="#" onclick="hapusPesan(${d.id});return false;" class="text-danger ms-1"><i class="bi bi-trash"></i></a>` : ''}
                    </small>
                </div>
            </div>`;
        box.insertAdjacentHTML('beforeend', html);
    });
    box.scrollTop = box.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function loadChat() {
    fetch(`<?= base_url('perkara/chat/list/') ?>${PERKARA_ID}?after_id=${lastChatId}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.data.length) {
                renderChat(res.data);
            }
        });
}

function kirimPesan() {
    const input = document.getElementById('chat-input');
    const fileInput = document.getElementById('chat-file');
    const pesan = input.value.trim();
    if (!pesan && !fileInput.files.length) return;

    const fd = new FormData();
    fd.append('pesan', pesan);
    if (fileInput.files.length) fd.append('file_lampiran', fileInput.files[0]);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch(`<?= base_url('perkara/chat/kirim/') ?>${PERKARA_ID}`, { method:'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                input.value = '';
                fileInput.value = '';
                document.getElementById('file-info').textContent = '';
                loadChat();
            } else {
                alert(res.message);
            }
        });
}

function hapusPesan(id) {
    if (!confirm('Hapus pesan ini?')) return;
    fetch(`<?= base_url('perkara/chat/hapus/') ?>${id}`, {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','<?= csrf_token() ?>':'<?= csrf_hash() ?>'}
    }).then(r => r.json()).then(res => {
        if (res.status === 'success') {
            document.getElementById('chat-box').innerHTML = '';
            lastChatId = 0;
            loadChat();
        }
    });
}

document.getElementById('chat-file').addEventListener('change', function() {
    document.getElementById('file-info').textContent = this.files.length
        ? '📎 ' + this.files[0].name : '';
});

// Load awal + polling 10 detik
document.addEventListener('DOMContentLoaded', () => {
    loadChat();
    chatPolling = setInterval(loadChat, 10000);
    // stop polling saat tab tidak aktif
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) clearInterval(chatPolling);
        else chatPolling = setInterval(loadChat, 10000);
    });
});
</script>

<?= $this->endSection() ?>