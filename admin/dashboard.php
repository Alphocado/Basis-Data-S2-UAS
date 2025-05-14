<?php
// admin/index.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../login.php');
}

// Ambil statistik dengan nama tabel yang benar
$query_anggota = "SELECT COUNT(*) as total_anggota FROM anggota";
$query_buku = "SELECT COUNT(*) as total_buku FROM buku";
$query_peminjaman = "SELECT COUNT(*) as total_peminjaman FROM peminjaman WHERE status = 'Dipinjam'";
$query_pengembalian = "SELECT COUNT(*) as total_pengembalian FROM pengembalian";

$result_anggota = runQuery($koneksi, $query_anggota);
$result_buku = runQuery($koneksi, $query_buku);
$result_peminjaman = runQuery($koneksi, $query_peminjaman);
$result_pengembalian = runQuery($koneksi, $query_pengembalian);

$total_anggota = mysqli_fetch_assoc($result_anggota)['total_anggota'];
$total_buku = mysqli_fetch_assoc($result_buku)['total_buku'];
$total_peminjaman = mysqli_fetch_assoc($result_peminjaman)['total_peminjaman'];
$total_pengembalian = mysqli_fetch_assoc($result_pengembalian)['total_pengembalian'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan</title>
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

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .dashboard-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
        }

        .dashboard-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
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
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="../modul/anggota/daftar.php">
                <i class="fas fa-users"></i>Anggota
            </a>
            <a href="../modul/buku/daftar_buku.php">
                <i class="fas fa-book"></i>Buku
            </a>
            <a href="../modul/peminjaman/laporan_peminjaman.php">
                <i class="fas fa-exchange-alt"></i>Peminjaman
            </a>
            <a href="../modul/denda/denda.php">
                <i class="fas fa-exchange-alt"></i>Denda
            </a>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 style="color: var(--primary-color); margin-bottom: 30px;">Dashboard Admin</h1>
        
        <div class="dashboard">
            <div class="dashboard-card">
                <h3>Total Anggota</h3>
                <div class="card-value"><?php echo $total_anggota; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Total Buku</h3>
                <div class="card-value"><?php echo $total_buku; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Buku Dipinjam</h3>
                <div class="card-value"><?php echo $total_peminjaman; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Buku Dikembalikan</h3>
                <div class="card-value"><?php echo $total_pengembalian; ?></div>
            </div>
        </div>

        <div class="recent-loans">
            <h2 style="color: var(--primary-color); margin-bottom: 20px;">Peminjaman Terakhir</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query peminjaman terakhir dengan nama tabel yang benar
                    $query_last_pinjam = "SELECT p.*, a.username AS nama_anggota, b.judul AS judul_buku 
                                          FROM peminjaman p
                                          JOIN anggota a ON p.id_anggota = a.id_anggota
                                          JOIN buku b ON p.id_buku = b.id_buku
                                          ORDER BY p.tanggal_pinjam DESC
                                          LIMIT 5";
                    $result_last_pinjam = runQuery($koneksi, $query_last_pinjam);
                    
                    $no = 1;
                    while ($pinjam = mysqli_fetch_assoc($result_last_pinjam)) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($pinjam['nama_anggota']) . "</td>";
                        echo "<td>" . htmlspecialchars($pinjam['judul_buku']) . "</td>";
                        echo "<td>" . $pinjam['tanggal_pinjam'] . "</td>";
                        echo "<td>" . $pinjam['status'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>