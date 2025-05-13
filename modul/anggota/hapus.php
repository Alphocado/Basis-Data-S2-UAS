<?php
// modul/anggota/hapus.php
session_start();
require_once '../../config/koneksi.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Cek apakah ada ID anggota yang akan dihapus
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID anggota tidak valid";
    redirect('daftar.php');
}

$id_anggota = mysqli_real_escape_string($koneksi, $_GET['id']);

// Cek apakah anggota memiliki peminjaman aktif
$cek_peminjaman = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'Dipinjam'");
if (mysqli_num_rows($cek_peminjaman) > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus anggota yang masih memiliki buku pinjaman aktif";
    redirect('daftar.php');
}

// Proses hapus anggota
$query_hapus = "DELETE FROM anggota WHERE id_anggota = '$id_anggota'";
if (mysqli_query($koneksi, $query_hapus)) {
    // Hapus data terkait (misal riwayat peminjaman)
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_anggota = '$id_anggota'");
    mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_anggota = '$id_anggota'");

    $_SESSION['success'] = "Anggota berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus anggota: " . mysqli_error($koneksi);
}

// Redirect kembali ke halaman daftar
redirect('daftar.php');

// Fungsi redirect (jika belum ada di file konfigurasi)
function redirect($url) {
    header("Location: $url");
    exit();
}
?>