<?= $this->extend('layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.wizard-steps { display:flex; justify-content:space-between; margin-bottom:30px; position:relative; }
.wizard-steps::before { content:''; position:absolute; top:20px; left:0; right:0; height:2px; background:#e2e8f0; z-index:0; }
.step-item { flex:1; text-align:center; position:relative; z-index:1; }
.step-circle { width:40px; height:40px; border-radius:50%; background:#e2e8f0; color:#64748b;
    display:flex; align-items:center; justify-content:center; margin:0 auto 8px; font-weight:bold; }
.step-item.active .step-circle { background:#1e40af; color:#fff; }
.step-item.done   .step-circle { background:#16a34a; color:#fff; }
.step-label { font-size:12px; color:#64748b; }
.step-item.active .step-label { color:#1e40af; font-weight:bold; }
.wizard-panel { display:none; }
.wizard-panel.active { display:block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header"><i class="bi bi-file-earmark-plus"></i> Buat Pengaduan Baru</div>
    <div class="card-body">

        <div class="wizard-steps">
            <div class="step-item active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Identitas</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Upload Pengaduan</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Bukti</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Review & Kirim</div>
            </div>
        </div>

        <!-- STEP 1: IDENTITAS -->
        <div class="wizard-panel active" id="panel-1">
            <h5 class="mb-3">Identitas Pengadu</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="pengadu_nama" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instansi <span class="text-danger">*</span></label>
                    <input type="text" id="pengadu_instansi" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP Aktif <span class="text-danger">*</span></label>
                    <input type="text" id="pengadu_no_hp" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="pengadu_email" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat Bersurat <span class="text-danger">*</span></label>
                    <textarea id="pengadu_alamat" class="form-control" rows="2" required></textarea>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Identitas Teradu</h5>
            <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle"></i>
                NIA (Nomor Identitas Advokat) boleh dikosongkan jika belum diketahui. Format: <code>NN.NNNNN</code> (contoh: 12.34567).
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="teradu_nama" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIA Advokat</label>
                    <input type="text" id="teradu_nia" class="form-control"
                           placeholder="12.34567" maxlength="8" inputmode="numeric">
                    <small class="text-muted">Format: NN.NNNNN</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" id="teradu_email" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input type="text" id="teradu_no_hp" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat Bersurat <span class="text-danger">*</span></label>
                    <textarea id="teradu_alamat" class="form-control" rows="2" required></textarea>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Info Perkara</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Jenis Perkara <span class="text-danger">*</span></label>
                    <select id="jenis_perkara_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="1">DKD - Dewan Kehormatan Daerah</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Perihal Singkat <span class="text-danger">*</span></label>
                    <input type="text" id="perihal" class="form-control" required
                           placeholder="Ringkasan singkat perkara">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button class="btn btn-primary" onclick="nextStep(2)">
                    Lanjut <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: UPLOAD PENGADUAN -->
        <div class="wizard-panel" id="panel-2">
            <h5 class="mb-3">Upload Dokumen Pengaduan</h5>
            <div class="alert alert-info small">
                <i class="bi bi-info-circle"></i>
                Format: PDF, DOC, DOCX. Maks 20 MB.
            </div>
            <input type="file" id="file_pengaduan" class="form-control mb-3"
                   accept=".pdf,.doc,.docx">
            <button class="btn btn-primary btn-sm" onclick="uploadPengaduan()">
                <i class="bi bi-cloud-upload"></i> Upload
            </button>
            <div id="status-pengaduan" class="mt-3"></div>

            <div class="mt-4 d-flex justify-content-between">
                <button class="btn btn-secondary" onclick="prevStep(1)">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
                <button class="btn btn-primary" onclick="nextStep(3)">
                    Lanjut <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 3: BUKTI -->
        <div class="wizard-panel" id="panel-3">
            <h5 class="mb-3">Daftar Bukti</h5>
            <div class="alert alert-warning small">
                <i class="bi bi-exclamation-triangle"></i>
                Bukti <strong>boleh dikosongkan</strong> sekarang, tapi <strong>wajib diupload</strong> saat agenda pembuktian.
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" id="judul_bukti" class="form-control" placeholder="Judul / Nama Bukti">
                </div>
                <div class="col-md-5">
                    <input type="file" id="file_bukti" class="form-control"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp4">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="uploadBukti()">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </button>
                </div>
            </div>
            <small class="text-muted d-block mb-3">
                Format: PDF, DOC, DOCX, JPG, PNG (maks 20 MB), MP4 (maks 100 MB)
            </small>

            <div id="list-bukti" class="border rounded p-3 bg-light">
                <em class="text-muted">Belum ada bukti.</em>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <button class="btn btn-secondary" onclick="prevStep(2)">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
                <button class="btn btn-primary" onclick="nextStep(4)">
                    Lanjut <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 4: REVIEW & SUBMIT -->
        <div class="wizard-panel" id="panel-4">
            <h5 class="mb-3">Review Pengaduan</h5>
            <div id="review-content" class="mb-4"></div>

            <div class="alert alert-warning small">
                <i class="bi bi-exclamation-triangle"></i>
                Dengan mengirim, Anda menyatakan data yang diisi benar dan setuju menggunakan layanan e-Court elektronik.
            </div>

            <div class="d-flex justify-content-between">
                <button class="btn btn-secondary" onclick="prevStep(3)">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
                <button class="btn btn-success btn-lg" onclick="submitPerkara()">
                    <i class="bi bi-send-check"></i> Kirim Pengaduan
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let draftId = <?= $draftId ? (int)$draftId : 'null' ?>;
let currentStep = 1;

// Masking NIA otomatis: 1234567 → 12.34567
document.getElementById('teradu_nia').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 7);
    if (v.length > 2) v = v.slice(0, 2) + '.' + v.slice(2);
    e.target.value = v;
});

function showStep(n) {
    document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.step-item').forEach(s => {
        const sNum = parseInt(s.dataset.step);
        s.classList.remove('active', 'done');
        if (sNum === n) s.classList.add('active');
        else if (sNum < n) s.classList.add('done');
    });
    document.getElementById('panel-' + n).classList.add('active');
    currentStep = n;

    if (n === 4) buildReview();
    if (n === 3) loadBukti();
}

function nextStep(n) {
    if (n === 2 && !draftId) {
        // Simpan identitas dulu
        saveIdentitas().then(ok => { if (ok) showStep(2); });
        return;
    }
    showStep(n);
}

function prevStep(n) { showStep(n); }

function saveIdentitas() {
    const payload = {
        draft_id:          draftId,
        pengadu_nama:      document.getElementById('pengadu_nama').value,
        pengadu_instansi:  document.getElementById('pengadu_instansi').value,
        pengadu_no_hp:     document.getElementById('pengadu_no_hp').value,
        pengadu_email:     document.getElementById('pengadu_email').value,
        pengadu_alamat:    document.getElementById('pengadu_alamat').value,
        teradu_nama:       document.getElementById('teradu_nama').value,
        teradu_nia:        document.getElementById('teradu_nia').value,
        teradu_email:      document.getElementById('teradu_email').value,
        teradu_no_hp:      document.getElementById('teradu_no_hp').value,
        teradu_alamat:     document.getElementById('teradu_alamat').value,
        jenis_perkara_id:  document.getElementById('jenis_perkara_id').value,
        perihal:           document.getElementById('perihal').value,
    };

    // Validasi frontend sederhana
    const required = ['pengadu_nama','pengadu_instansi','pengadu_no_hp','pengadu_email',
                      'pengadu_alamat','teradu_nama','teradu_alamat','jenis_perkara_id','perihal'];
    for (const k of required) {
        if (!payload[k]) {
            alert('Field wajib belum lengkap.');
            return Promise.resolve(false);
        }
    }

    return fetch('<?= base_url('perkara/store-identitas') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({...payload, '<?= csrf_token() ?>': '<?= csrf_hash() ?>'})
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            draftId = res.perkara_id;
            if (res.teradu_akun && res.teradu_akun.is_new) {
                alert('Akun Teradu otomatis dibuat:\nUsername: ' + res.teradu_akun.username + '\nPassword: ' + res.teradu_akun.password + '\n\nPanitera akan menyampaikan kredensial ini.');
            }
            return true;
        }
        alert(res.message || 'Gagal menyimpan.');
        return false;
    })
    .catch(e => { alert('Error: ' + e.message); return false; });
}

function uploadPengaduan() {
    const fileInput = document.getElementById('file_pengaduan');
    if (!fileInput.files.length) return alert('Pilih file dulu.');

    const fd = new FormData();
    fd.append('file_pengaduan', fileInput.files[0]);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('perkara/upload-pengaduan') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        document.getElementById('status-pengaduan').innerHTML = res.status === 'success'
            ? '<div class="alert alert-success py-2"><i class="bi bi-check-circle"></i> ' + res.message + '</div>'
            : '<div class="alert alert-danger py-2">' + res.message + '</div>';
    });
}

function uploadBukti() {
    const judul = document.getElementById('judul_bukti').value;
    const file  = document.getElementById('file_bukti').files[0];
    if (!judul || !file) return alert('Judul & file wajib.');

    const fd = new FormData();
    fd.append('judul', judul);
    fd.append('file_bukti', file);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('perkara/upload-bukti') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            document.getElementById('judul_bukti').value = '';
            document.getElementById('file_bukti').value  = '';
            loadBukti();
        } else {
            alert(res.message);
        }
    });
}

function loadBukti() {
    fetch('<?= base_url('perkara/list-bukti') ?>')
    .then(r => r.json())
    .then(res => {
        const el = document.getElementById('list-bukti');
        if (!res.data || !res.data.length) {
            el.innerHTML = '<em class="text-muted">Belum ada bukti.</em>';
            return;
        }
        let html = '<table class="table table-sm mb-0"><thead><tr><th>Kode</th><th>Judul</th><th>File</th><th></th></tr></thead><tbody>';
        res.data.forEach(d => {
            html += '<tr><td><code>' + d.judul.substring(0,5) + '</code></td>'
                 +  '<td>' + d.judul + '</td>'
                 +  '<td><small>' + d.file_name + '</small></td>'
                 +  '<td><button class="btn btn-sm btn-outline-danger" onclick="hapusBukti(' + d.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        });
        el.innerHTML = html + '</tbody></table>';
    });
}

function hapusBukti(id) {
    if (!confirm('Hapus bukti ini?')) return;
    fetch('<?= base_url('perkara/hapus-bukti') ?>/' + id, { method: 'POST',
        headers: {'X-Requested-With':'XMLHttpRequest','<?= csrf_token() ?>':'<?= csrf_hash() ?>'} })
    .then(r => r.json()).then(res => { if (res.status==='success') loadBukti(); });
}

function buildReview() {
    const html = `
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr><th width="30%">Nama Pengadu</th><td>${document.getElementById('pengadu_nama').value}</td></tr>
            <tr><th>Instansi</th><td>${document.getElementById('pengadu_instansi').value}</td></tr>
            <tr><th>No. HP</th><td>${document.getElementById('pengadu_no_hp').value}</td></tr>
            <tr><th>Email</th><td>${document.getElementById('pengadu_email').value}</td></tr>
            <tr><th>Alamat</th><td>${document.getElementById('pengadu_alamat').value}</td></tr>
            <tr><th colspan="2" class="bg-light">Identitas Teradu</th></tr>
            <tr><th>Nama Teradu</th><td>${document.getElementById('teradu_nama').value}</td></tr>
            <tr><th>NIA</th><td>${document.getElementById('teradu_nia').value || '-'}</td></tr>
            <tr><th>Alamat</th><td>${document.getElementById('teradu_alamat').value}</td></tr>
            <tr><th colspan="2" class="bg-light">Perkara</th></tr>
            <tr><th>Jenis</th><td>DKD</td></tr>
            <tr><th>Perihal</th><td>${document.getElementById('perihal').value}</td></tr>
        </table>
    </div>`;
    document.getElementById('review-content').innerHTML = html;
}

function submitPerkara() {
    if (!confirm('Kirim pengaduan sekarang?')) return;
    fetch('<?= base_url('perkara/submit') ?>', { method: 'POST',
        headers: {'X-Requested-With':'XMLHttpRequest','<?= csrf_token() ?>':'<?= csrf_hash() ?>'} })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            alert('Pengaduan berhasil dikirim!');
            window.location.href = '<?= base_url('perkara/show') ?>/' + res.perkara_id;
        } else {
            alert(res.message);
        }
    });
}
</script>
<?= $this->endSection() ?>