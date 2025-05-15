<?php
// modul/buku/hapus.php
session_start();
require_once '../../config/koneksi.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
  redirect('../../login.php');
}

// Cek apakah ada ID buku yang akan dihapus
if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['error'] = "ID buku tidak valid";
  redirect('daftar_buku.php');
}

$id_buku = mysqli_real_escape_string($koneksi, $_GET['id']);

// Cek apakah buku sedang dipinjam
$cek_peminjaman = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_buku = '$id_buku' AND status = 'Dipinjam'");
if (mysqli_num_rows($cek_peminjaman) > 0) {
  $_SESSION['error'] = "Tidak dapat menghapus buku yang sedang dipinjam";
  redirect('daftar_buku.php');
}

// Proses hapus buku
// $query_hapus_peminjaman = "DELETE FROM peminjaman WHERE id_buku = '$id_buku'";
// $query_hapus_pengembalian = "DELETE FROM pengembalian WHERE id_buku = '$id_buku'";

mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_peminjaman IN (
  SELECT id_peminjaman FROM peminjaman WHERE id_buku = '$id_buku'
)");

mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_buku = '$id_buku'");

$query_hapus = "DELETE FROM buku WHERE id_buku = '$id_buku'";
if (mysqli_query($koneksi, $query_hapus)) {
  $_SESSION['success'] = "Buku berhasil dihapus";
} else {
  $_SESSION['error'] = "Gagal menghapus buku: " . mysqli_error($koneksi);
}

// Redirect kembali ke halaman daftar
redirect('daftar_buku.php');

// Fungsi redirect (jika belum ada di file konfigurasi)
if (!function_exists('redirect')) {
  function redirect($url) {
    header("Location: $url");
    exit();
  }
}
?>