<?php
// modul/anggota/daftar.php
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/layout.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Proses pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Query dengan pencarian
$query_count = "SELECT COUNT(*) as total FROM anggota 
                WHERE username LIKE '%$search%' OR 
                      email LIKE '%$search%' OR 
                      no_hp LIKE '%$search%'";
$result_count = mysqli_query($koneksi, $query_count);
$data_count = mysqli_fetch_assoc($result_count);
$total_data = $data_count['total'];
$total_page = ceil($total_data / $limit);

// Query ambil data
$query = "SELECT * FROM anggota 
          WHERE username LIKE '%$search%' OR 
                email LIKE '%$search%' OR 
                no_hp LIKE '%$search%'
          LIMIT $start, $limit";
$result = mysqli_query($koneksi, $query);

// Render header
renderHeader("Daftar Anggota", "anggota");
?>

<div class="page-header">
    <h1>Daftar Anggota</h1>
    <div>
        <a href="tambah.php" class="btn">
            <i class="fas fa-plus"></i> Tambah Anggota
        </a>
    </div>
</div>

<div class="card">
    <!-- Pencarian -->
    <form method="get" class="search-form" style="margin-bottom: 20px; display: flex;">
        <input 
            type="text" 
            name="search" 
            placeholder="Cari anggota (username, email, no hp)..." 
            value="<?php echo htmlspecialchars($search); ?>"
            style="flex-grow: 1; padding: 10px; margin-right: 10px; border: 1px solid #ddd; border-radius: 5px;"
        >
        <button type="submit" class="btn" style="padding: 10px 15px;">
            <i class="fas fa-search"></i>
        </button>
    </form>

    <!-- Tabel Anggota -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Jenis Kelamin</th>
                    <th>Tanggal Daftar</th>
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = $start + 1;
                while ($row = mysqli_fetch_assoc($result)) : 
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                        <td><?php 
                            echo $row['jenis_kelamin'] == 1 ? 'Laki-laki' : 'Perempuan'; 
                        ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></td>
                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id_anggota']; ?>" class="btn" style="margin-right: 5px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus.php?id=<?php echo $row['id_anggota']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center;">
        <?php if($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="btn" style="margin-right: 10px;">
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

<?php
// Render footer
renderFooter();
?>