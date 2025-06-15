<?php
  // login.php
  session_start();
  require_once 'config/koneksi.php';
  require_once 'includes/head.php';

  // Prevent session fixation
  if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
  }

  // Cek apakah sudah login
  if (isset($_SESSION['login'])) {
    if ($_SESSION['level'] == 'admin') {
      redirect('admin/index.php');
    } else {
      redirect('anggota/index.php');
    }
  }

  // Proses login
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Implement CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die('CSRF token validation failed');
    }

    // Bersihkan input dengan prepared statements
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    $level = cleanInput($_POST['level']);

    // Validasi input
    if (empty($username) || empty($password) || empty($level)) {
      tampilkanPesan('Semua field harus diisi!', 'error');
      exit;
    }

    // Gunakan prepared statement untuk mencegah SQL Injection
    if ($level == 'admin') {
      $stmt = $koneksi->prepare("SELECT id_admin, username, password FROM admin WHERE username = ? LIMIT 1");
    } else {
      $stmt = $koneksi->prepare("SELECT id_anggota, username, password FROM anggota WHERE username = ? LIMIT 1");
    }

    // Bind parameter dan eksekusi
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verifikasi password dengan waktu konstan
    if ($user && password_verify($password, $user['password'])) {
      // Hapus session lama sebelum membuat session baru (pencegahan session fixation)
      session_unset();
      session_destroy();
      session_start();
      session_regenerate_id(true);

      // Set session baru
      $_SESSION['login'] = true;
      $_SESSION['id'] = $level == 'admin' ? $user['id_admin'] : $user['id_anggota'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['level'] = $level;
      
      // Tambahkan timestamp login
      $_SESSION['login_time'] = time();

      // Redirect sesuai level
      if ($level == 'admin') {
        redirect('admin/dashboard');
      } else {
        redirect('anggota/dashboard/');
      }
    } else {
      // Login gagal
      // Tambahkan mekanisme rate limiting
      if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 1;
        $_SESSION['last_attempt_time'] = time();
      } else {
        // Jika sudah 3 percobaan dalam 5 menit, blokir
        if ($_SESSION['login_attempts'] >= 3 && 
          (time() - $_SESSION['last_attempt_time']) < 300) {
          tampilkanPesan('Terlalu banyak percobaan login. Silakan coba lagi dalam 5 menit.', 'error');
          exit;
        }

        // Reset atau increment percobaan
        if ((time() - $_SESSION['last_attempt_time']) >= 300) {
          $_SESSION['login_attempts'] = 1;
          $_SESSION['last_attempt_time'] = time();
        } else {
          $_SESSION['login_attempts']++;
          $_SESSION['last_attempt_time'] = time();
        }
      }

      tampilkanPesan('Username atau password salah!', 'error');
    }

    // Tutup statement
    $stmt->close();
  }

  // Generate CSRF token
  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  // Render header
  renderHead("Login Perpustakaan");
?>
<link rel="stylesheet" href="assets/css/login.css">
<div class="login-wrapper">
  <div class="login-container">
    <div class="login-header">
      <h1>Sistem Perpustakaan</h1>
      <p>Selamat Datang Kembali</p>
    </div>
    
    <form method="POST" action="" class="login-form">
      <!-- Tambahkan CSRF token -->
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      
      <div class="form-group">
        <div class="input-icon">
          <i class="fas fa-user"></i>
          <input 
            type="text" 
            name="username" 
            placeholder="Username" 
            required 
            autocomplete="username"
            pattern="[A-Za-z0-9_]+" 
            title="Hanya huruf, angka, dan underscore"
            maxlength="50"
          >
        </div>
      </div>
      
      <div class="form-group">
        <div class="input-icon">
          <i class="fas fa-lock"></i>
          <input 
            type="password" 
            name="password" 
            placeholder="Password" 
            required 
            autocomplete="current-password"
            minlength="8"
          >
          <span class="password-toggle">
            <i class="fas fa-eye-slash" onclick="togglePassword(this)"></i>
          </span>
        </div>
      </div>
      
      <div class="form-group">
        <div class="input-icon">
          <i class="fas fa-user-tag"></i>
          <select name="level" required>
            <option value="">Pilih Level Login</option>
            <option value="admin">Admin</option>
            <option value="anggota">Anggota</option>
          </select>
        </div>
      </div>
      
      <button type="submit" class="login-button">
        <span>Login</span>
        <i class="fas fa-sign-in-alt"></i>
      </button>
      <div class="register-link">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
      </div>
    </form>
    
    <div class="login-footer">
      <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan</p>
    </div>
  </div>
</div>

<script src="assets/js/login.js"></script>
<?php
  // Render footer
  renderFooter();
?>