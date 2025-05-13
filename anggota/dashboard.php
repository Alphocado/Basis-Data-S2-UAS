<?php
// anggota/index.php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/layout.php';

// Cek apakah sudah login sebagai anggota
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'anggota') {
    redirect('../login.php');
}

// Ambil ID anggota dari session
$id_anggota = $_SESSION['id'];

// Fungsi untuk menjalankan query dan menangani error
function safeRunQuery($koneksi, $query) {
    $result = mysqli_query($koneksi, $query);
    if (!$result) {
        // Tangani error query
        error_log("Query Error: " . mysqli_error($koneksi));
        return null;
    }
    return $result;
}

// Ambil statistik untuk anggota
$query_peminjaman_aktif = "SELECT COUNT(*) as total_peminjaman FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'Dipinjam'";
$query_peminjaman_selesai = "SELECT COUNT(*) as total_selesai FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'Dikembalikan'";
$query_total_denda = "SELECT COALESCE(SUM(d.nominal_denda), 0) as total_denda
                      FROM peminjaman p
                      JOIN pengembalian pn ON p.id_peminjaman = pn.id_peminjaman
                      JOIN denda d ON pn.id_pengembalian = d.id_pengembalian
                      WHERE p.id_anggota = '$id_anggota' AND d.status_pembayaran = 'Belum'";
$query_total_buku = "SELECT COUNT(DISTINCT b.id_buku) as total_buku
                     FROM peminjaman p
                     JOIN buku b ON p.id_buku = b.id_buku
                     WHERE p.id_anggota = '$id_anggota'";

// Jalankan query dengan penanganan error
$result_peminjaman_aktif = safeRunQuery($koneksi, $query_peminjaman_aktif);
$result_peminjaman_selesai = safeRunQuery($koneksi, $query_peminjaman_selesai);
$result_total_denda = safeRunQuery($koneksi, $query_total_denda);
$result_total_buku = safeRunQuery($koneksi, $query_total_buku);

// Periksa hasil query
$total_peminjaman = $result_peminjaman_aktif ? mysqli_fetch_assoc($result_peminjaman_aktif)['total_peminjaman'] : 0;
$total_selesai = $result_peminjaman_selesai ? mysqli_fetch_assoc($result_peminjaman_selesai)['total_selesai'] : 0;
$total_denda = $result_total_denda ? mysqli_fetch_assoc($result_total_denda)['total_denda'] : 0;
$total_buku = $result_total_buku ? mysqli_fetch_assoc($result_total_buku)['total_buku'] : 0;

// Render header
renderHeader("Dashboard Anggota", "dashboard");
?>

<style>
    /* Card Styles */
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

    /* Warna khusus untuk setiap card */
    .card-peminjaman-aktif .dashboard-card-icon {
        background-color: #3498db;
    }

    .card-buku-dikembalikan .dashboard-card-icon {
        background-color: #2ecc71;
    }

    .card-total-buku .dashboard-card-icon {
        background-color: #9b59b6;
    }

    .card-total-denda .dashboard-card-icon {
        background-color: #e74c3c;
    }
</style>

<div class="dashboard">
    <div class="dashboard-card card-peminjaman-aktif">
        <div class="dashboard-card-icon">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="dashboard-card-content">
            <div class="dashboard-card-title">Peminjaman Aktif</div>
            <div class="dashboard-card-value"><?php echo $total_peminjaman; ?></div>
        </div>
    </div>

    <div class="dashboard-card card-buku-dikembalikan">
        <div class="dashboard-card-icon">
            <i class="fas fa-undo"></i>
        </div>
        <div class="dashboard-card-content">
            <div class="dashboard-card-title">Buku Dikembalikan</div>
            <div class="dashboard-card-value"><?php echo $total_selesai; ?></div>
        </div>
    </div>

    <div class="dashboard-card card-total-buku">
        <div class="dashboard-card-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="dashboard-card-content">
            <div class="dashboard-card-title">Total Buku</div>
            <div class="dashboard-card-value"><?php echo $total_buku; ?></div>
        </div>
    </div>

    <div class="dashboard-card card-total-denda">
        <div class="dashboard-card-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="dashboard-card-content">
            <div class="dashboard-card-title">Total Denda</div>
            <div class="dashboard-card-value">Rp <?php echo number_format($total_denda, 0, ',', '.'); ?></div>
        </div>
    </div>
</div>

<?php
// Ambil peminjaman terakhir
$query_last_pinjam = "SELECT p.*, b.judul AS judul_buku 
                      FROM peminjaman p
                      JOIN buku b ON p.id_buku = b.id_buku
                      WHERE p.id_anggota = '$id_anggota'
                      ORDER BY p.tanggal_pinjam DESC
                      LIMIT 5";
$result_last_pinjam = safeRunQuery($koneksi, $query_last_pinjam);

?>

<div class="recent-loans">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Peminjaman Terakhir</h2>
    <?php if ($result_last_pinjam && mysqli_num_rows($result_last_pinjam) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($pinjam = mysqli_fetch_assoc($result_last_pinjam)) {
                    // Tentukan warna status
                    $status_class = $pinjam['status'] == 'Dipinjam' ? 'badge-warning' : 'badge-success';
                    
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($pinjam['judul_buku']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])) . "</td>";
                    echo "<td><span class='badge " . $status_class . "'>" . htmlspecialchars($pinjam['status']) . "</span></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Anda belum memiliki riwayat peminjaman.
        </div>
    <?php endif; ?>
</div>

<?php
// Render footer
renderFooter();
?>