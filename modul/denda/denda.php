<?php
// modul/denda/laporan_denda.php
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/layout.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Proses pencarian
$search = isset($_GET['search']) 
    ? mysqli_real_escape_string($koneksi, $_GET['search']) 
    : '';

// Pagination
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Hitung total data (tanpa LIMIT)
$query_count = "
    SELECT COUNT(*) as total
    FROM denda d
      JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
      JOIN peminjaman     p  ON pg.id_peminjaman   = p.id_peminjaman
      JOIN anggota        a  ON p.id_anggota       = a.id_anggota
    WHERE a.username LIKE '%$search%'
       OR a.no_hp    LIKE '%$search%'
";
$result_count  = mysqli_query($koneksi, $query_count);
$data_count    = mysqli_fetch_assoc($result_count);
$total_data    = $data_count['total'];
$total_page    = ceil($total_data / $limit);

// Ambil data denda dengan pagination
$query = "
    SELECT 
        d.id_denda,
        a.username,
        a.no_hp,
        b.judul,
        p.tanggal_kembali,
        d.nominal_denda,
        d.tanggal_bayar,
        d.status_pembayaran
    FROM denda d
      JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
      JOIN peminjaman     p  ON pg.id_peminjaman   = p.id_peminjaman
      JOIN anggota        a  ON p.id_anggota       = a.id_anggota
      JOIN buku           b  ON p.id_buku          = b.id_buku
    WHERE a.username LIKE '%$search%'
       OR a.no_hp    LIKE '%$search%'
    ORDER BY d.tanggal_bayar DESC
    LIMIT $start, $limit
";
$result = mysqli_query($koneksi, $query);

// Render header (aktifkan menu “denda”)
renderHeader("Laporan Denda", "denda");
?>

<div class="page-header">
    <h1>Laporan Denda</h1>
    <div>
        <a href="cetak.php" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Laporan
        </a>
    </div>
</div>

<?php 
// Flash messages
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
    unset($_SESSION['error']);
}
?>

<div class="card">
    <!-- Form Pencarian -->
    <form method="get" class="search-form" style="margin-bottom:20px; display:flex; gap:10px;">
        <input 
            type="text" 
            name="search" 
            placeholder="Cari username atau no HP..." 
            value="<?php echo htmlspecialchars($search); ?>"
            style="flex-grow:1; padding:10px; border:1px solid #ddd; border-radius:5px;"
        >
        <button type="submit" class="btn" style="padding:10px 15px;">
            <i class="fas fa-search"></i> Cari
        </button>
    </form>

    <!-- Tabel Denda -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>No HP</th>
                    <th>Judul Buku</th>
                    <th>Batas Kembali</th>
                    <th>Jumlah Denda</th>
                    <th>Tanggal Bayar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = $start + 1;
                if (mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        $cls = $row['status_pembayaran'] === 'Belum' 
                               ? 'badge-danger' 
                               : 'badge-success';
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                    <td>Rp <?php echo number_format($row['nominal_denda'], 0, ',', '.'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_bayar'])); ?></td>
                    <td>
                        <span class="badge <?php echo $cls; ?>">
                            <?php echo htmlspecialchars($row['status_pembayaran']); ?>
                        </span>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data denda</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" style="margin-top:20px; display:flex; justify-content:center;">
        <?php if($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="btn" style="margin-right:10px;">
            <i class="fas fa-chevron-left"></i> Sebelumnya
        </a>
        <?php endif; ?>

        <?php if($page < $total_page): ?>
        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" class="btn">
            Selanjutnya <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Custom CSS for this page -->
<link rel="stylesheet" href="../../assets/css/denda/laporan_denda.css">

<?php
// Render footer
renderFooter();
?>
