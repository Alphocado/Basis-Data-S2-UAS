<?php
  // modul/buku/daftar_buku.php
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

  // Query untuk menghitung total data
  $query_count = "SELECT COUNT(*) as total FROM buku 
          WHERE judul LIKE '%$search%' OR 
              penerbit LIKE '%$search%' OR 
              pengarang LIKE '%$search%' OR 
              isbn LIKE '%$search%'";
  $result_count = mysqli_query($koneksi, $query_count);
  $data_count = mysqli_fetch_assoc($result_count);
  $total_data = $data_count['total'];
  $total_page = ceil($total_data / $limit);

  // Query ambil data buku
  $query = "SELECT * FROM buku 
        WHERE judul LIKE '%$search%' OR 
          penerbit LIKE '%$search%' OR 
          pengarang LIKE '%$search%' OR 
          isbn LIKE '%$search%'
        LIMIT $start, $limit";
  $result = mysqli_query($koneksi, $query);

  // Render header
  renderHeader("Daftar Buku", "buku");
?>

<link rel="stylesheet" href="../../assets/css/buku.css">

<div class="page-header">
  <h1>Daftar Buku</h1>
  <div>
    <a href="tambah.php" class="btn">
      <i class="fas fa-plus"></i> Tambah Buku
    </a>
  </div>
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
  <!-- Pencarian -->
  <form method="get" class="search-form custom-search-form">
    <input 
      type="text" 
      name="search" 
      placeholder="Cari buku (judul, penerbit, pengarang, ISBN)..." 
      value="<?php echo htmlspecialchars($search); ?>"
      class="search-input"
    >
    <button type="submit" class="btn search-btn">
      <i class="fas fa-search"></i>
    </button>
  </form>

  <!-- Tabel Buku -->
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>ISBN</th>
          <th>Judul</th>
          <th>Pengarang</th>
          <th>Penerbit</th>
          <th>Tahun Terbit</th>
          <th>Kategori</th>
          <th>Lokasi Rak</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = $start + 1;
        while ($buku = mysqli_fetch_assoc($result)) : 
        ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($buku['isbn']); ?></td>
            <td><?php echo htmlspecialchars($buku['judul']); ?></td>
            <td><?php echo htmlspecialchars($buku['pengarang']); ?></td>
            <td><?php echo htmlspecialchars($buku['penerbit']); ?></td>
            <td><?php echo htmlspecialchars($buku['tahun_terbit']); ?></td>
            <td><?php echo htmlspecialchars($buku['kategori']); ?></td>
            <td><?php echo htmlspecialchars($buku['lokasi_rak']); ?></td>
            <td>
              <a href="edit.php?id=<?php echo $buku['id_buku']; ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i>
              </a>
              <a href="hapus.php?id=<?php echo $buku['id_buku']; ?>" 
                class="btn btn-danger" 
                onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination custom-pagination">
    <?php if($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-edit2x">
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