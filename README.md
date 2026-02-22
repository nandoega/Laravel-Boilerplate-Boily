<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

# Boily: Ultimate Laravel 12 API & Dashboard Boilerplate

Selamat datang di **Boily**. Ini adalah fondasi berstandar industri (Enterprise-grade) yang dirancang khusus untuk membangun Backend Web API berkinerja tinggi, aman, bersih, sekaligus dilengkapi dengan Web Dashboard UI siap pakai berbasis Blade dan Bootstrap 5.

Project ini dibuat ramah untuk pemula namun secara arsitektur sangat direkomendasikan untuk produksi di skala besar. Dibuat dan dikembangkan oleh **Nando Ega**.

**DISCLAIMER: JANGAN PAKAI KODE INI MENTAHAN, KODE INI HANYA UNTUK PEMBELAJARAN DAN PENGUJIAN API. KODE DIBUAT UNTUK MEMBERI CONTOH TERKAIT BACKEND LARAVEL YANG CEPAT, SECURE, SCALABLE, DAN ARSITEKTUR YANG BERSIH NAN RAPI.SESUAIKAN KODE INI DENGAN KEBUTUHAN USER!**

---

## Fitur Utama

Boily sudah dilengkapi dengan 9 Modul Bisnis Utama yang berjalan penuh:

1. 🔐 **Authentication & RBAC:** Sistem Login berbasis Token (Sanctum) dengan Role-Based Access Control (Super Admin, Admin, Manager, User).
2. 🏢 **Client Management:** Kelola data Klien atau Perusahaan lengkap dengan validasi RESTful.
3. 🚀 **Project Management:** Kelola Proyek, Status, Anggaran (Budget), dan alokasi member tim.
4. ✅ **Task Board:** Kelola Tugas (Tasks), Status (Pending, In Progress, dll), Prioritas, dan penugasan (Assignee).
5. 👥 **Team Management:** Buat Tim dan gabungkan berbagai staf ke dalam sebuah departemen.
6. 🧾 **Invoicer (Penagihan):** Buat tagihan langsung dari proyek, atur Status (Draft, Sent, Paid), dan kalkulasi pajak otomatis.
7. 💳 **Payments (Pembayaran):** Catat pembayaran masuk dari Invoice dan fungsionalitas pengembalian dana (Refund).
8. ⏱️ **Time Entries (Timesheet):** Melacak jam kerja per Tugas yang nantinya bisa dikalkulasikan menjadi Billable Hours.
9. 📊 **Reports & Analytics:** Dashboard analitik (Cash flow, Project Profitability) yang dieksekusi cepat menggunakan skema *Tiered Caching*.

---

## Struktur Folder dan Pola Desain (Architecture Guide)

Bagi pengembang yang baru belajar Backend dan Laravel, sangat penting untuk memahami di mana peletakan logika bisnis yang tepat. Proyek ini menggunakan arsitektur Repository-Service Pattern.

```text
app/
 ├── Http/
 │    ├── Controllers/Api/V1/  -> Pintu masuk API. Semua Request HTTP diterima dan mereturn Response melalui BaseController.
 │    ├── Requests/            -> Lapisan Form Validation. Memastikan input (contoh: email, string unik) valid sebelum masuk ke Controller.
 │    ├── Resources/           -> Transformer (Pembuat Format JSON). Mengubah struktur data mentah menjadi JSON yang terstandarisasi untuk Frontend/Mobile.
 │    └── Middleware/          -> Lapisan keamanan request. Mencegah akses tak sah dan menangani pemaksaan format respons JSON.
 │
 ├── Models/                   -> Representasi dari tabel database. Setiap Model secara langsung mewakili entitas tabel di MySQL.
 │
 ├── Repositories/             -> Lapisan Akses Data. Semua query "SELECT/INSERT/UPDATE" berada di sini. Memisahkan logika kueri murni dari Service.
 │    ├── Contracts/           -> Cetak Biru (Interface) dari Methods yang diwajibkan untuk diimplementasi.
 │    └── Eloquent/            -> Implementasi nyata dari query builder Laravel Eloquent (mengandung fungsi cache).
 │
 └── Services/                 -> OTAK APLIKASI (Logic Layer). Di sinilah logika bisnis utama dan flow validasi kompleks ditempatkan.
```

**Alur Kerja Request (Request Workflow):**
`Client/Network` -> `Router` -> `Controller` -> `Service` -> `Repository` -> `Model` -> `Database`.

Dengan penerapan pemisahan pola ini, pemeliharaan kode menjadi sangat bersih. Jika Anda berniat mengubah database dari MySQL, Anda hanya butuh memperbarui layer `Repository` saja tanpa merusak logika aplikasi di `Service`.

---

## Cara Menjalankan Proyek

Langkah-langkah untuk menjalankan aplikasi secara lokal (Development Environment):

1. Buka Terminal di direktori utama proyek.
2. Install semua library PHP via Composer:
   ```bash
   composer install
   ```
3. Salin file konfigurasi *environment* dan sesuaikan kredensial koneksi Database MySQL Anda:
   ```bash
   cp .env.example .env
   ```
4. Masukkan kunci keamanan aplikasi bawaan Laravel:
   ```bash
   php artisan key:generate
   ```
5. Eksekusi file migrasi (untuk membangun struktur tabel) beserta file Seeder (untuk mengisi master role dan Super Admin):
   ```bash
   php artisan migrate:fresh --seed
   ```
6. Aktifkan local server development:
   ```bash
   php artisan serve
   ```
Sekarang, API dan Dashboard berjalan pada alamat `http://localhost:8000`.

---

## Cara Menggunakan Web Dashboard

Meskipun ini adalah proyek inti API Boilerplate, saya menyertakan "Dashboard Client" menggunakan Laravel Blade dan Bootstrap 5. Tampilan ini 100% menggunakan Javascript (Fetch API) untuk melakukan permintaan data ke endpoint API lokal, seolah-olah beroperasi sebagai aplikasi pihak ketiga/berbeda.

1. Buka browser pada alamat: `http://localhost:8000`
2. Kredensial default untuk login (Otomatis digenerate melalui proses Seeder):
   - **Email:** `admin@example.com`
   - **Password:** `admin123`
3. Seluruh manajemen sistem navigasi UI seperti create, update, dan tracking data secara asinkron (AJAX) akan berjalan menggunakan Token Sanctum yang menempel di `localStorage` peramban Anda.

---

## Tutorial Pengujian API Menggunakan REST Client

Bagi para Backend Developer, pengujian API via integrasi *HTTP Client* adalah rutinitas utama. Anda tidak perlu menyalin dan merangkai cURL per endpoint.

Saya sudah menyiapkan direktori dan file **`api.http`** yang terletak di partisi terluar proyek. File ini berisi dokumentasi dan pengujian live seluruh *endpoint*.

**Panduan Penggunaan via Code Editor (VS Code / PhpStorm):**
1. Instal dan aktifkan plugin **REST Client** (Ekstensi VS Code).
2. Buka dan muat file terlampir `api.http`.
3. Anda akan melacak tulisan "Send Request" di setiap definisi Endpoint URL.
4. **Langkah 1:** Lakukan eksekusi (klik Send Request) tepat di bagian skrip `# 1. AUTHENTICATION (Login)`.
5. Ekstensi tersebut secara otomatis akan menyimpan string Bearer Token di virtual memorinya.
6. **Langkah 2:** Anda sekarang bebas mengklik tombol "Send Request" mana saja pada endpoint-endpoint lainnya (Get Clients, Show Reports, Create Project) tanpa repot mendaftarkan Token secara manual setiap saat.

---

## Spesifikasi Lanjutan Aplikasi (Advanced Engineering)

Boily Boilerplate dikonfigurasi menahan standar korporasi secara bawaan. Beberapa di antaranya:

- 🛡️ **100% Prepared Statements (MySQL):** Kode dilindungi paksa terhadap vektor serangan SQL Injection tingkat fatal.
- 🚦 **Strict Rate Limiting (API Throttling):**
  - Autentikasi/Login System: Dibatasi maksimal 5 requests per menit. Mencegah vektor peretasan berbasis *Brute-Force*.
  - Global Application Scope: Batas 60 hits per menit untuk melimitasi resiko atas serangan Spam dan *DDoS BOT*.
- 🔒 **OWASP Secure HTTP Headers:** Standar filter Security Headers OWASP yang termodifikasi untuk mengeliminasi Clickjacking, mencegah kerentanan Mime Sniffing, dan injeksi *Cross-Site Scripting* (XSS) murni melalui Header Response.
- ⚡ **Query Optimization dan Eager Loading:** Mengatasi anomali redundansi *N+1 Query Problem*. Mekanisme di sistem Repository secara dinamis memuat rantai relasi Database dalam hanya 1 kueri agregat.
- 🚀 **Tiered Hybrid Caching System:** Mengimplementasikan penyimpanan sesi di RAM Memory Engine (spt Redis) untuk menangani Job rate-limiting, sedangkan Master Data menggunakan skema *File Driver Storage* agar operasional server RAM tetap ringan dan tidak memberatkan *System Resource Overhead*.

---

### Panduan File Penting (File Dictionary)
Berikut adalah daftar file kunci di dalam `app/` dan `resources/` yang paling sering diganti, ditambah, atau dikembangkan. Panduan penjelasan mandiri ini **spesifik hanya untuk arsitektur Boily Boilerplate**:

#### Direktori App (Backend Core)
- **`app/Models/User.php` (Model):** Representasi tabel database *users*. Di sinilah Anda mengatur relasi tabel (contoh: *User hasMany Tasks*), mendaftarkan *scope query* (`scopeActive`), dan membuat fungsi identitas Role. Tersambung otomatis ke database via Eloquent.
- **`app/Http/Controllers/Api/V1/*` (Controllers):** Pintu penerimaan request dari Web/Mobile (contoh: POST `/clients`). File ini hanya bertugas meneruskan dan membalas status HTTP, **dilarang keras** menaruh rumus logika bisnis yang panjang di sini. Ia memanggil isi `app/Services/`.
- **`app/Services/*` (Services):** **OTAK APLIKASI**. Di sinilah semua hitungan pajak, *looping* cek kondisi asinkron, dan operasi kompleks ditempatkan. Service tidak memanggil database secara mentah, melainkan meminta data ke `app/Repositories/`.
- **`app/Repositories/Eloquent/*` (Repositories):** Layer khusus yang berisi query murni MySQL (contoh: *Insert*, *Update*, `whereId()`). Tujuannya agar bila database Anda berganti sistem, Anda cukup mengedit sisi repository tanpa merusak *Business Logic* di Service.
- **`app/Http/Requests/*` (Form Requests):** Tameng validasi Form Data. Sebelum masuk controller, script ini mencegah *Request* asing (seperti email sudah dipakai, id tidak ada). Merupakan tempat menaruh `rules()` validasi data.
- **`app/Http/Resources/*` (API Resources):** *Transformer* pembungkus *Response*. Ia menyulap data array mentahan dari database menjadi struktur JSON yang bersih dan sesuai standar baku Frontend.
- **`app/Traits/HasAuditLog.php` (Traits):** Fitur tempelan *(plug-in)* yang bisa dipasang di Model manapun. Jika disertakan, sistem otomatis memantau siapa User yang menciptakan atau memodifikasi *record* data tersebut. 
- **`app/Http/Middleware/SecurityHeaders.php` (Middleware):** Satpam perlintasan HTTP. Mencegah ancaman Mime Sniffing, dan XSS dengan menyisipkan proteksi di tingkat *Header Response*.
- **`routes/api.php` dan `routes/web.php`:** Buku alamat proyek. `api.php` mendaftarkan *endpoint* REST yang akan disuguhkan ke Mobile App / Eksternal UI, lalu `web.php` menyajikan kerangka tampilan HTML untuk *Web UI Frontend*.

#### Direktori Resources (Frontend UI)
- **`resources/views/layouts/app.blade.php`:** Master HTML Template (Kerangka Utama Dasbor). Menyimpan struktur *SideBar*, *TopBar Navbar* yang bersifat *Component Cache*, dan import pustaka CSS/JS global agar tidak me-load berulang di tiap halaman (mirip konsep SPA).
- **`resources/views/*/*.blade.php`:** Halaman spesifik (Contoh: Daftar Klien, Manajemen Proyek). File ini memuat bentuk dan susunan tabel (UI Grid) serta skrip JS yang langsung menelepon URL `api/v1` untuk memampang datanya secara dinamis (AJAX/Fetch).
- **`public/js/api.js`:** Jantung interaksi Klien dengan Server. Pustaka pencegat (*interceptor*) yang bertugas mengambil token login tersimpan lalu disuntikkan secara otomatis ke setiap URL permintaan (*Request Headers*).

---

## 🔒 Konvensi Keamanan Sangat Penting (Security Guide)
Boily dirancang untuk produksi *Enterprise*, sehingga Anda wajib mematuhi aturan standar rilis (*Production*) demi keamanan mutlak:

1. **JANGAN PERNAH MENGUNGGAH FILE `.env` KE PUBLIK!** (Github / Gitlab dll). File `.env` bersifat tabu karena berisi kunci utama *Database*, Server Email, dan Kunci Enkripsi Aplikasi. Pantaulah melalui file `.gitignore` agar `.env` tidak ter-push secara tidak sengaja.
2. **Pakai Konfigurasi Produksi:** Saat dihosting (Vps / Cpanel / Cloud), pastikan parameter di `.env` sudah diatur menjadi `APP_ENV=production` dan `APP_DEBUG=false` agar sistem tidak membocorkan rentetan pesan error merah telanjang (*Stack Traces*) kepada *Hacker*.
3. **Regenerasi Kunci Keamanan:** Tiap kali kode dideploy ke *Server Baru*, jalankan perintah `php artisan key:generate` agar token kriptografi laravel dan *Session Hijacking protection* diriset ulang menjadi kunci yang baru khusus *server* itu.
4. **Proteksi Storage Publik:** Untuk mengamankan berkas rahasia pengguna, jangan membiarkan folder `storage/app` terekspos langsung melalui Symlink di server publik jika aset tersebut khusus Internal (seperti kwitansi/identitas Klien).

---

### Saran Pengembangan (Study Guide)
Bagi Anda yang sedang masuk ke jalur profesi Software Engineering:
1. Pahami bentuk kembalian struktur JSON dari `UserResource.php`. Ubah beberapa formasi kodenya untuk memahami *Response Flow*.
2. Cobalah untuk menambahkan 1 kolom basis data baru (contoh: "alamat" di tabel Client). Modifikasilah layer *Migration*, Model dasar, form validasi *Request*, sampai akhirnya Anda merubah isi dari *ClientResource*. 
3. Berinvestasilah pada pemahaman mengenai Dependency Injection (DI) yang terdapat di dalam file `RepositoryServiceProvider.php`.

Terima kasih. Proyek ini dikembangkan oleh **Nando Ega**.
