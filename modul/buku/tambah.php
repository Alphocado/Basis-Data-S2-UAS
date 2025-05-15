<?php
  // modul/buku/tambah.php
  session_start();
  require_once '../../config/koneksi.php';
  require_once '../../includes/layout.php';

  // Cek apakah sudah login sebagai admin
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
  }

  // Proses tambah buku
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan input
    $isbn = mysqli_real_escape_string($koneksi, $_POST['isbn']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($koneksi, $_POST['pengarang']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
    $lokasi_rak = mysqli_real_escape_string($koneksi, $_POST['lokasi_rak']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    // Validasi input
    $errors = [];
    if (empty($isbn)) $errors[] = "ISBN harus diisi";
    if (empty($judul)) $errors[] = "Judul buku harus diisi";
    if (empty($pengarang)) $errors[] = "Pengarang harus diisi";
    if (empty($penerbit)) $errors[] = "Penerbit harus diisi";
    if (empty($tahun_terbit)) $errors[] = "Tahun terbit harus diisi";
    if (empty($lokasi_rak)) $errors[] = "Lokasi rak harus diisi";

    // Cek ISBN sudah terdaftar
    $cek_isbn = mysqli_query($koneksi, "SELECT * FROM buku WHERE isbn = '$isbn'");
    if (mysqli_num_rows($cek_isbn) > 0) {
      $errors[] = "ISBN sudah terdaftar";
    }

    // Jika tidak ada error, simpan data
    if (empty($errors)) {
      $query = "INSERT INTO buku (
        isbn, judul, pengarang, penerbit, 
        tahun_terbit, lokasi_rak, kategori, deskripsi
      ) VALUES (
        '$isbn', '$judul', '$pengarang', '$penerbit', 
        '$tahun_terbit', '$lokasi_rak', '$kategori', '$deskripsi'
      )";

      if (mysqli_query($koneksi, $query)) {
        // Redirect dengan pesan sukses
        $_SESSION['success'] = "Buku berhasil ditambahkan";
        redirect('daftar_buku.php');
      } else {
        $errors[] = "Gagal menyimpan data: " . mysqli_error($koneksi);
      }
    }
  }

  // Render header
  renderHeader("Tambah Buku", "buku");
?>

<link rel="stylesheet" href="../../assets/css/modul/buku/tambah.css">

<div class="page-header">
  <h1>Tambah Buku Baru</h1>
</div>

<div class="card">
  <?php 
  // Tampilkan pesan error jika ada
  if (!empty($errors)): ?>
    <div class="alert-error">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="" class="form-container">
    <div class="form-group">
      <label for="isbn" class="form-label">ISBN</label>
      <input 
        type="text" 
        id="isbn" 
        name="isbn" 
        required 
        value="<?php echo isset($isbn) ? htmlspecialchars($isbn) : ''; ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="judul" class="form-label">Judul Buku</label>
      <input 
        type="text" 
        id="judul" 
        name="judul" 
        required 
        value="<?php echo isset($judul) ? htmlspecialchars($judul) : ''; ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="pengarang" class="form-label">Pengarang</label>
      <input 
        type="text" 
        id="pengarang" 
        name="pengarang" 
        required 
        value="<?php echo isset($pengarang) ? htmlspecialchars($pengarang) : ''; ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="penerbit" class="form-label">Penerbit</label>
      <input 
        type="text" 
        id="penerbit" 
        name="penerbit" 
        required 
        value="<?php echo isset($penerbit) ? htmlspecialchars($penerbit) : ''; ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
      <input 
        type="number" 
        id="tahun_terbit" 
        name="tahun_terbit" 
        required 
        min="1900"
        max="<?php echo date('Y'); ?>"
        value="<?php echo isset($tahun_terbit) ? htmlspecialchars($tahun_terbit) : date('Y'); ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="lokasi_rak" class="form-label">Lokasi Rak</label>
      <input 
        type="text" 
        id="lokasi_rak" 
        name="lokasi_rak" 
        required 
        value="<?php echo isset($lokasi_rak) ? htmlspecialchars($lokasi_rak) : ''; ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
    <label for="kategori" class="form-label">Kategori</label>
    <select 
      id="kategori" 
      name="kategori" 
      required
      class="form-control"
    >
      <option value="">Pilih Kategori</option>
      <option value="Fiksi" <?php echo (isset($kategori) && $kategori == 'Fiksi') ? 'selected' : ''; ?>>Fiksi</option>
      <option value="Non-Fiksi" <?php echo (isset($kategori) && $kategori == 'Non-Fiksi') ? 'selected' : ''; ?>>Non-Fiksi</option>
      <option value="Referensi" <?php echo (isset($kategori) && $kategori == 'Referensi') ? 'selected' : ''; ?>>Referensi</option>
    </select>
  </div>
  
      <div class="form-group">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea 
          id="deskripsi" 
          name="deskripsi"
          class="form-control"
        ><?php echo isset($deskripsi) ? htmlspecialchars($deskripsi) : ''; ?></textarea>
      </div>
  
      <div>
        <button type="submit" class="btn form-submit">
          Tambah Buku
        </button>
      </div>
    </form>
  </div>
  
  <?php
  // Render footer
  renderFooter();
  ?>