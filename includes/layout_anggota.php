<?php
// includes/layout.php
function renderHeader($title = "Sistem Perpustakaan", $activeMenu = "") {
  $active = $activeMenu;

  // Check if user is logged in
  if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit();
  }

  $userLevel = $_SESSION['level'];
  $isAnggota = ($userLevel === 'anggota');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> - Perpustakaan</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/includes/layout_anggota.css">
  
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-book-reader fa-2x"></i>
      <h1>Perpustakaan</h1>
    </div>
    <nav class="sidebar-menu">
      <?php if ($isAnggota): ?>
        <a href="../anggota/dashboard.php" class="<?php echo $active == 'dashboard' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>Dashboard
      </a>
      <a href="../anggota/profil.php" class="<?php echo $active == 'profil' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>Profil
      </a>
      <a href="../anggota/peminjaman.php" class="<?php echo $active == 'peminjaman' ? 'active' : ''; ?>">
        <i class="fas fa-book"></i>Daftar Pinjaman
      </a>
      <?php endif; ?>
      <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i>Logout
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
<?php
}

/**
 * Render footer aplikasi
 * 
 * @return void
 */
function renderFooter() {
?>
  </main>

  <script src="../assets/js/includes/layout_anggota.js"></script>
</body>
</html>
<?php
}

/**
 * Helper function untuk menjalankan query mysqli
 * 
 * @param mysqli $koneksi Koneksi database
 * @param string $query Query SQL
 * @return mysqli_result|bool Hasil query
 */

if (!function_exists('runQuery')) {
  function runQuery($koneksi, $query) {
  $result = mysqli_query($koneksi, $query);
  if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
  }
  return $result;
  }
}
?>