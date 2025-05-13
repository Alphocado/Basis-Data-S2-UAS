<?php
// modul/anggota/tambah.php
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/layout.php';

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
}

// Proses tambah anggota
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan input
    $nama           = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, $_POST['username']) : '';
$email          = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : '';
$password_raw   = isset($_POST['password']) ? $_POST['password'] : '';
$no_hp          = isset($_POST['no_hp']) ? mysqli_real_escape_string($koneksi, $_POST['no_hp']) : '';
$jenis_kelamin  = isset($_POST['jenis_kelamin']) ? mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']) : '';
$alamat         = isset($_POST['alamat']) ? mysqli_real_escape_string($koneksi, $_POST['alamat']) : '';
$kelas          = isset($_POST['kelas']) ? mysqli_real_escape_string($koneksi, $_POST['kelas']) : '';
$tanggal_daftar = date('Y-m-d');

    // Validasi input
    $errors = [];
    if (empty($nama)) $errors[] = "Nama harus diisi";
    if (empty($email)) $errors[] = "Email harus diisi";
    if (empty($_POST['password'])) $errors[] = "Password harus diisi";
    if (strlen($_POST['password']) < 8) $errors[] = "Password minimal 8 karakter";

    // Cek email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $errors[] = "Email sudah terdaftar";
    }

    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        $query = "INSERT INTO anggota (
            username, email, password, no_hp, jenis_kelamin, 
            alamat, kelas, tanggal_daftar
        ) VALUES (
            '$nama', '$email', '$password', '$no_hp', '$jenis_kelamin', 
            '$alamat', '$kelas', '$tanggal_daftar'
        )";

        if (mysqli_query($koneksi, $query)) {
            // Redirect dengan pesan sukses
            $_SESSION['success'] = "Anggota berhasil ditambahkan";
            redirect('daftar.php');
        } else {
            $errors[] = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    }
}

// Render header
renderHeader("Tambah Anggota", "anggota");
?>

<div class="page-header">
    <h1>Tambah Anggota Baru</h1>
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
            <label for="username" style="display: block; margin-bottom: 5px;">Nama Lengkap</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                required 
                value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px;">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password" style="display: block; margin-bottom: 5px;">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                minlength="8"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="no_hp" style="display: block; margin-bottom: 5px;">Nomor HP</label>
            <input 
                type="tel" 
                id="no_hp" 
                name="no_hp" 
                required 
                value="<?php echo isset($no_hp) ? htmlspecialchars($no_hp) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="jenis_kelamin" style="display: block; margin-bottom: 5px;">Jenis Kelamin</label>
            <select 
                id="jenis_kelamin" 
                name="jenis_kelamin" 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L" <?php echo (isset($jenis_kelamin) && $jenis_kelamin == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?php echo (isset($jenis_kelamin) && $jenis_kelamin == 'P') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="alamat" style="display: block; margin-bottom: 5px;">Alamat</label>
            <textarea 
                id="alamat" 
                name="alamat" 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;"
            ><?php echo isset($alamat) ? htmlspecialchars($alamat) : ''; ?></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="kelas" style="display: block; margin-bottom: 5px;">Kelas</label>
            <input 
                type="text" 
                id="kelas" 
                name="kelas" 
                required 
                value="<?php echo isset($kelas) ? htmlspecialchars($kelas) : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
            >
        </div>

        <div>
            <button type="submit" class="btn" style="width: 100%; padding: 15px;">
                Tambah Anggota
            </button>
        </div>
    </form>
</div>

<?php
// Render footer
renderFooter();
?>