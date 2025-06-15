<?php
  // modul/peminjaman/proses.php
  session_start();
  require_once '../../config/koneksi.php';

  // Cek apakah sudah login sebagai admin
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
  }

  // Proses form peminjaman buku
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data form
    $id_anggota = mysqli_real_escape_string($koneksi, $_POST['id_anggota']);
    $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $id_admin = $_SESSION['id_admin']; // ID admin yang sedang login
    $tanggal_pinjam = date('Y-m-d'); // Tanggal hari ini
    
    // Hitung tanggal kembali (default 7 hari)
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days'));
    
    // Cek ketersediaan buku
    $query_cek = "SELECT * FROM peminjaman WHERE id_buku = '$id_buku' AND status = 'Dipinjam'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
      // Buku sedang dipinjam
      $_SESSION['error'] = "Buku ini sedang dipinjam dan tidak tersedia.";
      redirect('add.php');
    }
    
    // Cek jumlah buku yang sedang dipinjam oleh anggota
    $query_limit = "SELECT COUNT(*) as jumlah FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'Dipinjam'";
    $result_limit = mysqli_query($koneksi, $query_limit);
    $data_limit = mysqli_fetch_assoc($result_limit);
    
    if ($data_limit['jumlah'] >= 3) {
      // Anggota sudah meminjam maksimal 3 buku
      $_SESSION['error'] = "Anggota ini sudah meminjam 3 buku (batas maksimum).";
      redirect('add.php');
    }
    
    // Insert data peminjaman
    $query = "INSERT INTO peminjaman (id_anggota, id_admin, id_buku, tanggal_pinjam, tanggal_kembali, status) 
          VALUES ('$id_anggota', '$id_admin', '$id_buku', '$tanggal_pinjam', '$tanggal_kembali', 'Dipinjam')";
    
    if (mysqli_query($koneksi, $query)) {
      $_SESSION['success'] = "Peminjaman buku berhasil dicatat.";
      redirect('index.php');
    } else {
      $_SESSION['error'] = "Gagal mencatat peminjaman buku: " . mysqli_error($koneksi);
      redirect('add.php');
    }
  } 
  // Proses pengembalian buku
  else if (isset($_GET['action']) && $_GET['action'] == 'kembali' && isset($_GET['id'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['id']);
    $tanggal_dikembalikan = date('Y-m-d'); // Tanggal hari ini
    
    // Ambil data peminjaman
    $query_pinjam = "SELECT * FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'";
    $result_pinjam = mysqli_query($koneksi, $query_pinjam);
    $data_pinjam = mysqli_fetch_assoc($result_pinjam);
    
    // Hitung denda jika terlambat
    $denda = 0;
    $tanggal_kembali = new DateTime($data_pinjam['tanggal_kembali']);
    $tanggal_sekarang = new DateTime($tanggal_dikembalikan);
    $selisih = $tanggal_sekarang->diff($tanggal_kembali);
    
    if ($tanggal_sekarang > $tanggal_kembali) {
      $hari_terlambat = $selisih->days;
      $denda = $hari_terlambat * 1000; // Denda Rp 1.000 per hari
    }
    
    // Update status peminjaman
    $query_update = "UPDATE peminjaman SET status = 'Dikembalikan' WHERE id_peminjaman = '$id_peminjaman'";
    mysqli_query($koneksi, $query_update);
    
    // Insert data pengembalian
    $query_pengembalian = "INSERT INTO pengembalian (id_peminjaman, tanggal_dikembalikan, denda) 
                VALUES ('$id_peminjaman', '$tanggal_dikembalikan', '$denda')";
    
    // Jika ada denda, catat di tabel denda
    if ($denda > 0) {
      $status_pembayaran = 'Belum';
      $query_denda = "INSERT INTO denda (id_pengembalian, jumlah_denda, nominal_denda, status_pembayaran) 
              VALUES (LAST_INSERT_ID(), $hari_terlambat, '$denda', '$status_pembayaran')";
    }
    
    if (mysqli_query($koneksi, $query_pengembalian)) {
      if ($denda > 0) {
        mysqli_query($koneksi, $query_denda);
        $_SESSION['success'] = "Buku berhasil dikembalikan dengan denda Rp " . number_format($denda, 0, ',', '.');
      } else {
        $_SESSION['success'] = "Buku berhasil dikembalikan tanpa denda.";
      }
      redirect('index.php');
    } else {
      $_SESSION['error'] = "Gagal mencatat pengembalian buku: " . mysqli_error($koneksi);
      redirect('index.php');
    }
  } else {
    // Jika bukan POST atau action kembali, redirect ke halaman daftar
    redirect('laporan.php');
  }
?>