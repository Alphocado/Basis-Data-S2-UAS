<?php
  include '../../config/koneksi.php';
  session_start();

  // Ambil data pengembalian
  $id_pinjam = $_GET['id']; // id peminjaman yang dikembalikan
  $tanggal_kembali = date('Y-m-d');

  // Cek data peminjaman
  $query = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_pinjam = '$id_pinjam'");
  $data = mysqli_fetch_assoc($query);

  // Simpan pengembalian
  mysqli_query($koneksi, "INSERT INTO pengembalian (id_pinjam, tanggal_kembali) VALUES ('$id_pinjam', '$tanggal_kembali')");

  // Cek apakah terlambat
  $batas_pengembalian = $data['tanggal_kembali'];
  $selisih_hari = (strtotime($tanggal_kembali) - strtotime($batas_pengembalian)) / (60 * 60 * 24);

  if ($selisih_hari > 0) {
      $biaya_per_hari = 1000; // Bisa kamu sesuaikan
      $total_denda = $selisih_hari * $biaya_per_hari;

      mysqli_query($koneksi, "INSERT INTO denda (id_pinjam, jumlah_denda, status) VALUES ('$id_pinjam', '$total_denda', 'Belum Lunas')");
  }

  // Redirect kembali
  header('Location: ../../dashboard?page=pengembalian');
  exit;
?>
