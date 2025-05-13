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
            min-height: 100vh;
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--primary-color);
        }

        /* Dashboard Card Styles */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .dashboard-card {
            display: flex;
            align-items: center;
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .dashboard-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 10px;
            margin-right: 20px;
        }

        .dashboard-card-icon i {
            font-size: 2.5rem;
            color: var(--white);
        }

        .dashboard-card-content {
            flex-grow: 1;
        }

        .dashboard-card-title {
            font-size: 1rem;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .dashboard-card-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Table Styles */
        .recent-loans {
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-success {
            background-color: #28a745;
            color: #ffffff;
        }

        .badge-danger {
            background-color: #dc3545;
            color: #ffffff;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .dashboard {
                grid-template-columns: 1fr;
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
            <a href="../logout.php">
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

    <script>
        // Optional: Add any global JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Confirm logout
            const logoutLinks = document.querySelectorAll('a[href="../logout.php"]');
            logoutLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin logout?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
}
?>