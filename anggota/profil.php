<?php
// anggota/profil.php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/layout_anggota.php';

// Cek apakah sudah login sebagai anggota
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'anggota') {
  redirect('../login.php');
}

// Ambil ID anggota dari session
$username = $_SESSION['username'];

// Ambil data anggota berdasarkan username
$query_anggota = "SELECT * FROM anggota WHERE username = '$username'";
$result_anggota = mysqli_query($koneksi, $query_anggota);

// Pastikan data anggota ditemukan
if (!$result_anggota || mysqli_num_rows($result_anggota) == 0) {
  $_SESSION['error'] = "Data anggota tidak ditemukan";
  redirect('../login.php');
}

$anggota = mysqli_fetch_assoc($result_anggota);

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validasi dan bersihkan input
  $email = mysqli_real_escape_string($koneksi, $_POST['email']);
  $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
  $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
  $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

  // Validasi input
  $errors = [];
  if (empty($email)) $errors[] = "Email tidak boleh kosong";
  if (empty($no_hp)) $errors[] = "Nomor HP tidak boleh kosong";
  if (empty($alamat)) $errors[] = "Alamat tidak boleh kosong";
  if (empty($kelas)) $errors[] = "Kelas tidak boleh kosong";

  // Proses password (opsional)
  $password_query = "";
  if (!empty($_POST['password'])) {
    if (strlen($_POST['password']) < 8) {
      $errors[] = "Password minimal 8 karakter";
    } else {
      $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $password_query = ", password = '$password'";
    }
  }

  // Jika tidak ada error, update data
  if (empty($errors)) {
    $query_update = "UPDATE anggota 
            SET email = '$email', 
              no_hp = '$no_hp', 
              alamat = '$alamat', 
              kelas = '$kelas'
              $password_query
            WHERE username = '$username'";

    if (mysqli_query($koneksi, $query_update)) {
      $_SESSION['success'] = "Profil berhasil diperbarui";
      // Refresh data
      $result_anggota = mysqli_query($koneksi, $query_anggota);
      $anggota = mysqli_fetch_assoc($result_anggota);
    } else {
      $errors[] = "Gagal memperbarui profil: " . mysqli_error($koneksi);
    }
  }
}

// Render header
renderHeader("Profil Anggota", "profil");
?>

<div class="page-header">
  <h1>Profil Anggota</h1>
</div>

<?php 
// Tampilkan pesan error atau sukses
if (!empty($errors)) {
  echo "<div class='alert alert-danger'>";
  foreach ($errors as $error) {
    echo "<p>" . htmlspecialchars($error) . "</p>";
  }
  echo "</div>";
}

if (isset($_SESSION['success'])) {
  echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
  unset($_SESSION['success']);
}
?>

<link rel="stylesheet" href="../assets/css/anggota/profil.css">
<div class="card">
  <form method="POST" action="">
    <div class="row">
      <div class="col-md-6">
        <h3>Informasi Akun</h3>
        <div class="form-group">
          <label>Username</label>
          <input 
            type="text" 
            class="form-control" 
            value="<?php echo htmlspecialchars($anggota['username']); ?>" 
            readonly
          >
        </div>

        <div class="form-group">
          <label>Email</label>
          <input 
            type="email" 
            name="email" 
            class="form-control" 
            value="<?php echo htmlspecialchars($anggota['email']); ?>" 
            required
          >
        </div>

        <div class="form-group">
          <label>Nomor HP</label>
          <input 
            type="tel" 
            name="no_hp" 
            class="form-control" 
            value="<?php echo htmlspecialchars($anggota['no_hp']); ?>" 
            required
          >
        </div>
      </div>

      <div class="col-md-6"> 
        <h3>Informasi Tambahan</h3>
        <div class="form-group">
          <label>Alamat</label>
          <textarea 
            name="alamat" 
            class="form-control" 
            rows="3" 
            required
          ><?php echo htmlspecialchars($anggota['alamat']); ?></textarea>
        </div>

        <div class="form-group">
          <label>Kelas</label>
          <input 
            type="text" 
            name="kelas" 
            class="form-control" 
            value="<?php echo htmlspecialchars($anggota['kelas']); ?>" 
            required
          >
        </div>

        <div class="form-group">
          <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
          <input 
            type="password" 
            name="password" 
            class="form-control" 
            minlength="8"
          >
        </div>
      </div>
    </div>

    <div class="form-group mt-3">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Perbarui Profil
      </button>
    </div>
  </form>
</div>



<?php
  // Render footer
  renderFooter();
?>