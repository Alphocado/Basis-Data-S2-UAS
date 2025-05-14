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
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f4f6f7;
            --sidebar-color: #2c3e50;
            --text-color: #333;
            --white: #ffffff;
            --hover-color: #2980b9;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: width 0.3s ease;
            overflow-x: hidden;
            z-index: 1000;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: var(--primary-color);
            color: var(--white);
        }

        .sidebar-header h1 {
            font-size: 1.5rem;
            margin-left: 10px;
        }

        .sidebar-menu {
            padding-top: 20px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            transition: background-color 0.3s ease;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 25px;
            text-align: center;
        }

        .sidebar-menu a:hover {
            background-color: var(--hover-color);
        }

        .sidebar-menu a.active {
            background-color: var(--primary-color);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h1 {
            color: var(--primary-color);
        }

        /* Card Styles */
        .card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-color);
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: var(--primary-color);
            color: var(--white);
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr:hover {
            background-color: #f1f3f5;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: var(--hover-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: var(--white);
        }

        .bg-success {
            background-color: var(--secondary-color);
        }

        .bg-warning {
            background-color: var(--warning-color);
        }

        .bg-danger {
            background-color: var(--danger-color);
        }

        /* Grid Layout */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-md-12 {
            padding: 0 10px;
            margin-bottom: 20px;
        }

        .col-md-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-md-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Pagination */
        .pagination {
            display: flex;
            list-style-type: none;
        }

        .pagination a {
            color: var(--text-color);
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #ddd;
            margin: 0 4px;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: var(--white);
            border: 1px solid var(--primary-color);
        }

        /* Text Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-danger {
            color: var(--danger-color);
        }

        .text-success {
            color: var(--secondary-color);
        }

        .text-warning {
            color: var(--warning-color);
        }

        .text-muted {
            color: #6c757d;
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }

            .sidebar.active {
                width: 250px;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .col-md-3, .col-md-4, .col-md-6, .col-md-8 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header div {
                margin-top: 10px;
            }
        }
    </style>
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

    <script>
    // Tambahkan script JavaScript untuk mobile toggle sidebar jika diperlukan
    document.addEventListener('DOMContentLoaded', function() {
        // Script untuk mobile toggle atau lainnya bisa ditambahkan di sini
    });
    </script>
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