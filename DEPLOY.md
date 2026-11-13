# Deploy POS ke VPS — elmannaf.com

> Stack: Laravel 12 + Inertia/Vue 3 + Vite + Tailwind + MySQL
> Domain: **elmannaf.com**
> VPS: Ubuntu 24.04, akses SSH (user + password), port 22
> DB: `elmannaf_pos` / user `elmannaf`
> Printer struk: via **QZ Tray di sisi kasir** (server tidak butuh akses printer)
> Catatan: TIDAK pakai queue worker / scheduler (kode tidak memakai queue)

---

## Kontrak eksekusi agen — baca sebelum menjalankan perintah

> **Agen sudah berada di server. Mulai dengan discovery read-only, bukan SSH ulang atau instalasi.**
> Semua blok perintah adalah referensi bersyarat, bukan skrip untuk dijalankan sekaligus.
> Kontrak ini mengalahkan contoh perintah di fase berikutnya.

Prompt tugas siap salin:

> Baca DEPLOY.md seluruhnya dan periksa kondisi server secara read-only. Tentukan instalasi
> pertama atau update existing, laporkan temuan tersanitasi, lalu ajukan satu rencana approval
> berkelompok dengan target revision, dampak/downtime, backup, migrasi, verifikasi dan rollback.
> Setelah approval eksplisit, **eksekusi** langkah yang disetujui, bukan hanya menjelaskannya.
> Berhenti pada kegagalan atau perubahan scope; jangan menganggap approval mencakup tindakan baru.
> Akhiri dengan ringkasan nonteknis dan handover tanpa rahasia.

- Jangan mengasumsikan root, deployment user, lokasi repo, remote URL, branch, kondisi server,
  atau working tree bersih. `/var/www/point-of-sale`, Ubuntu 24.04 dan PHP 8.3 di bawah adalah
  target/contoh yang harus dicocokkan dengan temuan aktual. Jangan mengganti stack existing otomatis.
- Jika ada perubahan server yang belum di-commit (termasuk untracked), **berhenti dan minta
  keputusan pemilik**. Jangan stash, overwrite, checkout, atau menghapusnya sendiri.
- Ajukan approval eksplisit di muka untuk perubahan paket (`apt update/upgrade`, instalasi paket/
  service), swap/fstab, database/user/grant, firewall/provider, DNS/Cloudflare, Certbot/renewal,
  migrasi, seed, konfigurasi/permission dan deployment. Nyatakan setiap tindakan secara terpisah
  dalam satu rencana agar operator bisa menyetujui sekaligus atau sebagian.
- Start/reload/restart service, maintenance `down/up`, cache dan perubahan lain yang memengaruhi
  aplikasi bersama perlu approval sesuai konteks/jendela downtime; jangan menyentuh service situs lain.
  Approval instalasi tidak otomatis mengizinkan upgrade seluruh OS atau reboot.
- **Dilarang** `migrate:fresh`, `migrate:reset`, `db:wipe`, penghapusan destruktif `rm`,
  `git reset --hard`, force pull/push/checkout, atau bypass `--force`. Pengecualian terbatas:
  `php artisan migrate --force` adalah flag Laravel untuk melewati konfirmasi production,
  **bukan approval**; hanya jalankan migrasi yang sudah ditinjau dan disetujui dengan backup.
  Seed admin production hanya untuk instalasi pertama dengan approval eksplisit dan rotasi password;
  jangan membaca/menampilkan password hardcoded. Jangan pernah otomatis seed demo production.
- Jangan mencetak `.env`, password, APP_KEY, private key, kredensial remote, dump DB, atau isi
  backup/log sensitif. Jangan gunakan `phpinfo`, `php -i`, `env`, `printenv`, debug dump atau
  shell tracing `set -x`. Inspeksi rahasia hanya melalui kanal aman; laporkan status boolean/
  metadata tersanitasi. Jangan taruh password di argumen, history, laporan atau URL.
- Pertahankan `.env`, APP_KEY existing, SSL/QZ keys, data/upload dan target symlink storage.
  APP_KEY hanya dibuat jika benar-benar belum ada pada instalasi pertama; jika hilang pada
  existing app, berhenti dan pulihkan dari backup, bukan generate ulang.
- Jangan jalankan `npm run dev`, `npm run dev:all`, `composer dev`, `artisan serve`, queue
  atau scheduler sebagai pengganti deployment. Jangan meninggalkan dev server yang sempat dimulai;
  hentikan hanya proses milik tugas ini, bukan proses asing/shared.

## FASE 0 — Discovery read-only dan gerbang approval

Jalankan hanya yang tersedia dan relevan; command tidak tersedia bukan alasan langsung install.
Output yang berpotensi sensitif (remote, log, konfigurasi) harus disaring **sebelum** masuk output
agen. Jangan `cat .env`, `git diff` isi rahasia, `nginx -T` tanpa penyaringan, atau dump log penuh.

```bash
pwd
id
# Setelah memastikan direktori repo yang benar:
git status --short --branch
git branch --show-current
git rev-parse HEAD
git log -5 --format='%h %cs'   # revision/tanggal, tanpa pesan potensial sensitif
php -v
php --ini
php -m
composer --version
node --version
npm --version
df -h .
free -h
swapon --show
systemctl is-active nginx mysql php8.3-fpm
systemctl is-enabled nginx mysql php8.3-fpm
ss -ltn
ls -l /run/php/
# Bila tersedia dan izin mencukupi:
sudo ufw status verbose
dig elmannaf.com +short
curl -I --max-time 20 http://elmannaf.com
curl -I --max-time 20 https://elmannaf.com
```

- [ ] Konfirmasi owner/deployment user, repo root, branch, revision dan remote dengan operator.
  Periksa remote secara lokal tanpa menampilkan userinfo/token; laporkan hanya host/path aman.
  Status git kosong selain branch; bila tidak, stop. Jangan menebak URL clone.
- [ ] Periksa service/port aktual (termasuk SSH dan DB), virtual host aktif, `server_name`,
  document root, socket FPM, TLS dan symlink enabled melalui inspeksi tersaring. Jangan mengubah
  default site atau service shared. Tinjau ringkasan error log Laravel/Nginx/FPM terbatas dan
  tersanitasi; jangan mengirim stack trace atau data pelanggan/kredensial ke output.
- [ ] Cocokkan DNS dengan IP server yang dikonfirmasi; cek HTTP/HTTPS tanpa `curl -k`.
  Hanya probe domain yang diberikan `elmannaf.com`; `www.elmannaf.com` di contoh konfigurasi
  memerlukan konfirmasi DNS/ownership dan approval sebelum dipakai. Jangan menebak host lain.
- [ ] Tentukan existing vs pertama dari aplikasi, `.env` (keberadaan saja), vendor, DB dan
  storage; jangan simpulkan instalasi baru hanya karena checkout kosong. Pastikan kapasitas disk,
  RAM/swap dan ruang backup/build cukup; laporkan angka serta batas aman yang disepakati.
- [ ] Jika dependency dan konfigurasi existing sudah dapat boot, jalankan
  `php artisan migrate:status` sebagai inspeksi DB read-only; jangan migrate agar status bisa dibaca.
  Jika belum bisa, catat blocker dan jadwalkan status sebelum migrasi setelah provisioning approved.
- [ ] Periksa `composer.json`, `composer.lock`, `package.json`, `package-lock.json` target release.
  PHP requirement langsung `^8.2` bukan jaminan semua lock dependency cocok. Cocokkan PHP CLI
  **dan FPM aktual**, extension serta lock production; Node 22 harus memenuhi engine lock/Vite
  (Vite 7 memerlukan Node 20.19+ atau 22.12+). Jangan `composer update`, ubah lockfile,
  `--ignore-platform-reqs`, atau bypass TypeScript untuk memaksa deploy.

**Lulus fase 0:** identitas, revision, state, kapasitas dan baseline jelas; tidak ada konflik
working tree; rencana/backup/rollback dan perubahan yang memerlukan approval disetujui.
Jika tidak lulus: stop, laporkan blocker tanpa mengubah server.

### Pilih jalur dan gerbang keberhasilan

| Tahap | Instalasi pertama | Existing app update | Kriteria lulus / fail-stop |
|---|---|---|---|
| Provision | FASE 1–4 hanya komponen yang belum ada dan approved | Lewati provisioning/clone/DB creation; gunakan repo terkonfirmasi | Stack kompatibel, DB lokal, target tidak menimpa aplikasi lain |
| Perlindungan | Simpan baseline konfigurasi/data yang sudah ada | Bagian update: maintenance approved, backup sebelum perubahan | Backup aman, lengkap, dapat dibaca dan restore plan tersedia; gagal backup = stop |
| Dependency/config/build | FASE 5–9; APP_KEY hanya bila missing initial | Lock target, `.env`/key/data tetap; tanpa seed | Platform check dan build/typecheck sukses, manifest/assets tersedia |
| Schema | Tinjau pending migrations; approval migrate dan admin seed terpisah | Hanya pending migrations approved, tanpa seed rutin | `migrate:status` semua expected Ran; bila gagal jangan lanjut live |
| Routing/TLS | FASE 10–12 hanya approved | Pertahankan konfigurasi existing kecuali perubahan approved | `nginx -t` lulus sebelum reload; service sehat; TLS/DNS benar |
| Live/handover | FASE 13 dan checklist akhir | Smoke test dan rollback di bawah | Semua check wajib lulus; bila gagal stop/maintenance approved dan recovery |

---

## Checklist deployment

- [ ] Stack, database lokal, dan SSH tunnel siap; tidak ada port MySQL publik.
- [ ] Kode dimiliki deployment user; web user hanya menulis storage/cache.
- [ ] `.env` production aman; APP_KEY hanya dibuat pada instalasi pertama.
- [ ] Migrasi, akun admin, build frontend, dan cache selesai.
- [ ] Nginx, HTTPS, renewal, firewall, dan backup terverifikasi.
- [ ] Smoke test admin/kasir, laporan/export, dan QZ Tray lulus.
- [ ] Password akun seed dirotasi sebelum akses publik/live.

Semua perintah di bawah adalah **contoh untuk dijalankan operator**, bukan perintah yang
sudah dijalankan. Ganti `user`, `IP_VPS`, URL repository, password placeholder, dan
`RELEASE_TAG` sesuai lingkungan. `user` adalah deployment user non-root dengan sudo;
jalankan git, Composer, npm, dan Artisan sebagai user tersebut, bukan `www-data`.

---

## FASE 1 — Persiapan VPS

```bash
ssh user@IP_VPS
sudo apt update
# Hanya bila upgrade OS disetujui secara terpisah:
sudo apt upgrade
sudo apt install -y curl unzip git ca-certificates lsb-release
```

Swap 2GB (hanya bila belum ada, kapasitas cukup, dan perubahan swap/fstab approved):

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
free -h   # verifikasi
```

## FASE 2 — Install stack

```bash
# Nginx + MySQL
sudo apt install -y nginx mysql-server

# PHP 8.3 + extension
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl \
  php8.3-sqlite3

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 22
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

Amankan MySQL:

```bash
sudo mysql_secure_installation
# - Set root password (kalau diminta)
# - Remove anonymous users: Y
# - Disallow root login remotely: Y
# - Remove test database: Y
# - Reload privilege tables: Y
```

> **Kenapa extension ini?**
> - `zip`, `gd` → dibutuhkan maatwebsite/excel & dompdf
> - `mbstring`, `xml`, `curl`, `bcmath`, `intl` → standar Laravel
> - `mysql` → koneksi database

## FASE 3 — Database

```bash
sudo mysql
```

Di prompt MySQL:

```sql
CREATE DATABASE elmannaf_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'elmannaf'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_DISINI';
GRANT ALL PRIVILEGES ON elmannaf_pos.* TO 'elmannaf'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Akses database dari laptop: SSH tunnel saja

Pertahankan `bind-address = 127.0.0.1` di
`/etc/mysql/mysql.conf.d/mysqld.cnf`. Jangan buka port 3306 di UFW maupun firewall
provider; jangan membuat user DB host publik atau wildcard. Gunakan akun
`'elmannaf'@'localhost'` yang dibuat di atas.

Jalankan di laptop dan biarkan sesi terbuka:

```bash
ssh -N -L 13306:127.0.0.1:3306 user@IP_VPS
```

DBeaver/TablePlus/Navicat: host `127.0.0.1` (localhost), port `13306`,
user `elmannaf`, password DB yang kuat. Host tujuan MySQL tetap localhost di VPS,
bukan IP publik VPS. Jika mengubah bind-address, restart MySQL setelah memeriksa konfigurasi.

## FASE 4 — Clone project

```bash
sudo install -d -o "$USER" -g www-data -m 2750 /var/www/point-of-sale
cd /var/www/point-of-sale
git clone https://github.com/USERNAME/REPO.git .
```

## FASE 5 — Dependency PHP

```bash
composer install --no-dev --optimize-autoloader
composer check-platform-reqs --no-dev
```

## FASE 6 — Konfigurasi `.env`

```bash
test -e .env || cp .env.example .env
nano .env
```

Contoh untuk instalasi baru; pada `.env` existing, ubah hanya nilai yang diperlukan dan pertahankan APP_KEY:

```env
APP_NAME=PointOfSale
APP_ENV=production
APP_KEY=                     # diisi otomatis di langkah generate
APP_DEBUG=false              # WAJIB false
APP_URL=https://elmannaf.com

APP_LOCALE=id
BCRYPT_ROUNDS=12

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elmannaf_pos
DB_USERNAME=elmannaf
DB_PASSWORD=PASSWORD_KUAT_DISINI

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync

CACHE_STORE=database
```

Hanya instalasi pertama, bila APP_KEY belum ada: jalankan perintah berikut.
**Jangan pernah mengganti APP_KEY saat update**, dan jangan menimpa `.env` yang sudah ada.
Simpan APP_KEY lama pada pemulihan agar data terenkripsi tetap dapat dibaca.

```bash
php artisan key:generate
```

## FASE 7 — Migrate + seed + storage (instalasi pertama)

```bash
php artisan migrate --force
# Hanya approval seed admin instalasi pertama, bukan default deployment:
php artisan db:seed --class=DatabaseSeeder
# Inspeksi public/storage dahulu; pertahankan link/target existing, jangan overwrite.
# Jalankan hanya jika link belum ada dan target sudah diverifikasi:
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> `migrate --force` untuk production hanya setelah approval migrasi; bukan izin otomatis.
> Seed production memerlukan konfirmasi interaktif operator setelah approval terpisah, bukan bypass force.
> `DatabaseSeeder` saat ini membuat `elmannafadmin@yopmail.com`, nama `superadmin`,
> dengan role **`admin`** (bukan role superadmin). Jika email sudah ada, seeder tidak mengubah akun.
> Seeder masih menyimpan password hardcoded; nilainya sengaja tidak dicantumkan di sini.
> **Rotasi password segera setelah provisioning, sebelum layanan dibuka ke publik/live.**
> Rekomendasi tugas kode terpisah di masa depan: hapus kredensial hardcoded dan gunakan
> provisioning aman. Panduan ini tidak mengubah kode.

### Data demo: hanya local/testing terpisah

`DemoDataSeeder` berdiri sendiri, tidak dipanggil oleh `DatabaseSeeder`, dan menolak
selain environment `local`/`testing`. **Dilarang menjalankannya pada production.**
Jangan mengubah APP_ENV server live menjadi local untuk melewati pembatasan.
Hanya pada instalasi local/testing dengan database terisolasi yang sudah dimigrasi:

```bash
php artisan db:seed --class=DemoDataSeeder
```

## FASE 8 — Build frontend

```bash
npm ci --include=dev
npm run build
```

`package.json` menjalankan `vue-tsc && vite build`. Vite, TypeScript, dan tool build
ada dalam devDependencies: `npm ci --include=dev` diperlukan meskipun target production.
Jika pemeriksaan TypeScript gagal, perbaiki dan ulangi `npm run build`; jangan melewati
pemeriksaan tersebut. Jangan lanjut live dengan build gagal.

## FASE 9 — Permission

```bash
# Hanya setelah $USER dipastikan deployment user dan path/group benar:
sudo chown -R "$USER":www-data /var/www/point-of-sale
sudo chmod -R u=rwX,g=rX,o= /var/www/point-of-sale
sudo find /var/www/point-of-sale -type d -exec chmod g+s {} \;
sudo find storage bootstrap/cache -type d -exec chmod 2770 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 660 {} \;
sudo chmod 640 .env
```

Kode dimiliki deployment user, hanya dapat dibaca web user `www-data`; direktori
`storage` dan `bootstrap/cache` dapat ditulis keduanya. Ulangi permission setelah
build/update agar file baru juga terbaca web user. Jangan menjalankan git/npm sebagai
web user atau menyerahkan ownership seluruh repository kepadanya.

Untuk QZ, setelah operator memasang key yang benar melalui kanal aman, batasi lagi
`storage/app/keys` ke mode `2750` dan `private-key.pem` ke `640`, owner deployment
user, group `www-data`. Jangan membuat direktori key dapat ditulis web user.

## FASE 10 — Nginx

```bash
sudo nano /etc/nginx/sites-available/elmannaf
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name elmannaf.com www.elmannaf.com;

    root /var/www/point-of-sale/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/elmannaf /etc/nginx/sites-enabled/
# Jangan hapus default site otomatis; periksa apakah dipakai aplikasi lain.
# Penonaktifan site hanya sesuai perubahan yang telah disetujui.
sudo nginx -t
sudo systemctl reload nginx
```

## FASE 11 — Domain & SSL

Pastikan DNS A record `elmannaf.com` & `www` → IP VPS:

```bash
dig elmannaf.com +short
```

Kalau di **Cloudflare**, matikan proxy (grey cloud) dulu biar Certbot bisa HTTP-01.

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d elmannaf.com -d www.elmannaf.com
sudo certbot renew --dry-run
```

Update `.env`:

```env
APP_URL=https://elmannaf.com
SESSION_SECURE_COOKIE=true
```

```bash
php artisan config:cache
```

## FASE 12 — Firewall UFW: URUTAN PENTING

> **JANGAN `ufw enable` sebelum allow OpenSSH** — koneksi SSH bisa langsung terputus.

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow OpenSSH          # WAJIB sebelum enable
sudo ufw allow 'Nginx Full'     # 80 & 443

# Tidak ada rule allow port 3306; akses DB hanya melalui SSH tunnel.

sudo ufw enable                 # jawab 'y'
sudo ufw status verbose
```

Jika sebelumnya ada rule MySQL publik, hapus rule tersebut melalui operator (termasuk
firewall provider) dan verifikasi ulang. Hasil yang benar:

```
To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
Nginx Full                 ALLOW       Anywhere
```

## FASE 13 — Verifikasi

```bash
# Dari server, dan ulangi dari laptop bila akses tersedia:
curl -I --max-time 20 http://elmannaf.com   # redirect ke HTTPS
curl -I https://elmannaf.com              # harus 200/302
curl -I https://elmannaf.com/.env         # harus 403/404
```

---

## Update deployment dan backup

Jalankan sebagai deployment user dari `/var/www/point-of-sale`. Catat commit/release
sebelumnya dan target `RELEASE_TAG` yang sudah diuji; contoh ini memakai checkout
in-place, bukan skema atomic release. Jangan menimpa `.env`, mengganti APP_KEY,
atau menjalankan seed ulang pada update rutin. Pastikan working tree bersih sebelum checkout.

Sebelum mengubah kode/schema, masuk maintenance dan ambil backup. Lokasi backup
harus di luar `public`, dengan akses hanya operator. Contoh berikut meminta password
MySQL secara interaktif (`-p`), tidak menyimpannya di command/history:

```bash
cd /var/www/point-of-sale
php artisan down
umask 077
BACKUP_DIR="$HOME/pos-backups/$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"
chmod 700 "$HOME/pos-backups" "$BACKUP_DIR"
git rev-parse HEAD > "$BACKUP_DIR/previous-commit.txt"
mysqldump --single-transaction --no-tablespaces -h 127.0.0.1 -u elmannaf -p elmannaf_pos > "$BACKUP_DIR/database.sql"
tar -czf "$BACKUP_DIR/app-secrets-storage.tar.gz" .env storage
chmod 600 "$BACKUP_DIR"/*
```

Backup `storage` mencakup upload dan `storage/app/keys/private-key.pem`; pastikan
key dan `.env` benar-benar tersertakan. Simpan salinan terenkripsi off-site dengan
akses terbatas dan uji restore di lingkungan terisolasi. `--single-transaction`
memberi snapshot konsisten untuk tabel transaksional (InnoDB); hentikan penulis lain
juga dan hindari perubahan DDL saat dump. **Jika backup gagal, berhenti, jangan update.**

Setelah backup diverifikasi dan rencana migrasi/recovery disetujui:

```bash
git fetch --tags
git checkout RELEASE_TAG
composer install --no-dev --optimize-autoloader
composer check-platform-reqs --no-dev
npm ci --include=dev
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ulangi permission FASE 9 (termasuk pembatasan key), lalu:

```bash
sudo nginx -t
# Gunakan nama FPM aktual yang sudah diverifikasi; graceful reload, bukan restart:
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
php artisan up
```

Lakukan smoke test segera; bila gagal, pertahankan/kembalikan maintenance mode.
Kembalikan kode dan asset dari release sebelumnya yang tercatat, install dependency
sesuai lockfile release itu, pulihkan permission dan cache. **Rollback kode hanya aman
jika schema DB masih kompatibel.** Jangan menjalankan migrate rollback secara membabi
buta. Bila schema tidak kompatibel, gunakan rencana pemulihan yang sudah diuji:
restore backup DB, storage, `.env`/APP_KEY dan key yang cocok dengan release lama,
dengan persetujuan operator karena transaksi setelah backup dapat hilang. Verifikasi
integritas dan smoke test sebelum `php artisan up`.

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| 502 Bad Gateway | Cek `sudo systemctl status php8.3-fpm`; samakan socket path di Nginx |
| 500 error APP_DEBUG=false | Tinjau error terbatas dari `storage/logs/laravel.log` dengan redaksi rahasia sebelum output |
| Gambar tidak muncul | Cek `storage:link` & permission storage |
| Permission denied | Ulangi FASE 9 |
| Perubahan .env gak kebaca | `php artisan config:clear && php artisan config:cache` |
| `npm run build` gagal di vue-tsc | Pastikan `npm ci --include=dev`, perbaiki error TypeScript, lalu build ulang tanpa bypass |
| Tidak bisa connect MySQL dari laptop | Cek SSH tunnel aktif, port laptop 13306 tidak bentrok, tujuan 127.0.0.1:3306, bind-address 127.0.0.1 dan akun DB localhost; jangan membuka port DB publik |
| Terkunci dari SSH | Buka console VPS via panel provider, `sudo ufw allow OpenSSH` |

---

## Checklist akhir sebelum live

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Password DB bukan default
- [ ] Password admin seed sudah dirotasi sebelum akses publik/live
- [ ] `public/build/` ada hasil build
- [ ] `storage:link` sudah
- [ ] Nginx root = `/public`
- [ ] `https://elmannaf.com/.env` → 403/404
- [ ] HTTPS aktif & redirect dari HTTP
- [ ] UFW aktif, SSH masih bisa diakses
- [ ] MySQL bind 127.0.0.1; tidak ada allow 3306 di UFW/firewall provider; tunnel laptop diuji
- [ ] Backup DB/storage/.env/key aman, permission terbatas, restore teruji
- [ ] SSL renewal dry-run sukses
- [ ] QZ Tray terinstall & dikonfigurasi di tiap PC kasir
- [ ] Admin: tombol full-width **Buka ringkasan owner** membuka modal, data/layout dan tutup modal benar
- [ ] Kasir: tombol/ringkasan owner tersembunyi; akses data owner tidak diberikan
- [ ] Produk, transaksi, stok, dan cetak struk QZ diuji
- [ ] Laporan dan export Excel/PDF: rentang tanggal (termasuk batas tanggal) dan filter kasir menghasilkan data/total yang sama

---

## Catatan khusus printer (QZ Tray)

- Cetak struk terjadi di **komputer kasir**, bukan server.
- QZ Tray harus jalan di tiap PC kasir sebagai service.
- `QzSignController` membaca private key dari **`storage/app/keys/private-key.pem`**
  (nonpublik) dan menandatangani dengan SHA512. Operator harus memasang key yang
  cocok dengan sertifikat publik yang saat ini tertanam di `resources/views/app.blade.php`.
  Jangan menaruh key di `public`, `storage/app/public`, repository, log, atau dokumen ini.
- Permission key: deployment user sebagai owner, group `www-data`, direktori `2750`,
  file `640` agar PHP-FPM dapat membaca tetapi tidak menulisnya. Panduan ini tidak
  menghasilkan maupun membaca key aktual. Jika key hilang/tidak cocok, hentikan
  handover cetak dan koordinasikan provisioning pasangan sertifikat/key yang sah.
- Uji signing, trust sertifikat, koneksi QZ Tray, dan cetak pada tiap PC kasir;
  sertifikat terpasang saja tidak menjamin dialog izin hilang tanpa trust yang sesuai.
  Referensi: https://qz.io/wiki/2.0-signing-messages


## Verifikasi key QZ tanpa membuka rahasia

Periksa keberadaan/readability dengan `test -f storage/app/keys/private-key.pem` dan
metadata `stat -c '%a %U %G' storage/app/keys/private-key.pem`; cocokkan owner/group dan
mode dengan FASE 9. Uji readability oleh user FPM yang sudah dikonfirmasi tanpa mencetak isi.
Validasi key memakai `openssl pkey -in storage/app/keys/private-key.pem -check -noout`;
jangan gunakan `-text` atau mencetak PEM. Kegagalan parsing/readability = blocker printing.
Untuk mencocokkan pasangan, ekstrak **hanya sertifikat publik** tertanam dari
`resources/views/app.blade.php` ke file sementara privat operator, lalu bandingkan digest
SHA-256 DER public key: `openssl pkey -in storage/app/keys/private-key.pem -pubout -outform DER`
dan `openssl x509 -in CERT_PUBLIK.pem -pubkey -noout | openssl pkey -pubin -outform DER`.
Hash masing-masing output secara internal; semua exit status pipeline harus diperiksa
(`pipefail` pada Bash), laporkan hanya MATCH/FAIL, bukan material key atau password.
Jangan menganggap sertifikat TLS Nginx adalah sertifikat QZ. Jangan generate/replace pasangan
existing untuk mengatasi mismatch tanpa provisioning approved.

## Handover agen (tanpa rahasia)

- **Ringkasan nonteknis:** berhasil / tertunda / gagal; apakah POS dapat digunakan dan dampaknya.
- **Jalur & approval:** first install/update, scope approved, tindakan yang dilewati.
- **Release:** lokasi repo, revision sebelum → sesudah, branch/tag, status working tree.
- **Check berhasil:** platform CLI/FPM, dependency, typecheck/build, status migrasi,
  permission/storage, `nginx -t`, service, HTTP→HTTPS, TLS, `.env` 403/404, smoke test dan QZ.
- **Check pending/blocker:** sebab, pemilik tindak lanjut, apakah maintenance masih aktif;
  jangan menyatakan sukses bila tes kasir/printer/restore belum dilakukan.
- **Backup/recovery:** lokasi privat, waktu/revision, komponen dan hasil validasi/restore test,
  kompatibilitas schema, langkah rollback approved; jangan tampilkan isi backup atau kredensial.
- **Keamanan:** APP_KEY preserved/initial only, rotasi admin terkonfirmasi bila seed approved,
  tidak ada dev server tugas yang tertinggal; daftar perubahan service/config tanpa rahasia.
