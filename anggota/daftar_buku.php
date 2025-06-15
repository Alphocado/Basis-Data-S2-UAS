<?php
  // modul/peminjaman/daftar_buku.php
  session_start();
  require_once '../config/koneksi.php';
  require_once '../includes/layout_anggota.php';

  // Cek apakah sudah login sebagai anggota
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'anggota') {
    redirect('../../login.php');
  }

  // Proses peminjaman buku
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_buku'])) {
    $id_buku          = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $id_anggota       = $_SESSION['id'];
    $tanggal_pinjam   = date('Y-m-d');
    $tanggal_kembali  = date('Y-m-d', strtotime('+7 days'));

    $cek_pinjam = "SELECT * FROM peminjaman 
          WHERE id_buku = '$id_buku' 
          AND (status = 'Dipinjam' OR status = 'Menunggu Persetujuan')";
    $result_cek = mysqli_query($koneksi, $cek_pinjam);

    if (mysqli_num_rows($result_cek) > 0) {
      $_SESSION['error'] = "Buku sudah sedang dipinjam atau menunggu persetujuan";
    } else {
      $query = "INSERT INTO peminjaman 
          (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, status) 
          VALUES 
          ('$id_anggota', '$id_buku', '$tanggal_pinjam', '$tanggal_kembali', 'Menunggu Persetujuan')";

      if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Pengajuan peminjaman buku berhasil. Menunggu persetujuan admin.";
      } else {
        $_SESSION['error'] = "Gagal mengajukan peminjaman: " . mysqli_error($koneksi);
      }
    }

    // Setelah proses selesai, arahkan ke halaman peminjaman.php
    header("Location: peminjaman.php");
    exit();
  }


  // Pencarian dan filter
  $search   = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
  $kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

  // Pagination
  $limit  = 10;
  $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $start  = ($page > 1) ? ($page * $limit) - $limit : 0;

  // Query untuk menghitung total buku
  $query_count = "SELECT COUNT(*) as total FROM buku 
          WHERE (judul LIKE '%$search%' OR 
              pengarang LIKE '%$search%' OR 
              isbn LIKE '%$search%')";

  if (!empty($kategori)) {
    $query_count .= " AND kategori = '$kategori'";
  }

  $result_count = mysqli_query($koneksi, $query_count);
  $data_count   = mysqli_fetch_assoc($result_count);
  $total_data   = $data_count['total'];
  $total_page   = ceil($total_data / $limit);

  // Query ambil data buku
  $query = "SELECT b.* FROM buku b
        WHERE (b.judul LIKE '%$search%' OR 
          b.pengarang LIKE '%$search%' OR 
          b.isbn LIKE '%$search%')";
          
  if (!empty($kategori)) {
    $query .= " AND b.kategori = '$kategori'";
  }

  $query .= " LIMIT $start, $limit";

  $result = mysqli_query($koneksi, $query);

  // Render header
  renderHeader("Daftar Buku Pinjam", "peminjaman");
?>

<link rel="stylesheet" href="../assets/css/anggota.css">


<div class="page-header">
  <h1>Daftar Buku Perpustakaan</h1>
</div>

<?php 
// Tampilkan pesan sukses atau error
if (isset($_SESSION['success'])) {
  echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
  unset($_SESSION['success']);
}
else if (isset($_SESSION['error'])) {
  echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
  unset($_SESSION['error']);
}
?>

<div class="card">
  <!-- Filter dan Pencarian -->
  <form method="get" class="search-form-wrapper">
    <div class="flex-grow">
      <input 
        type="text" 
        name="search" 
        placeholder="Cari buku (judul, pengarang, ISBN)..." 
        value="<?php echo htmlspecialchars($search); ?>"
        class="search-input"
        >
    </div>
    <div>
      <select name="kategori" class="select-kategori">
        <option value="">Semua Kategori</option>
        <option value="Fiksi" <?php echo $kategori == 'Fiksi' ? 'selected' : ''; ?>>Fiksi</option>
        <option value="Non-Fiksi" <?php echo $kategori == 'Non-Fiksi' ? 'selected' : ''; ?>>Non-Fiksi</option>
        <option value="Referensi" <?php echo $kategori == 'Referensi' ? 'selected' : ''; ?>>Referensi</option>
      </select>
    </div>
    <div>
      <button type="submit" class="btn" class="btn btn-search">
        <i class="fas fa-search"></i> Cari
      </button>
    </div>
  </form>

  <!-- Daftar Buku -->
  <?php 
  if (mysqli_num_rows($result) > 0) :
    while ($buku = mysqli_fetch_assoc($result)) : 
  ?>
    <div class="buku-card">
      <div class="buku-thumbnail">
        <i class="fas fa-book"></i>
      </div>
      <div class="buku-info">
        <h3><?php echo htmlspecialchars($buku['judul']); ?></h3>
        <p><strong>Penulis:</strong> <?php echo htmlspecialchars($buku['pengarang']); ?></p>
        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($buku['isbn']); ?></p>
        <p><strong>Kategori:</strong> <?php echo htmlspecialchars($buku['kategori']); ?></p>
        <p><strong>Lokasi Rak:</strong> <?php echo htmlspecialchars($buku['lokasi_rak']); ?></p>
      </div>
      <div class="buku-actions">
        <form method="POST" action="">
          <input type="hidden" name="id_buku" value="<?php echo $buku['id_buku']; ?>">
          <button type="submit" class="btn">
            <i class="fas fa-plus"></i> Pinjam
          </button>
        </form>
      </div>
    </div>
  <?php 
    endwhile;
  else : 
  ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> Tidak ada buku yang tersedia.
    </div>
  <?php endif; ?>

  <!-- Pagination -->
  <div class="pagination-wrapper">
    <?php if($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($kategori); ?>" class="btn" style="margin-right: 10px;">
        <i class="fas fa-chevron-left"></i> Sebelumnya
      </a>
    <?php endif; ?>

    <?php if($page < $total_page): ?>
      <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($kategori); ?>" class="btn">
        Selanjutnya <i class="fas fa-chevron-right"></i>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php
  // Render footer
  renderFooter();
?>