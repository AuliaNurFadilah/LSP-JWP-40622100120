# Aplikasi To-Do List

## Deskripsi

Aplikasi sederhana berbasis PHP untuk mencatat tugas harian. Data tugas disimpan menggunakan session PHP (`$_SESSION`), sehingga tidak memerlukan database. Tampilan dibangun menggunakan Bootstrap agar terlihat rapi dan responsif.

## Fitur

- Tambah tugas
- Tandai tugas selesai (checkbox, lalu teks tugas akan di coret jika sudah selesai)
- Edit tugas (inline, tanpa pindah halaman)
- Hapus tugas (dengan konfirmasi sebelum dihapus)

## Teknologi yang Digunakan

- PHP
- HTML5
- CSS
- JavaScript
- Bootstrap 5.3.3
- XAMPP

## Struktur Folder

todolist
├── index.php # halaman utama aplikasi (logika + tampilan)
└── README.md # dokumentasi proyek

## Struktur Kode

### Logika Backend (PHP)

| Fungsi         | Keterangan                                                            |
| -------------- | --------------------------------------------------------------------- |
| `getTasks()`   | Mengambil daftar tugas dari session                                   |
| `saveTasks()`  | Menyimpan daftar tugas ke session                                     |
| `addTask()`    | Menambahkan tugas baru (teks disanitasi dengan `strip_tags` & `trim`) |
| `toggleTask()` | Mengubah status selesai/belum selesai berdasarkan index               |
| `editTask()`   | Mengubah teks tugas berdasarkan index                                 |
| `deleteTask()` | Menghapus tugas berdasarkan index menggunakan `array_splice()`        |

Setiap tugas disimpan sebagai array asosiatif dengan dua key:

php
['text' => 'Isi tugas', 'done' => false]

Pola **Post/Redirect/Get** diterapkan setelah setiap aksi POST (`header('Location: ...')`) untuk mencegah pengiriman form berulang saat halaman di-refresh.

### Tampilan (HTML + Bootstrap)

- `<form>` untuk aksi tambah, edit, toggle, dan hapus tugas
- `<ul class="list-group">` untuk menampilkan daftar tugas hasil `foreach ($tasks as $index => $task)`
- Class Bootstrap seperti `btn`, `form-control`, `list-group`, `input-group` digunakan untuk styling
- Style tambahan (`task-done`, layout kartu) didefinisikan di dalam tag `<style>`

### Interaksi (JavaScript)

- Checkbox tugas otomatis mengirim form toggle saat status berubah (`change` event)
- Tombol **Edit** menampilkan form edit inline dan menyembunyikan teks tugas
- Tombol **Batal** mengembalikan tampilan ke mode teks biasa

## Cara Menjalankan

1. Pastikan XAMPP (atau server lokal PHP lainnya) sudah terinstal
2. Salin folder proyek ke dalam `htdocs/todolist`
3. Jalankan Apache melalui XAMPP Control Panel
4. Buka browser dan akses: `http://localhost/todolist/index.php/`

## Kontributor

- Aulia Nur Fadilah (https://github.com/AuliaNurFadilah/LSP-JWP-40622100120)
