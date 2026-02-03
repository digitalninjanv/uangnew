# Finance Manager - Aplikasi Pengelola Keuangan Pribadi

Aplikasi web modern untuk mengelola keuangan pribadi dengan fitur lengkap dan tampilan mobile-friendly.

## 🚀 Fitur Utama

### 💰 Multi-Wallet Support
- Kelola berbagai jenis dompet (Bank, E-Wallet, Cash, Tabungan, Investasi)
- Tambah/edit/nonaktifkan dompet dengan mudah
- Kustomisasi warna dan icon untuk setiap dompet
- Otomatis update saldo saat transaksi

### 📊 Tracking Transaksi Lengkap
- Catat pemasukan dan pengeluaran dengan detail
- Filter berdasarkan tipe, dompet, kategori, dan bulan
- Edit dan hapus transaksi kapan saja
- Deskripsi opsional untuk setiap transaksi

### 🏷️ Kategori Fleksibel
- Kategori pemasukan dan pengeluaran terpisah
- Kustomisasi warna dan icon
- Kategori default sudah tersedia
- Tambah kategori sesuai kebutuhan

### 📈 Riwayat Bulanan
- **Fitur Utama**: Lacak perkembangan keuangan dari bulan ke bulan
- Lihat total saldo, pemasukan, dan pengeluaran per bulan
- Grafik interaktif menunjukkan tren keuangan
- Persentase perubahan antar bulan
- Contoh: Jan 1 juta → Feb 3 juta (+200% growth)

### 🎨 Modern & Mobile-First
- Desain modern dengan glassmorphism effect
- Dark theme yang nyaman di mata
- Fully responsive untuk semua ukuran layar
- Animasi smooth dan micro-interactions
- Optimized untuk penggunaan mobile

### 🔒 Keamanan
- Password hashing dengan bcrypt
- CSRF protection
- SQL injection prevention dengan PDO
- Session management yang aman

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx melalui Laragon)
- Browser modern (Chrome, Firefox, Safari, Edge)

## 🛠️ Instalasi

### 1. Setup Database

Jalankan file database.sql untuk membuat database dan tabel:

```bash
# Lewat MySQL CLI
mysql -u root -p
source C:/laragon/www/uangnew/database.sql

# Atau lewat phpMyAdmin
# Import file database.sql
```

### 2. Konfigurasi Database

File `config/database.php` sudah dikonfigurasi untuk Laragon default:
- Host: localhost
- Database: finance_manager
- User: root
- Password: (kosong)

Jika menggunakan konfigurasi berbeda, edit file tersebut sesuai kebutuhan.

### 3. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/uangnew/auth/login.php
```

### 4. Login dengan Demo Account

Aplikasi sudah dilengkapi dengan akun demo:
- **Username**: demo
- **Password**: demo123

Akun demo memiliki data contoh untuk membantu Anda memahami fitur-fitur aplikasi.

## 📖 Cara Penggunaan

### Dashboard
- Lihat ringkasan total saldo across all wallets
- Pemasukan dan pengeluaran bulan ini
- Transaksi terbaru
- Aksi cepat untuk tambah transaksi

### Kelola Dompet
1. Klik menu "Dompet"
2. Klik "Tambah Dompet"
3. Isi nama, pilih tipe, warna, dan icon
4. Set saldo awal (opsional)
5. Klik "Simpan"

**Tipe Dompet:**
- **Bank**: Rekening bank (BCA, Mandiri, dll)
- **E-Wallet**: Dompet digital (GoPay, OVO, Dana, dll)
- **Cash**: Uang tunai
- **Tabungan**: Rekening tabungan
- **Investment**: Investasi

### Tambah Transaksi
1. Klik menu "Transaksi"
2. Klik "Pemasukan" atau "Pengeluaran"
3. Pilih dompet dan kategori
4. Masukkan jumlah dan tanggal
5. Tambahkan deskripsi (opsional)
6. Klik "Simpan"

**Note**: Saldo dompet otomatis ter-update!

### Kelola Kategori
1. Klik menu "Kategori"
2. Klik "Tambah Kategori"
3. Isi nama kategori
4. Pilih tipe (Pemasukan/Pengeluaran)
5. Pilih warna dan icon
6. Klik "Simpan"

### Lihat Riwayat Bulanan
1. Klik menu "Riwayat"
2. Lihat grafik perkembangan saldo
3. Lihat grafik pemasukan vs pengeluaran
4. Scroll down untuk tabel detail bulanan
5. Perhatikan kolom "Perubahan" untuk melihat growth

**Contoh Interpretasi:**
- **Januari**: Saldo Rp 1.000.000
- **Februari**: Saldo Rp 3.000.000 (+200% ↑)
- **Maret**: Saldo Rp 2.500.000 (-16.7% ↓)

## 🏗️ Arsitektur

### Backend
- **Native PHP**: Tanpa framework untuk efisiensi maksimal
- **PDO**: Prepared statements untuk keamanan
- **Database Triggers**: Otomatis update saldo wallet
- **Session Management**: Secure authentication

### Frontend
- **Vanilla CSS**: Custom CSS modern tanpa framework
- **Vanilla JavaScript**: Lightweight dan cepat
- **Chart.js**: Visualisasi data interaktif
- **Font Awesome**: Icons profesional
- **Google Fonts (Inter)**: Typography modern

### Database
- **MySQL**: Relational database yang reliable
- **Triggers**: Automatic balance calculation
- **Indexes**: Query optimization
- **Foreign Keys**: Data integrity

## 📁 Struktur File

```
uangnew/
├── api/
│   ├── wallets.php           # Wallet CRUD API
│   ├── transactions.php      # Transaction CRUD API
│   └── categories.php        # Category CRUD API
├── assets/
│   ├── css/
│   │   ├── style.css        # Base styles
│   │   └── app.css          # App-specific styles
│   └── js/
│       └── main.js          # Main JavaScript
├── auth/
│   ├── login.php            # Login page
│   ├── register.php         # Registration page
│   ├── authenticate.php     # Login handler
│   └── logout.php           # Logout handler
├── config/
│   ├── config.php           # App configuration
│   └── database.php         # Database connection
├── includes/
│   ├── header.php           # Reusable header
│   ├── footer.php           # Reusable footer
│   └── functions.php        # Helper functions
├── index.php                # Dashboard
├── wallets.php              # Wallet management
├── transactions.php         # Transaction management
├── categories.php           # Category management
├── history.php              # Monthly history
├── database.sql             # Database schema
└── README.md                # Documentation
```

## 🔧 Customization

### Mengubah Tema Warna
Edit `assets/css/style.css` di bagian `:root`:
```css
:root {
    --primary: #6366F1;      /* Warna utama */
    --secondary: #10B981;    /* Warna sukses */
    --danger: #EF4444;       /* Warna bahaya */
}
```

### Menambah Tipe Wallet Baru
Edit `wallets.php` array `$walletTypes`

### Menambah Icon/Warna
Edit array `$walletIcons`, `$walletColors`, `$categoryIcons`, `$categoryColors`

## ⚡ Performance

- **Optimized Queries**: Index pada kolom yang sering di-query
- **Lazy Loading**: Chart.js hanya load di halaman history
- **Minimal Dependencies**: Hanya Chart.js untuk visualisasi
- **Database Triggers**: Eliminasi redundant calculations

## 🔐 Security Features

1. **Password Hashing**: bcrypt dengan cost factor 10
2. **CSRF Protection**: Token validation pada semua form
3. **SQL Injection Prevention**: PDO prepared statements
4. **XSS Prevention**: Input sanitization
5. **Session Security**: Regenerate ID after login

## 📱 Mobile Optimization

- Touch-friendly buttons dan controls
- Responsive grid yang adapt ke layar kecil
- Mobile-first CSS approach
- Hamburger menu untuk navigation
- Optimized font sizes untuk mobile

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan MySQL berjalan
- Cek kredensial di `config/database.php`
- Pastikan database `finance_manager` sudah dibuat

### Saldo Tidak Update
- Database triggers mungkin belum ter-create
- Re-import `database.sql`

### Grafik Tidak Muncul
- Pastikan koneksi internet (Chart.js dari CDN)
- Cek console browser untuk errors

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi developer.

## 📄 License

This project is open source and available for personal use.

---

**Dibuat dengan ❤️ menggunakan Native PHP & Modern Web Technologies**
