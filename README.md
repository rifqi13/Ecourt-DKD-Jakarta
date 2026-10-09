# Aplikasi E-Court Dewan Kehormatan Daerah (DKD) — Version CI4

Aplikasi E-Court adalah sistem informasi persidangan etik berbasis web yang dibangun untuk memfasilitasi proses pengaduan, verifikasi, penjadwalan sidang, pengelolaan bukti, hingga pembacaan putusan secara elektronik.

Sistem ini dikembangkan menggunakan framework **CodeIgniter 4**, antarmuka **Bootstrap**, dan database **MySQL**.

---

##  Fitur Utama & Role

1. **Landing Page & Informasi**: Halaman publik berisi informasi tata cara dan alur persidangan e-court.
2. **Registrasi Pengadu**: Pendaftaran akun pengadu disertai lembar persetujuan penggunaan layanan E-Court secara elektronik.
3. **Manajemen Hak Akses & Role (Dapat Diatur Admin)**:
   - **Admin**: Akses penuh ke seluruh sistem, manajemen pengguna, dan pengaturan modul/fitur per role.
   - **Panitera**: Mengelola berkas pengaduan, verifikasi, jadwal sidang, penomoran registrasi, konfirmasi pembayaran administrasi, serta pembuatan akun Pengadu, Teradu, dan Majelis.
   - **Pengadu**: Mengunggah berkas pengaduan, daftar/file bukti, kesimpulan, melihat linimasa sidang, dan menerima salinan putusan.
   - **Teradu**: Mengunggah jawaban pengaduan, daftar/file bukti, kesimpulan, melihat linimasa sidang, dan menerima salinan putusan.
   - **Majelis**: Memeriksa seluruh berkas aduan/jawaban, menyetujui (*approve*) bukti dari kedua belah pihak, serta memantau jadwal/linimasa persidangan.
4. **Dashboard Informatif**: Tampilan statistik dan ringkasan kasus yang dinamis sesuai role yang sedang login.
5. **Linimasa Sidang Interaktif**: Penjadwalan sidang berbasis *timeline* interaktif yang dilengkapi fitur komentar dan unggah dokumen pendukung.

---

##  Alur Kerja Persidangan

1. **Penginputan Aduan & Akun Teradu**:
   - Pengadu mengisi identitas diri dan identitas Teradu, mengunggah dokumen pengaduan (PDF/Word), serta melampirkan berkas bukti (PDF, Word, Gambar, Video).
   - Sistem merekap daftar bukti yang dapat diekspor menjadi PDF.
   - Akun Teradu ter-generate secara otomatis (username & password dikelola Panitera). Saat Teradu login pertama kali, wajib menyetujui persetujuan persidangan elektronik.
2. **Verifikasi & Penomoran Awal**:
   - Panitera memverifikasi kelengkapan berkas aduan dan bukti.
   - Jika lengkap, Panitera memproses **Nomor Register** otomatis (Format: `[Urutan]/DKD/DKI/[Nomor Input]-[Tahun]`).
3. **Hearing & Pembayaran Administrasi**:
   - Panitera membuat jadwal hearing dan menunjuk 1 orang Majelis Hearing.
   - Pengadu memperbaiki pengaduan berdasarkan catatan/koreksi Majelis.
   - Setelah sesuai, Panitera menerbitkan Surat Pemberitahuan Biaya Perkara. Pengadu melakukan pembayaran administrasi sebesar **Rp 5.000.000,-** via Payment Gateway (DOKU/Midtrans).
4. **Penomoran Registrasi Perkara**:
   - Setelah pembayaran terkonfirmasi, Panitera menerbitkan **Nomor Registrasi Perkara** resmi (Format: `No.[Urutan]/PERADI/DKD-JAKARTA/[Tahun]`). Nomor Register awal tetap disimpan sebagai riwayat.
5. **Penyampaian Jawaban Teradu**:
   - Panitera mengirimkan Surat Pengaduan dan Permintaan Jawaban kepada Teradu.
   - Teradu memiliki batas waktu **21 hari kerja** untuk menjawab. Jika belum ada respon, Panitera mengirimkan surat peringatan dengan batas waktu **14 hari kerja**.
   - Jika batas waktu habis tanpa jawaban, Teradu dianggap melepas hak jawabnya dan persidangan tetap dilanjutkan.
6. **Persidangan & Pembacaan Putusan**:
   - **Sidang I**: Pemeriksaan berkas pengaduan, jawaban, dan bukti oleh Majelis.
   - **Sidang II**: Pembuktian lanjutan dan verifikasi bukti.
   - **Sidang III**: Pengunggahan berkas Kesimpulan oleh Pengadu dan Teradu (file bersifat tertutup, hanya dapat dilihat oleh masing-masing pihak dan Majelis).
   - **Sidang IV**: Pembacaan Putusan. Panitera mengunggah salinan Putusan resmi yang dapat diakses oleh Pengadu dan Teradu.

---

## 💻 Persyaratan Server (Requirements)

- **PHP**: Version 8.1 atau lebih baru
- **Ekstensi PHP Wajib**: `intl`, `mbstring`, `json`, `curl`, `gd`, `mysqli`
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache / Nginx
- **Composer**: Terinstal pada server/komputer lokal

---

## ⚙️ Cara Instalasi (CodeIgniter 4)

1. **Kloning Repository**:
   ```bash
   git clone [https://github.com/username/Ecourt-DKD-Jakarta-CI4-Version.git](https://github.com/username/Ecourt-DKD-Jakarta-CI4-Version.git)
   cd Ecourt-DKD-Jakarta-CI4-Version
