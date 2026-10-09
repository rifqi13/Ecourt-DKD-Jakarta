<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-people"></i> Kelola User</span>
        <a href="<?= base_url('users/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Cari nama/email/username/NIA..."
                       value="<?= esc($q) ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <?php
                    $roles = $role === 'admin'
                        ? ['admin','panitera','majelis','pengadu','teradu']
                        : ['pengadu','teradu'];
                    foreach ($roles as $r): ?>
                        <option value="<?= $r ?>" <?= $filter===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= $status==='aktif'?'selected':'' ?>>Aktif</option>
                    <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                    <option value="nonaktif" <?= $status==='nonaktif'?'selected':'' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verifikasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($list)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada user.</td></tr>
                <?php else: foreach ($list as $u): ?>
                    <tr>
                        <td>
                            <strong><?= esc($u['nama']) ?></strong>
                            <?php if ($u['nia']): ?>
                                <br><small class="text-muted">NIA: <?= esc($u['nia']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= esc($u['username']) ?></code></td>
                        <td><small><?= esc($u['email']) ?></small></td>
                        <td>
                            <?php
                            $badge = ['admin'=>'danger','panitera'=>'primary','majelis'=>'info','pengadu'=>'success','teradu'=>'warning'][$u['primary_role']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($u['primary_role']) ?></span>
                        </td>
                        <td>
                            <?php
                            $stBadge = ['aktif'=>'success','pending'=>'warning','nonaktif'=>'secondary'][$u['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $stBadge ?>"><?= ucfirst($u['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($u['email_verified_at']): ?>
                                <i class="bi bi-patch-check-fill text-success" title="Terverifikasi"></i>
                            <?php else: ?>
                                <i class="bi bi-x-circle text-danger" title="Belum verifikasi"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('users/edit/'.$u['id']) ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= base_url('users/toggle-status/'.$u['id']) ?>"
                                   class="btn btn-outline-warning"
                                   data-confirm="Ubah status user ini?"
                                   title="Aktif/Nonaktif">
                                    <i class="bi bi-power"></i>
                                </a>
                                <?php if (!$u['email_verified_at']): ?>
                                    <a href="<?= base_url('users/resend-verification/'.$u['id']) ?>"
                                       class="btn btn-outline-info"
                                       data-confirm="Kirim ulang email verifikasi?"
                                       title="Kirim ulang verifikasi">
                                        <i class="bi bi-envelope"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (has_role('admin')): ?>
                                    <a href="<?= base_url('users/delete/'.$u['id']) ?>"
                                       class="btn btn-outline-danger"
                                       data-confirm="Yakin hapus user ini?">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $pager->links() ?></div>
    </div>
</div>

<?= $this->endSection() ?>