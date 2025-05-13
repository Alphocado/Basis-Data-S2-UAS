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

<div class="page-header">
    <h1>Tambah Buku Baru</h1>
</div>

<div class="card">
    <?php 
    // Tampilkan pesan error jika ada
    if (!empty($errors)): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="" style="max-width: 600px; margin: 0 auto;">
        <div style="margin-bottom: 15px;">
            <label for="isbn" style="display: block; margin-bottom: 5px;">ISBN</label>
            <input 
                type="text" 
                id="isbn" 
                name="isbn" 
                required 
                value="<?php echo isset($isbn) ? htmlspecialchars($isbn) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="judul" style="display: block; margin-bottom: 5px;">Judul Buku</label>
            <input 
                type="text" 
                id="judul" 
                name="judul" 
                required 
                value="<?php echo isset($judul) ? htmlspecialchars($judul) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="pengarang" style="display: block; margin-bottom: 5px;">Pengarang</label>
            <input 
                type="text" 
                id="pengarang" 
                name="pengarang" 
                required 
                value="<?php echo isset($pengarang) ? htmlspecialchars($pengarang) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="penerbit" style="display: block; margin-bottom: 5px;">Penerbit</label>
            <input 
                type="text" 
                id="penerbit" 
                name="penerbit" 
                required 
                value="<?php echo isset($penerbit) ? htmlspecialchars($penerbit) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="tahun_terbit" style="display: block; margin-bottom: 5px;">Tahun Terbit</label>
            <input 
                type="number" 
                id="tahun_terbit" 
                name="tahun_terbit" 
                required 
                min="1900"
                max="<?php echo date('Y'); ?>"
                value="<?php echo isset($tahun_terbit) ? htmlspecialchars($tahun_terbit) : date('Y'); ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="lokasi_rak" style="display: block; margin-bottom: 5px;">Lokasi Rak</label>
            <input 
                type="text" 
                id="lokasi_rak" 
                name="lokasi_rak" 
                required 
                value="<?php echo isset($lokasi_rak) ? htmlspecialchars($lokasi_rak) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
        <label for="kategori" style="display: block; margin-bottom: 5px;">Kategori</label>
        <select 
            id="kategori" 
            name="kategori" 
            required
            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
        >
            <option value="">Pilih Kategori</option>
            <option value="Fiksi" <?php echo (isset($kategori) && $kategori == 'Fiksi') ? 'selected' : ''; ?>>Fiksi</option>
            <option value="Non-Fiksi" <?php echo (isset($kategori) && $kategori == 'Non-Fiksi') ? 'selected' : ''; ?>>Non-Fiksi</option>
            <option value="Referensi" <?php echo (isset($kategori) && $kategori == 'Referensi') ? 'selected' : ''; ?>>Referensi</option>
        </select>
    </div>
    
            <div style="margin-bottom: 15px;">
                <label for="deskripsi" style="display: block; margin-bottom: 5px;">Deskripsi</label>
                <textarea 
                    id="deskripsi" 
                    name="deskripsi"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 150px;"
                ><?php echo isset($deskripsi) ? htmlspecialchars($deskripsi) : ''; ?></textarea>
            </div>
    
            <div>
                <button type="submit" class="btn" style="width: 100%; padding: 15px;">
                    Tambah Buku
                </button>
            </div>
        </form>
    </div>
    
    <?php
    // Render footer
    renderFooter();
    ?>