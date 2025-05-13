<?php
// modul/peminjaman/cetak.php
session_start();
require_once '../../config/koneksi.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Filter tanggal
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-t');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query ambil data peminjaman
$query = "SELECT p.*, a.username as nama_anggota, a.no_hp, b.judul, b.isbn 
          FROM peminjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          JOIN buku b ON p.id_buku = b.id_buku 
          WHERE DATE(p.tanggal_pinjam) BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if (!empty($status)) {
    $query .= " AND p.status = '$status'";
}

$query .= " ORDER BY p.tanggal_pinjam DESC";

$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Perpustakaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
        }

        .info {
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .status-dipinjam {
            color: orange;
        }

        .status-dikembalikan {
            color: green;
        }

        .status-ditolak {
            color: red;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Peminjaman Perpustakaan</h1>
        <div class="info">
            <p>
                Periode: <?php echo date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)); ?>
                <?php if (!empty($status)): ?>
                    | Status: <?php echo htmlspecialchars($status); ?>
                <?php endif; ?>
            </p>
            <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0) :
                while ($row = mysqli_fetch_assoc($result)) : 
                    // Hitung keterlambatan
                    $tgl_kembali = new DateTime($row['tanggal_kembali']);
                    $tgl_sekarang = new DateTime(date('Y-m-d'));
                    $is_terlambat = ($row['status'] == 'Dipinjam' && $tgl_sekarang > $tgl_kembali);
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_anggota']); ?></td>
                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                    <td class="status-<?php echo strtolower($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </td>
                    <td>
                        <?php 
                        if ($is_terlambat) {
                            $selisih = $tgl_sekarang->diff($tgl_kembali);
                            echo "Terlambat {$selisih->days} hari";
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                </tr>
            <?php 
                endwhile; 
            else : 
            ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data peminjaman</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Otomatis cetak saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>