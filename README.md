<h1 align="center">💼 HRIS Payroll System</h1>

<p align="center">
  <strong>Sistem Manajemen SDM & Penggajian Karyawan Berbasis Web</strong><br/>
  Human Resource Information System — dibangun dengan Laravel 13
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/SQLite-Database-003B57?style=for-the-badge&logo=sqlite&logoColor=white"/>
  <img src="https://img.shields.io/badge/Vite-Frontend-646CFF?style=for-the-badge&logo=vite&logoColor=white"/>
  <img src="https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge"/>
</p>

---

## 📋 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack)
- [Struktur Proyek](#-struktur-proyek)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi Database](#-konfigurasi-database)
- [Akun Default](#-akun-default)
- [Modul & Fitur Detail](#-modul--fitur-detail)
- [Sistem Hak Akses](#-sistem-hak-akses)
- [Struktur Database](#-struktur-database)
- [Penggunaan](#-penggunaan)

---

## 🏢 Tentang Aplikasi

**HRIS Payroll System** adalah aplikasi manajemen sumber daya manusia dan penggajian berbasis web yang dirancang untuk memudahkan pengelolaan data karyawan, absensi, lembur, kasbon, dan proses payroll secara menyeluruh.

Aplikasi ini memungkinkan organisasi untuk:
- Mengelola seluruh data kepegawaian dari satu platform terpadu
- Mengotomatiskan proses perhitungan gaji dan payroll bulanan
- Memantau kehadiran, lembur, dan kasbon karyawan secara real-time
- Menghasilkan slip gaji dan laporan penggajian dalam format PDF
- Mengimpor/ekspor data karyawan dan absensi melalui file Excel

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 👥 **Manajemen Karyawan** | CRUD data karyawan lengkap, import/export Excel, soft delete & restore |
| 📅 **Absensi** | Pencatatan kehadiran harian, import massal via Excel, rekap tahunan |
| ⏰ **Lembur** | Pengajuan & approval lembur, perhitungan otomatis tarif per jam |
| 💰 **Kasbon** | Manajemen pinjaman karyawan dengan alur approve/reject/lunas |
| 💵 **Penggajian (Payroll)** | Generate payroll otomatis, slip gaji PDF, komponen BPJS |
| 🏢 **Master Data** | Instansi, Divisi, Jabatan, Status Karyawan, Komponen Gaji |
| 🔐 **Hak Akses (RBAC)** | Role-based access control per modul dan per aksi |
| 🔑 **Reset Password** | Alur permintaan reset password dengan approval Super Admin |
| 📊 **Dashboard** | Ringkasan statistik SDM & penggajian |

---

## 🛠 Tech Stack

### Backend
- **Framework:** [Laravel](https://laravel.com) `^13.8`
- **PHP:** `^8.3`
- **Authentication:** Laravel Breeze (session-based)
- **PDF Generation:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) `^3.1`
- **Excel Import/Export:** [Maatwebsite/Laravel-Excel](https://laravel-excel.com) `^3.1`

### Frontend
- **Build Tool:** [Vite](https://vitejs.dev)
- **Asset Bundling:** npm

### Database
- **Default:** SQLite (file-based, zero-config)
- **Supported:** MySQL / MariaDB (opsional)

### Dev Tools
- **Laravel Pint** — PHP code formatter
- **Laravel Pail** — Real-time log viewer
- **PHPUnit `^12`** — Unit & feature testing
- **Faker** — Data seeding & factory

---

## 📁 Struktur Proyek

```
payroll_gaji/
├── app/
│   ├── Console/            # Artisan commands
│   ├── Exports/            # Excel export classes
│   ├── Http/
│   │   ├── Controllers/    # 18 controllers (Auth, Payroll, Karyawan, dll)
│   │   └── Middleware/     # Middleware hak akses (hak_akses)
│   ├── Imports/            # Excel import classes
│   ├── Models/             # 19 Eloquent models
│   ├── Providers/          # Service providers
│   └── Services/           # Business logic services
├── database/
│   ├── migrations/         # 34 migration files
│   ├── seeders/            # Data seeder (Role, HakAkses, Tarif, dll)
│   └── database.sqlite     # SQLite database file
├── resources/
│   └── views/              # Blade templates
├── routes/
│   └── web.php             # 60+ named routes
├── public/                 # Static assets & entry point
└── storage/                # Logs, cache, file uploads
```

---

## 💻 Persyaratan Sistem

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| SQLite | 3.x (atau MySQL 8.0+) |

---

## 🚀 Instalasi & Setup

### 1. Clone Repository

```bash
git clone <repository-url>
cd payroll_gaji
```

### 2. Setup Otomatis *(Direkomendasikan)*

```bash
composer run setup
```

Script ini akan menjalankan secara berurutan:
- `composer install` — install dependensi PHP
- Copy `.env.example` → `.env`
- Generate `APP_KEY`
- Jalankan semua migrasi database
- `npm install` — install dependensi frontend
- `npm run build` — build asset production

### 3. Setup Manual *(Alternatif)*

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin file environment
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Buat file database SQLite
touch database/database.sqlite

# 5. Jalankan migrasi
php artisan migrate

# 6. Jalankan seeder (data awal & akun default)
php artisan db:seed

# 7. Install dependensi frontend
npm install

# 8. Build asset
npm run build
```

### 4. Jalankan Development Server

```bash
# Jalankan semua service sekaligus (server + queue + logs + vite)
composer run dev
```

Atau secara terpisah:

```bash
php artisan serve   # Web server → http://localhost:8000
npm run dev         # Vite dev server (hot reload)
```

---

## 🗃 Konfigurasi Database

Secara default aplikasi menggunakan **SQLite**. Konfigurasi ada di `.env`:

```env
DB_CONNECTION=sqlite
```

### Beralih ke MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_payroll
DB_USERNAME=root
DB_PASSWORD=your_password
```

Kemudian jalankan ulang migrasi:

```bash
php artisan migrate:fresh --seed
```

---

## 🔑 Akun Default

Setelah menjalankan seeder (`php artisan db:seed`), akun berikut tersedia:

| Username | Password | Role |
|----------|----------|------|
| `admin` | `password` | Super Admin |
| `manager` | `password` | Manager |
| `staff_admin` | `password` | Admin |

> ⚠️ **Penting:** Segera ganti password default setelah login pertama, terutama di environment production.

---

## 📦 Modul & Fitur Detail

### 1. 👥 Data Karyawan
- Tambah, edit, lihat detail, dan hapus data karyawan
- Import data massal via file **Excel** (`.xlsx`)
- Export daftar karyawan ke **Excel**
- **Soft Delete** — karyawan non-aktif dapat di-restore kapan saja
- Pencatatan tanggal & alasan resign
- Kalkulasi otomatis: **Umur** dan **Masa Kerja**

### 2. 📅 Absensi
- Pencatatan kehadiran harian: Masuk / Izin / Alpha
- Import data absensi massal via **Excel**
- Rekap absensi tahunan per karyawan
- Histori absensi lengkap dengan filter

### 3. ⏰ Lembur
- Pengajuan lembur dengan detail jam dan kategori
- **Alur Approval:** Pengajuan → Review Manager → Disetujui / Ditolak
- Perhitungan tarif lembur otomatis berdasarkan `pengaturan_tarif`
- Kategori lembur yang dapat dikonfigurasi

### 4. 💰 Kasbon *(Pinjaman Karyawan)*
- Pencatatan pengajuan pinjaman karyawan
- Alur status: **Pending → Approve / Reject → Lunas**
- Riwayat kasbon per karyawan
- Potongan kasbon otomatis saat generate payroll

### 5. 💵 Payroll & Penggajian
- **Generate payroll otomatis** dari data kehadiran, lembur, dan kasbon
- Komponen gaji: Gaji Pokok, Tunjangan, BPJS Karyawan, BPJS Kantor, Kasbon, Potongan Lain
- **Review payroll** sebelum difinalisasi
- Cetak **Slip Gaji PDF** per karyawan via DomPDF
- Histori payroll bulanan per karyawan

### 6. 🏢 Master Data

| Master | Keterangan |
|--------|------------|
| Instansi | Data perusahaan/kantor (termasuk logo) |
| Divisi | Departemen/bagian dalam organisasi |
| Jabatan | Posisi/jabatan karyawan |
| Status Karyawan | Tetap, Kontrak, Probation, dll |
| Komponen Gaji | Template struktur gaji pokok |
| Kategori Lembur | Jenis lembur yang berlaku |
| Pengaturan Tarif | Tarif lembur & konfigurasi jam kerja |

### 7. 🔐 Manajemen User
- Tambah, edit, hapus akun pengguna sistem
- Assign role: Super Admin / Manager / Admin
- Alur permintaan reset password dengan approval

---

## 🔐 Sistem Hak Akses

Aplikasi menggunakan **Role-Based Access Control (RBAC)** yang dikonfigurasi secara granular per modul dan per aksi melalui tabel `hak_akses_roles`.

### Role yang Tersedia

| Role | Deskripsi |
|------|-----------|
| **Super Admin** | Akses penuh ke seluruh sistem termasuk konfigurasi hak akses |
| **Manager** | Akses operasional + approval lembur & kasbon |
| **Admin** | Akses operasional sesuai hak yang diberikan |

### Modul yang Dapat Dikontrol

Setiap modul memiliki 4 tipe izin: **Read · Create · Update · Delete**

```
user_management    instansi          divisi
jabatan            status_karyawan   gaji_pokok
karyawan           gaji_karyawan     absensi
lembur             kasbon            payroll
```

Konfigurasi dapat diubah melalui menu **Hak Akses** oleh Super Admin.

---

## 🗄 Struktur Database

Aplikasi memiliki **34 migration files** dengan skema utama:

```
roles                        — Role pengguna sistem
users                        — Akun pengguna (login)
hak_akses_roles              — Konfigurasi RBAC per role
instansis                    — Data perusahaan/instansi
divisis                      — Divisi/departemen
jabatans                     — Jabatan karyawan
statuses                     — Status kepegawaian
karyawans                    — Data karyawan (soft delete)
absensis                     — Kehadiran harian karyawan
kategori_lemburs             — Kategori jenis lembur
lemburs                      — Data lembur & approval flow
kasbons                      — Pinjaman/kasbon karyawan
komponen_gajis               — Template komponen gaji
gaji_karyawans               — Gaji pokok per karyawan
detail_gaji_karyawans        — Rincian komponen gaji
payrolls                     — Payroll bulanan
detail_payrolls              — Rincian komponen payroll (BPJS, dll)
pengaturan_tarif             — Konfigurasi tarif & jam kerja
permintaan_reset_passwords   — Request reset password
```

---

## 🧪 Penggunaan

### Menjalankan Tests

```bash
composer run test

# Atau langsung dengan artisan
php artisan test
```

### Menjalankan Seeder

```bash
# Semua seeder
php artisan db:seed

# Seeder spesifik
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=HakAksesRoleSeeder
php artisan db:seed --class=PengaturanTarifSeeder
php artisan db:seed --class=KategoriLemburSeeder
```

### Reset & Rebuild Database

```bash
php artisan migrate:fresh --seed
```

### Log Real-time

```bash
php artisan pail
```

### Format Kode PHP

```bash
./vendor/bin/pint
```

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan internal.  
Framework Laravel dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---

<p align="center">
  Dibuat dengan ❤️ menggunakan <a href="https://laravel.com">Laravel 13</a> & PHP 8.3
</p>
