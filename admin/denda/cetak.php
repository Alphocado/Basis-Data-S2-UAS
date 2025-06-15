<?php
// modul/denda/cetak.php
session_start();
require_once '../../config/koneksi.php';
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
  header('Location: ../../login.php');
  exit();
}
$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');
$status    = $_GET['status']    ?? '';

$query = "
  SELECT 
    d.id_denda, a.username, a.no_hp, b.judul, p.tanggal_kembali,
    d.jumlah_denda, d.nominal_denda, d.tanggal_bayar, d.status_pembayaran
  FROM denda d
  JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
  JOIN peminjaman   p  ON pg.id_peminjaman    = p.id_peminjaman
  JOIN anggota      a  ON p.id_anggota        = a.id_anggota
  JOIN buku         b  ON p.id_buku           = b.id_buku
  WHERE DATE(d.tanggal_bayar) BETWEEN '$tgl_awal' AND '$tgl_akhir'
";
if ($status !== '') {
  $query .= " AND d.status_pembayaran = '$status'";
}
$query .= " ORDER BY d.tanggal_bayar ASC";

$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Denda Perpustakaan</title>
  <link rel="stylesheet" href="../../assets/css/print.css">
</head>
<body>
  <div class="header">
    <h1>Laporan Denda Perpustakaan</h1>
    <div class="info">
      <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> — <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
      <?php if ($status!==''): ?><p>Status: <?= htmlspecialchars($status) ?></p><?php endif; ?>
      <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th><th>Username</th><th>No HP</th><th>Judul Buku</th>
        <th>Batas Kembali</th><th>Hari Terlambat</th><th>Nominal Denda</th>
        <th>Tanggal Bayar</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      if (mysqli_num_rows($result) > 0):
        while ($r = mysqli_fetch_assoc($result)):
          $hari = (int)$r['jumlah_denda'];
          $cls  = 'status-' . $r['status_pembayaran'];
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['username']) ?></td>
        <td><?= htmlspecialchars($r['no_hp']) ?></td>
        <td><?= htmlspecialchars($r['judul']) ?></td>
        <td><?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?></td>
        <td><?= $hari ?> hari</td>
        <td>Rp <?= number_format($r['nominal_denda'],0,',','.') ?></td>
        <td><?= $r['tanggal_bayar'] ? date('d/m/Y',strtotime($r['tanggal_bayar'])) : '-' ?></td>
        <td class="<?= $cls ?>"><?= htmlspecialchars($r['status_pembayaran']) ?></td>
      </tr>
      <?php
        endwhile;
      else:
      ?>
      <tr>
        <td colspan="9" style="text-align:center">Tidak ada data denda</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <script>window.onload = ()=>window.print();</script>
</body>
</html>
