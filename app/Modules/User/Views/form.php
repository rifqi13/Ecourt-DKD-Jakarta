<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-plus"></i> <?= $user ? 'Edit' : 'Tambah' ?> User
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('users/' . ($user ? 'update/'.$user['id'] : 'store')) ?>">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" required
                           value="<?= esc($user['nama'] ?? old('nama')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="primary_role" class="form-select" required>
                        <?php foreach ($allowedRoles as $r): ?>
                            <option value="<?= $r ?>" <?= ($user['primary_role'] ?? '')===$r?'selected':'' ?>>
                                <?= ucfirst($r) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= esc($user['email'] ?? old('email')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= esc($user['username'] ?? old('username')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <?= $user ? '' : '<span class="text-danger">*</span>' ?></label>
                    <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
                    <small class="text-muted">
                        <?= $user ? 'Kosongkan jika tidak ingin ubah. ' : '' ?>
                        Min 8 karakter, kombinasi huruf & angka.
                    </small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIA (khusus teradu)</label>
                    <input type="text" name="nia" class="form-control" placeholder="12.34567"
                           maxlength="8" value="<?= esc($user['nia'] ?? old('nia')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instansi</label>
                    <input type="text" name="instansi" class="form-control"
                           value="<?= esc($user['instansi'] ?? old('instansi')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control"
                           value="<?= esc($user['no_hp'] ?? old('no_hp')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= esc($user['alamat'] ?? old('alamat')) ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="must_change_password" value="1"
                               id="mcp" <?= ($user['must_change_password'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mcp">
                            Wajib ganti password saat login pertama
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="<?= base_url('users') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>