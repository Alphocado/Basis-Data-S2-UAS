<?php
  // anggota/peminjaman.php
  session_start();
  require_once '../config/koneksi.php';
  require_once '../includes/layout.php';

  // Cek apakah sudah login sebagai anggota
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'anggota') {
    redirect('../login.php');
  }

  // Ambil data anggota
  $id_anggota = $_SESSION['id_anggota'] ?? 0;

  // Filter status
  $filter_status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

  // Pagination
  $limit = 10; // Jumlah data per halaman
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $start = ($page > 1) ? ($page * $limit) - $limit : 0;

  // Query untuk menghitung total data
  $query_count = "SELECT COUNT(*) as total FROM peminjaman 
          WHERE id_anggota = '$id_anggota'";

  if (!empty($filter_status)) {
    $query_count .= " AND status = '$filter_status'";
  }

  $result_count = mysqli_query($koneksi, $query_count);
  $data_count = mysqli_fetch_assoc($result_count);
  $total_data = $data_count['total'] ?? 0;
  $total_page = ceil($total_data / $limit);

  // Query ambil data peminjaman
  $query = "SELECT p.*, b.judul, b.isbn, b.kategori,
          DATE(p.tanggal_pinjam) as tgl_pinjam, 
          DATE(p.tanggal_kembali) as tgl_kembali,
          pn.tanggal_dikembalikan,
          d.nominal_denda, d.status_pembayaran
        FROM peminjaman p
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN pengembalian pn ON p.id_peminjaman = pn.id_peminjaman
        LEFT JOIN denda d ON pn.id_pengembalian = d.id_pengembalian
        WHERE p.id_anggota = '$id_anggota'";

  if (!empty($filter_status)) {
    $query .= " AND p.status = '$filter_status'";
  }

  $query .= " ORDER BY p.tanggal_pinjam DESC
        LIMIT $start, $limit";

  $result = mysqli_query($koneksi, $query);

  // Render header
  renderHeader("Daftar Peminjaman", "peminjaman");
?>

<link rel="stylesheet" href="../../assets/css/peminjaman.css">

<div class="page-header">
  <h1>Daftar Peminjaman Buku</h1>
  <div>
    <a href="index.php" class="btn">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="card">
  <!-- Filter -->
  <form method="get" class="filter-form">
    <div>
      <select name="status" class="select-status">
        <option value="">Semua Status</option>
        <option value="Dipinjam" <?php echo $filter_status == 'Dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
        <option value="Dikembalikan" <?php echo $filter_status == 'Dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
      </select>
    </div>
    <div>
      <button type="submit" class="btn btn-filter">
        <i class="fas fa-filter"></i> Filter
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
          <th>Kategori</th>
          <th>Tanggal Pinjam</th>
          <th>Batas Kembali</th>
          <th>Tanggal Kembali</th>
          <th>Status</th>
          <th>Denda</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = $start + 1;
        if ($result && mysqli_num_rows($result) > 0) :
          while ($pinjam = mysqli_fetch_assoc($result)) : 
            // Hitung keterlambatan
            $is_terlambat = false;
            if ($pinjam['status'] == 'Dipinjam') {
              $tgl_kembali = new DateTime($pinjam['tgl_kembali']);
              $tgl_sekarang = new DateTime(date('Y-m-d'));
              $is_terlambat = $tgl_sekarang > $tgl_kembali;
            }
        ?>
          <tr class="<?php echo $is_terlambat ? 'row-terlambat' : ''; ?>">
            <td><?php echo $no++; ?></td>
            <td><?php echo isset($pinjam['judul']) ? htmlspecialchars($pinjam['judul']) : '-'; ?></td>
            <td><?php echo isset($pinjam['isbn']) ? htmlspecialchars($pinjam['isbn']) : '-'; ?></td>
            <td><?php echo isset($pinjam['kategori']) ? htmlspecialchars($pinjam['kategori']) : '-'; ?></td>
            <td><?php echo isset($pinjam['tgl_pinjam']) ? date('d/m/Y', strtotime($pinjam['tgl_pinjam'])) : '-'; ?></td>
            <td>
              <?php 
              if (isset($pinjam['tgl_kembali'])) {
                echo date('d/m/Y', strtotime($pinjam['tgl_kembali']));
                if ($is_terlambat) {
                  $selisih = $tgl_sekarang->diff($tgl_kembali);
                  echo ' <span class="badge bg-danger">Terlambat ' . $selisih->days . ' hari</span>';
                }
              } else {
                echo '-';
              }
              ?>
            </td>
            <td>
              <?php 
              echo isset($pinjam['tanggal_dikembalikan']) && $pinjam['tanggal_dikembalikan'] ? 
                date('d/m/Y', strtotime($pinjam['tanggal_dikembalikan'])) : '-'; 
              ?>
            </td>
            <td>
              <?php if (isset($pinjam['status']) && $pinjam['status'] == 'Dipinjam') : ?>
                <span class="badge bg-warning">Dipinjam</span>
              <?php else : ?>
                <span class="badge bg-success">Dikembalikan</span>
              <?php endif; ?>
            </td>
            <td>
              <?php 
              if (isset($pinjam['nominal_denda']) && $pinjam['nominal_denda'] > 0) {
                echo 'Rp ' . number_format($pinjam['nominal_denda'], 0, ',', '.');
                if (isset($pinjam['status_pembayaran'])) {
                  if ($pinjam['status_pembayaran'] == 'Lunas') {
                    echo ' <span class="badge bg-success">Lunas</span>';
                  } else {
                    echo ' <span class="badge bg-danger">Belum</span>';
                  }
                }
              } else {
                echo '-';
              }
              ?>
            </td>
          </class=>
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
  <?php if ($total_page > 1) : ?>
    <div class="pagination">
      <?php if($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn btn-prev">
          <i class="fas fa-chevron-left"></i> Sebelumnya
        </a>
      <?php endif; ?>

      <?php if($page < $total_page): ?>
        <a href="?page=<?php echo $page+1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn">
          Selanjutnya <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php
// Render footer
renderFooter();
?>

<div class="page-header">
  <h1>Daftar Peminjaman Buku</h1>
  <div>
    <a href="index.php" class="btn">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="card">
  <!-- Filter -->
  <form method="get" class="filter-form">
    <div>
      <select name="status" class="select-status">
        <option value="">Semua Status</option>
        <option value="Dipinjam" <?php echo $filter_status == 'Dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
        <option value="Dikembalikan" <?php echo $filter_status == 'Dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
      </select>
    </div>
    <div>
      <button type="submit" class="btn btn-filter">
        <i class="fas fa-filter"></i> Filter
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
          <th>Kategori</th>
          <th>Tanggal Pinjam</th>
          <th>Batas Kembali</th>
          <th>Tanggal Kembali</th>
          <th>Status</th>
          <th>Denda</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = $start + 1;
        if ($result && mysqli_num_rows($result) > 0) :
          while ($pinjam = mysqli_fetch_assoc($result)) : 
            // Hitung keterlambatan
            $is_terlambat = false;
            if ($pinjam['status'] == 'Dipinjam') {
              $tgl_kembali = new DateTime($pinjam['tgl_kembali']);
              $tgl_sekarang = new DateTime(date('Y-m-d'));
              $is_terlambat = $tgl_sekarang > $tgl_kembali;
            }
        ?>
          <tr class="<?php echo $is_terlambat ? 'row-terlambat' : ''; ?>">
            <td><?php echo $no++; ?></td>
            <td><?php echo isset($pinjam['judul']) ? htmlspecialchars($pinjam['judul']) : '-'; ?></td>
            <td><?php echo isset($pinjam['isbn']) ? htmlspecialchars($pinjam['isbn']) : '-'; ?></td>
            <td><?php echo isset($pinjam['kategori']) ? htmlspecialchars($pinjam['kategori']) : '-'; ?></td>
            <td><?php echo isset($pinjam['tgl_pinjam']) ? date('d/m/Y', strtotime($pinjam['tgl_pinjam'])) : '-'; ?></td>
            <td>
              <?php 
              if (isset($pinjam['tgl_kembali'])) {
                echo date('d/m/Y', strtotime($pinjam['tgl_kembali']));
                if ($is_terlambat) {
                  $selisih = $tgl_sekarang->diff($tgl_kembali);
                  echo ' <span class="badge bg-danger">Terlambat ' . $selisih->days . ' hari</span>';
                }
              } else {
                echo '-';
              }
              ?>
            </td>
            <td>
              <?php 
              echo isset($pinjam['tanggal_dikembalikan']) && $pinjam['tanggal_dikembalikan'] ? 
                date('d/m/Y', strtotime($pinjam['tanggal_dikembalikan'])) : '-'; 
              ?>
            </td>
            <td>
              <?php if (isset($pinjam['status']) && $pinjam['status'] == 'Dipinjam') : ?>
                <span class="badge bg-warning">Dipinjam</span>
              <?php else : ?>
                <span class="badge bg-success">Dikembalikan</span>
              <?php endif; ?>
            </td>
            <td>
              <?php 
              if (isset($pinjam['nominal_denda']) && $pinjam['nominal_denda'] > 0) {
                echo 'Rp ' . number_format($pinjam['nominal_denda'], 0, ',', '.');
                if (isset($pinjam['status_pembayaran'])) {
                  if ($pinjam['status_pembayaran'] == 'Lunas') {
                    echo ' <span class="badge bg-success">Lunas</span>';
                  } else {
                    echo ' <span class="badge bg-danger">Belum</span>';
                  }
                }
              } else {
                echo '-';
              }
              ?>
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
  <?php if ($total_page > 1) : ?>
    <div class="pagination">
      <?php if($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn btn-prev">
          <i class="fas fa-chevron-left"></i> Sebelumnya
        </a>
      <?php endif; ?>

      <?php if($page < $total_page): ?>
        <a href="?page=<?php echo $page+1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn">
          Selanjutnya <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php
// Render footer
renderFooter();
?>