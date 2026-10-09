<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle"></i> Registrasi Surat Masuk</div>
    <div class="card-body">
        <form method="post" action="<?= base_url('surat-masuk/store') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">No. Agenda <span class="text-danger">*</span></label>
                    <input type="text" name="no_agenda" class="form-control" value="<?= esc($no_agenda) ?>" readonly>
                    <small class="text-muted">Auto-generate: urut-tahun</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Disposisi <span class="text-danger">*</span></label>
                    <input type="text" name="no_disposisi" class="form-control" value="<?= esc($no_disposisi) ?>" required>
                    <small class="text-muted">Bisa diubah manual jika mengikuti buku fisik.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Surat</label>
                    <input type="text" name="no_surat" class="form-control" value="<?= old('no_surat') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select">
                        <?php foreach ($jenis_list as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tgl Surat</label>
                    <input type="date" name="tgl_surat" class="form-control" value="<?= old('tgl_surat') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tgl Terima <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_terima" class="form-control" value="<?= old('tgl_terima', date('Y-m-d')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Asal Surat <span class="text-danger">*</span></label>
                    <input type="text" name="asal_surat" class="form-control" value="<?= old('asal_surat') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control" value="<?= old('perihal') ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label">File Surat (PDF/DOC, maks 10 MB)</label>
                    <input type="file" name="file_surat" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="<?= base_url('surat-masuk') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>