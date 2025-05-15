<?php
  // modul/anggota/hapus.php
  session_start();
  require_once '../../config/koneksi.php';

  // Fungsi redirect (hindari duplikasi)
  if (!function_exists('redirect')) {
    function redirect($url) {
      header("Location: $url");
      exit;
    }
  }

  // Cek apakah sudah login sebagai admin
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
  }

  // Cek apakah ada ID anggota yang akan dihapus
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID anggota tidak valid";
    redirect('daftar.php');
  }

  $id_anggota = intval($_GET['id']); // gunakan intval agar aman

  // Cek apakah anggota memiliki peminjaman aktif
  $cek_peminjaman = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_anggota = $id_anggota AND status = 'Dipinjam'");
  if (mysqli_num_rows($cek_peminjaman) > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus anggota yang masih memiliki buku pinjaman aktif";
    redirect('daftar.php');
  }

  // Hapus data pengembalian terlebih dahulu via id_peminjaman (subquery)
  mysqli_query($koneksi, "
    DELETE FROM pengembalian 
    WHERE id_peminjaman IN (
      SELECT id_peminjaman FROM peminjaman WHERE id_anggota = $id_anggota
    )
  ");

  // Hapus riwayat peminjaman
  mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_anggota = $id_anggota");

  // Hapus dari tabel anggota
  if (mysqli_query($koneksi, "DELETE FROM anggota WHERE id_anggota = $id_anggota")) {
    $_SESSION['success'] = "Anggota berhasil dihapus";
  } else {
    $_SESSION['error'] = "Gagal menghapus anggota: " . mysqli_error($koneksi);
  }

  // Redirect kembali ke halaman daftar
  redirect('daftar.php');
?>
