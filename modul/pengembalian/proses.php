<?php
// modul/pengembalian/proses.php
session_start();
require_once '../../config/koneksi.php';

// Cek hak akses
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Proses pengembalian
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_peminjaman = intval($_POST['id_peminjaman']);
    $tanggal_kembali_aktual = date('Y-m-d');

    // Mulai transaksi
    $koneksi->begin_transaction();

    try {
        // Ambil detail peminjaman
        $query_pinjam = "SELECT 
                            p.id_peminjaman, 
                            p.id_buku, 
                            p.tanggal_kembali AS tanggal_kembali_rencana,
                            b.judul AS judul_buku
                        FROM tabel_peminjaman p
                        JOIN tabel_buku b ON p.id_buku = b.id_buku
                        WHERE p.id_peminjaman = '$id_peminjaman'";
        $result_pinjam = runQuery($koneksi, $query_pinjam);
        $peminjaman = mysqli_fetch_assoc($result_pinjam);

        // Hitung keterlambatan
        $tanggal_kembali_rencana = new DateTime($peminjaman['tanggal_kembali_rencana']);
        $tanggal_kembali_aktual_obj = new DateTime($tanggal_kembali_aktual);
        
        $selisih_hari = max(0, $tanggal_kembali_aktual_obj->diff($tanggal_kembali_rencana)->days);
        $denda = $selisih_hari * 5000; // Contoh: denda Rp 5000 per hari

        // Query update peminjaman
        $query_update_pinjam = "UPDATE tabel_peminjaman 
                                SET status = 'Dikembalikan' 
                                WHERE id_peminjaman = '$id_peminjaman'";
        runQuery($koneksi, $query_update_pinjam);

        // Query tambah pengembalian
        $query_pengembalian = "INSERT INTO tabel_pengembalian (
            id_peminjaman, 
            tanggal_kembali_aktual, 
            jumlah_hari_terlambat, 
            denda
        ) VALUES (
            '$id_peminjaman', 
            '$tanggal_kembali_aktual', 
            '$selisih_hari', 
            '$denda'
        )";
        runQuery($koneksi, $query_pengembalian);

        // Kembalikan stok buku
        $query_update_buku = "UPDATE tabel_buku 
                              SET jumlah_tersedia = jumlah_tersedia + 1 
                              WHERE id_buku = '{$peminjaman['id_buku']}'";
        runQuery($koneksi, $query_update_buku);

        // Jika ada denda, tambahkan ke tabel denda
        if ($denda > 0) {
            $query_denda = "INSERT INTO tabel_denda (
                id_pengembalian, 
                jumlah_hari_terlambat, 
                nominal_denda, 
                status_pembayaran
            ) VALUES (
                LAST_INSERT_ID(), 
                '$selisih_hari', 
                '$denda', 
                'Belum Dibayar'
            )";
            runQuery($koneksi, $query_denda);
        }

        // Commit transaksi
        $koneksi->commit();

        // Pesan sukses dengan informasi denda
        if ($denda > 0) {
            tampilkanPesan("Buku {$peminjaman['judul_buku']} berhasil dikembalikan. Denda keterlambatan: Rp " . number_format($denda, 0, ',', '.'), 'warning');
        } else {
            tampilkanPesan("Buku {$peminjaman['judul_buku']} berhasil dikembalikan.", 'success');
        }

        redirect('laporan.php');
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $koneksi->rollback();
        tampilkanPesan('Gagal memproses pengembalian: ' . $e->getMessage(), 'error');
    }
}

// Ambil daftar peminjaman yang belum dikembalikan
$query_pinjam = "SELECT 
                    p.id_peminjaman, 
                    a.nama AS nama_anggota, 
                    b.judul AS judul_buku, 
                    p.tanggal_pinjam, 
                    p.tanggal_kembali
                FROM tabel_peminjaman p
                JOIN tabel_anggota a ON p.id_anggota = a.id_anggota
                JOIN tabel_buku b ON p.id_buku = b.id_buku
                WHERE p.status = 'Dipinjam'";
$result_pinjam = runQuery($koneksi, $query_pinjam);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Proses Pengembalian</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Proses Pengembalian Buku</h2>
        <form method="POST" action="">
            <select name="id_peminjaman" required>
                <option value="">Pilih Buku yang Dikembalikan</option>
                <?php while($pinjam = mysqli_fetch_assoc($result_pinjam)): ?>
                    <option value="<?php echo $pinjam['id_peminjaman']; ?>">
                        <?php 
                        echo htmlspecialchars($pinjam['nama_anggota'] . ' - ' . 
                            $pinjam['judul_buku'] . ' (Pinjam: ' . 
                            $pinjam['tanggal_pinjam'] . ')'); 
                        ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Proses Pengembalian</button>
        </form>
    </div>
    <script src="../../assets/js/script.js"></script>
</body>
</html>