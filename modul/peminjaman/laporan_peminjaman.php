<?php
// modul/peminjaman/laporan_peminjaman.php
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/layout.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Proses konfirmasi atau penolakan peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['id_peminjaman'])) {
        $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
        $action = mysqli_real_escape_string($koneksi, $_POST['action']);

        // Query untuk update status
        switch ($action) {
            case 'terima':
                $query = "UPDATE peminjaman SET status = 'Dipinjam' WHERE id_peminjaman = '$id_peminjaman'";
                $pesan = "Peminjaman buku berhasil disetujui";
                break;
            case 'tolak':
                $query = "UPDATE peminjaman SET status = 'Ditolak' WHERE id_peminjaman = '$id_peminjaman'";
                $pesan = "Peminjaman buku ditolak";
                break;
            case 'kembalikan':
              // ambil tanggal rencana
              $data          = mysqli_fetch_assoc(mysqli_query($koneksi,
                                  "SELECT tanggal_kembali FROM peminjaman WHERE id_peminjaman='$id_peminjaman'"));
              $tgl_rencana   = new DateTime($data['tanggal_kembali']);
              $tgl_aktual    = new DateTime(date('Y-m-d'));
              $terlambat     = max(0, $tgl_aktual->diff($tgl_rencana)->days);
              $denda_per_hari = 1000;
              $total_denda   = $terlambat * $denda_per_hari;
          
              // insert pengembalian
              $q1 = "INSERT INTO pengembalian (
                          id_peminjaman,
                          tanggal_dikembalikan,
                          denda
                      ) VALUES (
                          '$id_peminjaman',
                          '{$tgl_aktual->format('Y-m-d')}',
                          '$total_denda'
                      )";
              mysqli_query($koneksi, $q1);
          
              // insert denda ke tabel denda jika ada keterlambatan
              if ($terlambat > 0) {
                  $last_id = mysqli_insert_id($koneksi); // ini id_pengembalian
                  $q2 = "INSERT INTO denda (
                              id_pengembalian,
                              jumlah_denda,
                              nominal_denda,
                              status_pembayaran
                          ) VALUES (
                              '$last_id',
                              '$terlambat',
                              '$total_denda',
                              'Belum'
                          )";
                  mysqli_query($koneksi, $q2);
              }
          
              // update status peminjaman
              $query = "UPDATE peminjaman 
                        SET status = 'Dikembalikan' 
                        WHERE id_peminjaman = '$id_peminjaman'";
              $pesan = "Buku berhasil dikembalikan";
              break;
              
        }

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = $pesan;
        } else {
            $_SESSION['error'] = "Gagal memproses: " . mysqli_error($koneksi);
        }
    }
}

// Proses pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Query untuk menghitung total data
$query_count = "SELECT COUNT(*) as total FROM peminjaman p 
                JOIN anggota a ON p.id_anggota = a.id_anggota
                JOIN buku b ON p.id_buku = b.id_buku
                WHERE (a.username LIKE '%$search%' OR 
                       b.judul LIKE '%$search%' OR 
                       a.no_hp LIKE '%$search%')";

// Filter status
if (!empty($filter_status)) {
    $query_count .= " AND p.status = '$filter_status'";
}

$result_count = mysqli_query($koneksi, $query_count);
$data_count = mysqli_fetch_assoc($result_count);
$total_data = $data_count['total'];
$total_page = ceil($total_data / $limit);

// Query ambil data peminjaman
$query = "SELECT p.*, a.username as nama_anggota, a.no_hp, b.judul, b.isbn, 
                 DATE(p.tanggal_pinjam) as tgl_pinjam, 
                 DATE(p.tanggal_kembali) as tgl_kembali
          FROM peminjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          JOIN buku b ON p.id_buku = b.id_buku 
          WHERE (a.username LIKE '%$search%' OR 
                 b.judul LIKE '%$search%' OR 
                 a.no_hp LIKE '%$search%')";

// Filter status
if (!empty($filter_status)) {
    $query .= " AND p.status = '$filter_status'";
}

$query .= " ORDER BY p.tanggal_pinjam DESC
          LIMIT $start, $limit";

$result = mysqli_query($koneksi, $query);

// Render header
renderHeader("Laporan Peminjaman", "peminjaman");
?>

<div class="page-header">
    <h1>Laporan Peminjaman Buku</h1>
    <div>
        <a href="cetak.php" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Laporan
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
                placeholder="Cari username, judul buku, atau no HP..." 
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
                    <th>Username</th>
                    <th>No HP</th>
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
                        <td><?php echo htmlspecialchars($peminjaman['nama_anggota']); ?></td>
                        <td><?php echo htmlspecialchars($peminjaman['no_hp']); ?></td>
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
                                <div class="btn-group">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $peminjaman['id_peminjaman']; ?>">
                                        <button type="submit" name="action" value="terima" class="btn btn-success btn-sm" onclick="return confirm('Setujui peminjaman buku ini?');">
                                            <i class="fas fa-check"></i> Terima
                                        </button>
                                    </form>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $peminjaman['id_peminjaman']; ?>">
                                        <button type="submit" name="action" value="tolak" class="btn btn-danger btn-sm" onclick="return confirm('Tolak peminjaman buku ini?');">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($peminjaman['status'] == 'Dipinjam'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="id_peminjaman" value="<?php echo $peminjaman['id_peminjaman']; ?>">
                                    <button type="submit" name="action" value="kembalikan" class="btn btn-warning btn-sm" onclick="return confirm('Konfirmasi pengembalian buku?');">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="detail.php?id=<?php echo $peminjaman['id_peminjaman']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    endwhile; 
                else : 
                ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data peminjaman</td>
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

<link rel="stylesheet" href="../../assets/css/peminjaman/laporan_peminjaman.css">

<?php
// Render footer
renderFooter();
?>