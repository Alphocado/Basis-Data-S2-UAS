<?php
// modul/pengembalian/laporan.php
session_start();
require_once '../../config/koneksi.php';

// Cek hak akses
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Query laporan pengembalian
$query = "SELECT 
            p.id_pengembalian, 
            a.nama AS nama_anggota, 
            b.judul AS judul_buku, 
            pm.tanggal_pinjam,
            p.tanggal_kembali_aktual,
            p.jumlah_hari_terlambat,
            p.denda
          FROM tabel_pengembalian p
          JOIN tabel_peminjaman pm ON p.id_peminjaman = pm.id_peminjaman
          JOIN tabel_anggota a ON pm.id_anggota = a.id_anggota
          JOIN tabel_buku b ON pm.id_buku = b.id_buku
          ORDER BY p.tanggal_kembali_aktual DESC";
$result = runQuery($koneksi, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengembalian</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Laporan Pengembalian Buku</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Hari Terlambat</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_anggota']); ?></td>
                    <td><?php echo htmlspecialchars($row['judul_buku']); ?></td>
                    <td><?php echo $row['tanggal_pinjam']; ?></td>
                    <td><?php echo $row['tanggal_kembali_aktual']; ?></td>
                    <td><?php echo $row['jumlah_hari_terlambat']; ?></td>
                    <td>Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Tombol Cetak Laporan -->
        <div class="action-buttons">
            <button onclick="window.print()">Cetak Laporan</button>
        </div>
    </div>
    <script src="../../assets/js/script.js"></script>
</body>
</html>