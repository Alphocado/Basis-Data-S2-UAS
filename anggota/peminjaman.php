<?php
// anggota/peminjaman.php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/layout.php';

// Cek apakah sudah login sebagai anggota
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'anggota') {
    redirect('../login.php');
}

// Ambil ID anggota dari session
$id_anggota = $_SESSION['id'];

// Proses pencarian dan filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Query untuk menghitung total data
$query_count = "SELECT COUNT(*) as total FROM peminjaman p 
                JOIN buku b ON p.id_buku = b.id_buku
                WHERE p.id_anggota = '$id_anggota'";

if (!empty($search)) {
    $query_count .= " AND (b.judul LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}

if (!empty($filter_status)) {
    $query_count .= " AND p.status = '$filter_status'";
}

$result_count = mysqli_query($koneksi, $query_count);
$data_count = mysqli_fetch_assoc($result_count);
$total_data = $data_count['total'];
$total_page = ceil($total_data / $limit);

// Query ambil data peminjaman
$query = "SELECT p.*, b.judul, b.isbn, 
                 DATE(p.tanggal_pinjam) as tgl_pinjam, 
                 DATE(p.tanggal_kembali) as tgl_kembali
          FROM peminjaman p
          JOIN buku b ON p.id_buku = b.id_buku 
          WHERE p.id_anggota = '$id_anggota'";

if (!empty($search)) {
    $query .= " AND (b.judul LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}

if (!empty($filter_status)) {
    $query .= " AND p.status = '$filter_status'";
}

$query .= " ORDER BY p.tanggal_pinjam DESC
          LIMIT $start, $limit";

$result = mysqli_query($koneksi, $query);

// Render header
renderHeader("Daftar Pinjaman", "peminjaman");
?>

<div class="page-header">
    <h1>Daftar Pinjaman Buku</h1>
    <div>
        <a href="daftar_buku.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Pinjam Buku Baru
        </a>
    </div>
</div>

<?php 
// Tampilkan pesan sukses atau error
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}
?>

<div class="card">
    <!-- Filter dan Pencarian -->
    <form method="get" class="search-form" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <div style="flex-grow: 1;">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari buku (judul atau ISBN)..." 
                value="<?php echo htmlspecialchars($search); ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>
        <div>
            <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Semua Status</option>
                <option value="Menunggu Persetujuan" <?php echo $filter_status == 'Menunggu Persetujuan' ? 'selected' : ''; ?>>Menunggu Persetujuan</option>
                <option value="Dipinjam" <?php echo $filter_status == 'Dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                <option value="Dikembalikan" <?php echo $filter_status == 'Dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                <option value="Ditolak" <?php echo $filter_status == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn" style="padding: 10px 15px;">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>
    </form>

    <!-- Tabel Peminjaman -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>ISBN</th>
                    <th>Tanggal Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = $start + 1;
                if (mysqli_num_rows($result) > 0) :
                    while ($peminjaman = mysqli_fetch_assoc($result)) : 
                        // Tentukan warna status
                        $status_class = 'badge-warning';
                        switch ($peminjaman['status']) {
                            case 'Dipinjam':
                                $status_class = 'badge-primary';
                                break;
                            case 'Dikembalikan':
                                $status_class = 'badge-success';
                                break;
                            case 'Ditolak':
                                $status_class = 'badge-danger';
                                break;
                        }

                        // Hitung keterlambatan
                        $tgl_kembali = new DateTime($peminjaman['tgl_kembali']);
                        $tgl_sekarang = new DateTime(date('Y-m-d'));
                        $is_terlambat = ($peminjaman['status'] == 'Dipinjam' && $tgl_sekarang > $tgl_kembali);
                ?>
                    <tr <?php echo $is_terlambat ? 'style="background-color: #ffeeee;"' : ''; ?>>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($peminjaman['judul']); ?></td>
                        <td><?php echo htmlspecialchars($peminjaman['isbn']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($peminjaman['tgl_pinjam'])); ?></td>
                        <td>
                            <?php 
                            echo date('d/m/Y', strtotime($peminjaman['tgl_kembali'])); 
                            if ($is_terlambat) {
                                $selisih = $tgl_sekarang->diff($tgl_kembali);
                                echo ' <span class="badge bg-danger">Terlambat ' . $selisih->days . ' hari</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($peminjaman['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($peminjaman['status'] == 'Menunggu Persetujuan'): ?>
                                <span class="badge badge-warning">Menunggu Konfirmasi Admin</span>
                            <?php elseif ($peminjaman['status'] == 'Dipinjam'): ?>
                                <!-- Tambahkan informasi atau aksi tambahan jika diperlukan -->
                                <span class="badge badge-primary">Sedang Dipinjam</span>
                            <?php elseif ($peminjaman['status'] == 'Ditolak'): ?>
                                <span class="badge badge-danger">Peminjaman Ditolak</span>
                            <?php else: ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    endwhile; 
                else : 
                ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data peminjaman</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center;">
        <?php if($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>" class="btn" style="margin-right: 10px;">
                <i class="fas fa-chevron-left"></i> Sebelumnya
            </a>
        <?php endif; ?>

        <?php if($page < $total_page): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>" class="btn">
                Selanjutnya <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
// Render footer
renderFooter();
?>