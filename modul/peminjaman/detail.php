<?php
// modul/peminjaman/detail.php
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/layout.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    redirect('../../login.php');
}

// Cek apakah ada ID peminjaman
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID Peminjaman tidak valid";
    redirect('laporan_peminjaman.php');
}

$id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['id']);

// Query detail peminjaman
$query = "SELECT p.*, 
                 a.username, 
                 a.email, 
                 a.no_hp, 
                 a.alamat,
                 b.judul, 
                 b.isbn, 
                 b.pengarang, 
                 b.penerbit,
                 b.kategori
          FROM peminjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          JOIN buku b ON p.id_buku = b.id_buku
          WHERE p.id_peminjaman = '$id_peminjaman'";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Data peminjaman tidak ditemukan";
    redirect('laporan_peminjaman.php');
}

$peminjaman = mysqli_fetch_assoc($result);

// Cek apakah ada denda
$query_denda = "SELECT * FROM denda d
                JOIN pengembalian pn ON d.id_pengembalian = pn.id_pengembalian
                WHERE pn.id_peminjaman = '$id_peminjaman'";
$result_denda = mysqli_query($koneksi, $query_denda);
$denda = mysqli_fetch_assoc($result_denda);

// Tentukan warna status
$status_class = 'bg-warning';
switch ($peminjaman['status']) {
    case 'Dipinjam':
        $status_class = 'bg-primary';
        break;
    case 'Dikembalikan':
        $status_class = 'bg-success';
        break;
    case 'Ditolak':
        $status_class = 'bg-danger';
        break;
}

// Render header
renderHeader("Detail Peminjaman", "peminjaman");
?>

<div class="page-header">
    <h1>Detail Peminjaman</h1>
    <div>
        <a href="laporan_peminjaman.php" class="btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Informasi Anggota -->
    <div class="col-md-6">
        <div class="card">
            <h3>Informasi Anggota</h3>
            <table class="table">
                <tr>
                    <th>Username</th>
                    <td><?php echo htmlspecialchars($peminjaman['username']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($peminjaman['email']); ?></td>
                </tr>
                <tr>
                    <th>No HP</th>
                    <td><?php echo htmlspecialchars($peminjaman['no_hp']); ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?php echo htmlspecialchars($peminjaman['alamat']); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Informasi Buku -->
    <div class="col-md-6">
        <div class="card">
            <h3>Informasi Buku</h3>
            <table class="table">
                <tr>
                    <th>Judul</th>
                    <td><?php echo htmlspecialchars($peminjaman['judul']); ?></td>
                </tr>
                <tr>
                    <th>ISBN</th>
                    <td><?php echo htmlspecialchars($peminjaman['isbn']); ?></td>
                </tr>
                <tr>
                    <th>Pengarang</th>
                    <td><?php echo htmlspecialchars($peminjaman['pengarang']); ?></td>
                </tr>
                <tr>
                    <th>Penerbit</th>
                    <td><?php echo htmlspecialchars($peminjaman['penerbit']); ?></td>
                </tr>
                <tr>
                    <th>Kategori</th>
                    <td><?php echo htmlspecialchars($peminjaman['kategori']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Informasi Peminjaman -->
<div class="card mt-4">
    <h3>Detail Peminjaman</h3>
    <table class="table">
        <tr>
            <th>Tanggal Pinjam</th>
            <td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></td>
        </tr>
        <tr>
            <th>Batas Pengembalian</th>
            <td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($peminjaman['status']); ?>
                </span>
            </td>
        </tr>
        <?php if ($denda): ?>
            <tr>
                <th>Denda</th>
                <td>
                    Rp <?php echo number_format($denda['nominal_denda'], 0, ',', '.'); ?>
                    <span class="badge <?php echo $denda['status_pembayaran'] == 'Lunas' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo htmlspecialchars($denda['status_pembayaran']); ?>
                    </span>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<link rel="stylesheet" href="../../assets/css/peminjaman/detail.css">

<?php
// Render footer
renderFooter();
?>