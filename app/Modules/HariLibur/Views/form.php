<!-- app/Modules/HariLibur/Views/form.php -->
<div class="card">
  <div class="card-header bg-primary text-white">
    <i class="bi bi-calendar-x"></i> Tambah Hari Libur / Cuti Bersama
  </div>
  <div class="card-body">
    <form method="post" action="<?= base_url('hari-libur/store') ?>">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
        <input type="date" name="tanggal" class="form-control" required
               min="<?= date('Y-m-d') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Keterangan <span class="text-danger">*</span></label>
        <input type="text" name="keterangan" class="form-control" required
               placeholder="Contoh: Cuti bersama HUT RI">
      </div>

      <div class="mb-3">
        <label class="form-label">Sumber</label>
        <select name="sumber" class="form-select">
          <option value="cuti_bersama">Cuti Bersama</option>
          <option value="custom">Custom / Internal</option>
          <option value="nasional">Libur Nasional</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Jenis</label>
        <select name="is_kerja" class="form-select">
          <option value="0">Libur (tanggal merah)</option>
          <option value="1">Hari Kerja Pengganti</option>
        </select>
      </div>

      <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
      <a href="<?= base_url('hari-libur') ?>" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>