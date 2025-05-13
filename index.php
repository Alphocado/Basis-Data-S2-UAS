<?php
// index.php - Halaman Beranda
session_start();
require_once 'config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Sistem Informasi Perpustakaan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">
  
</head>
<body>
  <nav class="navbar">
    <div class="container">
      <div class="navbar-brand">
        <i class="fas fa-book-reader"></i>
        Perpustakaan Sekolah
      </div>
      <ul class="navbar-menu">
        <li><a href="index.php">Beranda</a></li>
        <?php if (!isset($_SESSION['login'])): ?>
          <li><a href="login.php">Login</a></li>
        <?php else: ?>
          <?php if ($_SESSION['level'] == 'admin'): ?>
            <li><a href="admin/index.php">Dashboard Admin</a></li>
          <?php else: ?>
            <li><a href="anggota/profil.php">Profil Saya</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <div class="container">
    <div class="hero">
      <h1>Selamat Datang di Sistem Informasi Perpustakaan</h1>
      <p>Solusi modern untuk manajemen perpustakaan yang efisien dan mudah digunakan. Akses katalog, kelola peminjaman, dan temukan pengetahuan baru dengan mudah.</p>
    </div>
    
    <?php if (!isset($_SESSION['login'])): ?>
      <div class="welcome-section">
        <p>Silakan login untuk mengakses fitur perpustakaan.</p>
        <a href="login.php" class="btn">Login Sekarang</a>
      </div>
    <?php else: ?>
      <div class="welcome-section">
        <p>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <?php if ($_SESSION['level'] == 'admin'): ?>
          <a href="admin/index.php" class="btn">Ke Dashboard Admin</a>
        <?php else: ?>
          <a href="anggota/index.php" class="btn">Lihat Profil</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="info-section">
      <h2>Fitur Utama Perpustakaan</h2>
      <div class="features">
        <div class="feature">
          <i class="fas fa-users fa-3x" style="color: var(--primary-color); margin-bottom: 15px;"></i>
          <h3>Manajemen Anggota</h3>
          <p>Kelola data anggota perpustakaan dengan mudah dan efisien.</p>
        </div>
        <div class="feature">
          <i class="fas fa-book-open fa-3x" style="color: var(--primary-color); margin-bottom: 15px;"></i>
          <h3>Katalog Buku</h3>
          <p>Telusuri dan kelola koleksi buku secara komprehensif.</p>
        </div>
        <div class="feature">
          <i class="fas fa-exchange-alt fa-3x" style="color: var(--primary-color); margin-bottom: 15px;"></i>
          <h3>Transaksi Peminjaman</h3>
          <p>Proses peminjaman dan pengembalian buku dengan cepat dan akurat.</p>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="container">
      <p>&copy; <?php echo date('Y'); ?> Sistem Informasi Perpustakaan</p>
    </div>
  </footer>
</body>
</html>