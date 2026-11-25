# Rexel Games

Aplikasi toko game Laravel 12 untuk katalog gabungan game PS3/PS4 dan aksesoris, prioritas pembaruan stok aksesoris dengan SPK metode SAW di dashboard admin/karyawan, sesi belanja tamu, checkout, struk, manajemen produk/stok, dan transaksi. Database bawaan memakai SQLite lokal sehingga tidak perlu menyalakan MySQL untuk mulai memakai aplikasi. Bisa diganti ke MySQL melalui `.env`.

## Menjalankan

1. Buka folder ini di Windows.
2. Klik dua kali **Mulai Rexel Games.bat**.
3. Tunggu migrasi database selesai; browser terbuka di `http://127.0.0.1:8000`.
4. Biarkan jendela terminal tetap hidup selama aplikasi digunakan. Tekan `Ctrl+C` untuk berhenti.

PHP 8.2+ dan Composer diperlukan. PHP XAMPP dapat dipakai; launcher mencoba `C:\xampp\php\php.exe` bila `php` tidak ditemukan di PATH. Dependensi Composer sudah disertakan di folder `vendor`.

## Akun demo

- Admin: ID `ADMIN001` atau email `admin@rexel.local`, password `RexelAdmin123!`
- Karyawan: ID `KRY001` atau email `karyawan@rexel.local`, password `RexelStaff123!`

Ganti password demo setelah mulai digunakan. Admin dapat membuat akun karyawan dari dashboard.

## Pembayaran

COD dicatat sebagai belum dibayar sampai staf menandai lunas setelah menerima uang. Virtual Account dan QRIS ditandai **DEMO** dan menampilkan nomor atau QR palsu sebelum struk. Staf dapat menandai ketiganya sebagai lunas untuk simulasi lokal. Integrasi transaksi sungguhan memerlukan kredensial Midtrans Sandbox serta endpoint callback yang divalidasi.

## Data produk

Seeder memuat 40 judul yang tercantum di spesifikasi (25 PS3 dan 15 PS4), dengan harga demo Rp45.000 dan stok tidak terbatas. Game dapat ditambahkan ke keranjang berkali-kali dan pembelian tidak mengurangi stok. Teks spesifikasi menyebut 50 judul, tetapi hanya memberikan 40 nama; judul yang tidak tercantum tidak dibuat-buat. Harga dapat disesuaikan dari panel staf.

Katalog PlayStation terpisah menampilkan PS3 seharga Rp2.500.000 dan PS4 seharga Rp4.800.000, masing-masing dengan stok awal 6 unit. Konsol dapat dibeli sampai stok habis; stoknya dapat diubah dari pengelolaan produk staf tanpa memengaruhi stok game yang tetap tidak terbatas.

Ringkasan dashboard staf “Stok tersedia” menjumlahkan stok terbatas aksesoris dan konsol PS3/PS4. Game dan jasa tidak dijumlahkan karena stoknya tidak terbatas.

## Catatan

- Login staf dilindungi role dan pembatasan percobaan login. Pembeli dapat masuk sebagai tamu; keranjang tersimpan pada session browser.
- Total omzet hanya berasal dari transaksi berstatus Lunas.
- Status staf meliputi Diproses, Siap Diambil, Selesai, dan Dibatalkan. Riwayat stok tersimpan di `stock_logs`.
- Pelanggan dapat melihat struk, tetapi pencetakan hanya tersedia bagi admin untuk pesanan lunas dari dashboard.
- `database/database.sqlite` berisi data lokal. Simpan cadangan file itu secara berkala.

## Cakupan versi ini

Versi ini siap dijalankan untuk alur katalog → keranjang → checkout → struk serta operasi dasar staf. Fitur ekspor laporan Excel/PDF, grafik penjualan, halaman laporan dengan filter tanggal, pengelolaan/edit/hapus akun staf, dan integrasi Midtrans nyata belum disertakan. VA/QRIS hanya pilihan berlabel DEMO. Produk dapat ditambahkan dan stok dapat disesuaikan dari panel staf; formulir edit/hapus produk belum tersedia di antarmuka.
