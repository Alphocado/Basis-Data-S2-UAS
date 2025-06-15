<?php
// includes/layout.php
function renderHeader($title = "Sistem Perpustakaan", $activeMenu = "") {
  // Check if user is logged in
  if (!isset($_SESSION['login'])) {
  header("Location: ../login.php");
  exit();
  }

  $userLevel = $_SESSION['level'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="../../assets/css/layout.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  

</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
  <div class="sidebar-header">
    <i class="fas fa-book-reader fa-2x"></i>
    <h1>Perpustakaan</h1>
  </div>
  <nav class="sidebar-menu">
    <?php if ($userLevel == 'admin'): ?>
    <a href="../../admin/dashboard.php" class="<?php echo ($activeMenu == 'dashboard') ? 'active' : ''; ?>">
      <i class="fas fa-home"></i>Dashboard
    </a>
    <a href="../../modul/anggota/daftar.php" class="<?php echo ($activeMenu == 'anggota') ? 'active' : ''; ?>">
      <i class="fas fa-users"></i>Anggota
    </a>
    <a href="../../modul/buku/daftar_buku.php" class="<?php echo ($activeMenu == 'buku') ? 'active' : ''; ?>">
      <i class="fas fa-book"></i>Buku
    </a>
    <a href="../../modul/peminjaman/laporan_peminjaman.php" class="<?php echo ($activeMenu == 'peminjaman') ? 'active' : ''; ?>">
      <i class="fas fa-exchange-alt"></i>Peminjaman
    </a>
    <a href="../../modul/denda/denda.php" class="<?php echo ($activeMenu == 'denda') ? 'active' : ''; ?>">
      <i class="fas fa-money-bill"></i>Denda
    </a>
    <?php else: ?>
    <a href="dashboard.php" class="<?php echo ($activeMenu == 'dashboard') ? 'active' : ''; ?>">
      <i class="fas fa-home"></i>Dashboard
    </a>
    <a href="profil.php" class="<?php echo ($activeMenu == 'profil') ? 'active' : ''; ?>">
      <i class="fas fa-user"></i>Profil
    </a>
    <a href="peminjaman.php" class="<?php echo ($activeMenu == 'peminjaman') ? 'active' : ''; ?>">
      <i class="fas fa-book"></i>Daftar Pinjaman
    </a>
    
    <?php endif; ?>
    <a href="../../logout.php">
    <i class="fas fa-sign-out-alt"></i>Logout
    </a>
  </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
<?php
}

function renderFooter() {
?>
  </main>

  <script src="../assets/js/includes/layout.js"></script>
</body>
</html>
<?php
}
?>