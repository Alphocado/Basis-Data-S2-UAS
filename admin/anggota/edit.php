<?php
  // modul/anggota/edit.php
  session_start();
  require_once '../../config/koneksi.php';
  require_once '../../includes/layout.php';

  // Cek apakah sudah login sebagai admin
  if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    redirect('../../login.php');
  }

  // Cek apakah ada ID anggota yang akan diedit
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('daftar.php');
  }

  $id_anggota = mysqli_real_escape_string($koneksi, $_GET['id']);

  // Ambil data anggota yang akan diedit
  $query      = "SELECT * FROM anggota WHERE id_anggota = '$id_anggota'";
  $result     = mysqli_query($koneksi, $query);

  if (mysqli_num_rows($result) == 0) {
    redirect('daftar.php');
  }

  $anggota    = mysqli_fetch_assoc($result);

  // Proses edit anggota
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan input
    $username       = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email          = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp          = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $jenis_kelamin  = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $kelas          = mysqli_real_escape_string($koneksi, $_POST['kelas']);

    // Validasi input
    $errors = [];
    if (empty($username)) $errors[] = "Nama harus diisi";
    if (empty($email)) $errors[]    = "Email harus diisi";

    // Cek email sudah terdaftar (kecuali email milik anggota saat ini)
    $cek_email = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email = '$email' AND id_anggota != '$id_anggota'");
    if (mysqli_num_rows($cek_email) > 0) {
      $errors[] = "Email sudah terdaftar";
    }

    // Proses password jika diisi
    $password_update = '';
    if (!empty($_POST['password'])) {
      if (strlen($_POST['password']) < 8) {
        $errors[] = "Password minimal 8 karakter";
      } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password = '$password'";
      }
    }

    // Jika tidak ada error, update data
    if (empty($errors)) {
      $query = "UPDATE anggota SET 
            username          = '$username', 
            email             = '$email', 
            no_hp             = '$no_hp', 
            jenis_kelamin     = '$jenis_kelamin', 
            alamat            = '$alamat', 
            kelas             = '$kelas'
            $password_update
            WHERE id_anggota  = '$id_anggota'";

      if (mysqli_query($koneksi, $query)) {
        // Redirect dengan pesan sukses
        $_SESSION['success'] = "Data anggota berhasil diupdate";
        redirect('daftar.php');
      } else {
        $errors[] = "Gagal mengupdate data: " . mysqli_error($koneksi);
      }
    }
  }

  // Render header
  renderHeader("Edit Anggota", "anggota");
?>

<link rel="stylesheet" href="../../assets/css/anggota.css">

<div class="page-header">
  <h1>Edit Anggota</h1>
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
      <label for="username">Nama Lengkap</label>
      <input 
        type="text" 
        id="username" 
        name="username" 
        required 
        value="<?php echo htmlspecialchars($anggota['username']); ?>"
        class="form-control"
      >
    </div>

    <div class>
      <label for="email">Email</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        required 
        value="<?php echo htmlspecialchars($anggota['email']); ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="password">Password (Kosongkan jika tidak ingin mengubah)</label>
      <input 
        type="password" 
        id="password" 
        name="password" 
        minlength="8"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="no_hp">Nomor HP</label>
      <input 
        type="tel" 
        id="no_hp" 
        name="no_hp" 
        required 
        value="<?php echo htmlspecialchars($anggota['no_hp']); ?>"
        class="form-control"
      >
    </div>

    <div class="form-group">
      <label for="jenis_kelamin">Jenis Kelamin</label>
      <select 
        id="jenis_kelamin" 
        name="jenis_kelamin" 
        required
        class="form-control"
      >
        <option value="">Pilih Jenis Kelamin</option>
        <option value="L" <?php echo ($anggota['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
        <option value="P" <?php echo ($anggota['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
      </select>
    </div>

    <div class="form-group">
      <label for="alamat">Alamat</label>
      <textarea 
        id="alamat" 
        name="alamat" 
        required
        class="form-control textarea-control"
      ><?php echo htmlspecialchars($anggota['alamat']); ?></textarea>
    </div>

    <div class="form-group">
      <label for="kelas">Kelas</label>
      <input 
        type="text" 
        id="kelas" 
        name="kelas" 
        required 
        value="<?php echo htmlspecialchars($anggota['kelas']); ?>"
        class="form-control"
      >
    </div>

    <div>
      <button type="submit" class="btn btn-full">
        Update Anggota
      </button>
    </div>
  </form>
</div>

<?php
// Render footer
renderFooter();
?>