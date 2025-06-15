<?php
  // admin/index.php
  session_start();
  require_once '../../config/koneksi.php';
  require_once '../../includes/layout.php';
  
  // Cek apakah sudah login sebagai admin
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
  }

  function safeRunQuery($koneksi, $query) {
    $result = mysqli_query($koneksi, $query);
    if (!$result) {
      // Tangani error query
      error_log("Query Error: " . mysqli_error($koneksi));
      return null;
    }
    return $result;
  }

  // Ambil statistik dengan nama tabel yang benar
  $query_anggota      = "SELECT COUNT(*) as total_anggota FROM anggota";
  $query_buku         = "SELECT COUNT(*) as total_buku FROM buku";
  $query_peminjaman   = "SELECT COUNT(*) as total_peminjaman FROM peminjaman WHERE status = 'Dipinjam'";
  $query_pengembalian = "SELECT COUNT(*) as total_pengembalian FROM pengembalian";

  $result_anggota       = runQuery($koneksi, $query_anggota);
  $result_buku          = runQuery($koneksi, $query_buku);
  $result_peminjaman    = runQuery($koneksi, $query_peminjaman);
  $result_pengembalian  = runQuery($koneksi, $query_pengembalian);

  $total_anggota      = mysqli_fetch_assoc($result_anggota)['total_anggota'];
  $total_buku         = mysqli_fetch_assoc($result_buku)['total_buku'];
  $total_peminjaman   = mysqli_fetch_assoc($result_peminjaman)['total_peminjaman'];
  $total_pengembalian = mysqli_fetch_assoc($result_pengembalian)['total_pengembalian'];

  
  // Render header
  renderHeader("Dashboard Admin", "dashboard");
?>
<link rel="stylesheet" href="../../assets/css/dashboard.css">

<h1 class="page-title">Dashboard Admin</h1>
<div class="dashboard">
  <div class="dashboard-card">
    <h3>Total Anggota</h3>
    <div class="card-value"><?php echo $total_anggota; ?></div>
  </div>
  <div class="dashboard-card">
    <h3>Total Buku</h3>
    <div class="card-value"><?php echo $total_buku; ?></div>
  </div>
  <div class="dashboard-card">
    <h3>Buku Dipinjam</h3>
    <div class="card-value"><?php echo $total_peminjaman; ?></div>
  </div>
  <div class="dashboard-card">
    <h3>Buku Dikembalikan</h3>
    <div class="card-value"><?php echo $total_pengembalian; ?></div>
  </div>
</div>

<div class="recent-loans">
  <h2 class="page-title">Peminjaman Terakhir</h2>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Query peminjaman terakhir dengan nama tabel yang benar
      $query_last_pinjam = "SELECT p.*, a.username AS nama_anggota, b.judul AS judul_buku 
                  FROM peminjaman p
                  JOIN anggota a ON p.id_anggota = a.id_anggota
                  JOIN buku b ON p.id_buku = b.id_buku
                  ORDER BY p.tanggal_pinjam DESC
                  LIMIT 5";
      $result_last_pinjam = runQuery($koneksi, $query_last_pinjam);
      
      
      $no = 1;
      while ($pinjam = mysqli_fetch_assoc($result_last_pinjam)) {
        
        $status_class = '';
        switch ($pinjam['status']) {
          case 'Dipinjam':
            $status_class = 'bg-warning'; // Kuning
            break;
          case 'Dikembalikan':
            $status_class = 'bg-success'; // Hijau
            break;
          case 'Ditolak':
            $status_class = 'bg-danger'; // Merah
            break;
          default:
            $status_class = 'bg-secondary'; // Abu-abu
            break;
        }

        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($pinjam['nama_anggota']) . "</td>";
        echo "<td>" . htmlspecialchars($pinjam['judul_buku']) . "</td>";
        echo "<td>" . $pinjam['tanggal_pinjam'] . "</td>";
        echo "<td><span class='badge " . $status_class . "'>" . htmlspecialchars($pinjam['status']) . "</span></td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>
<?php
// Render footer
renderFooter();
?>