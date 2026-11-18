PANDUAN PENGGUNAAN APLIKASI DIARYBOOK (LARAGON)
================================================
1. Ekstrak folder 'diarybook' ini ke dalam: C:\laragon\www\diarybook
2. Jalankan Laragon (Start All).
3. Buka HeidiSQL (klik tombol Database di Laragon) -> buat database 'diarybook' -> jalankan isi file 'database.sql'.
4. Buka browser dan akses: http://localhost/diarybook/setup_kunci.php
   -> Salin Kunci Simetris dan seluruh isi Private Key yang tampil di layar.
   -> Simpan keduanya di tempat aman. Private Key tidak dapat ditampilkan kembali.
5. Klik 'Lanjut ke Landing Page' -> 'Masuk ke Halaman Admin'.
6. Tempel kedua kunci dari langkah 4 ke kolom login. Kunci Simetris harus sama dengan yang disimpan saat setup.
7. Selesai! Silakan input data dan lihat hasil enkripsinya.

Catatan: Membuka atau me-refresh setup_kunci.php tidak mengganti kunci yang sudah ada.
Gunakan tombol reset hanya jika Private Key hilang/tidak cocok. Reset mengganti pasangan
RSA; data catatan tetap ada dan untuk membacanya tetap diperlukan Kunci Simetris lama.
