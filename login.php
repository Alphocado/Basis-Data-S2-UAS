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
            redirect('admin/dashboard.php');
        } else {
            redirect('anggota/dashboard.php');
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
            <div style="text-align: center; margin-top: 15px;">
                <p>Belum punya akun? <a href="register.php" style="color: #764ba2; text-decoration: none;">Daftar di sini</a></p>
            </div>  
        </form>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan</p>
        </div>
    </div>
</div>
<style>
    body, html {
        height: 100%;
        margin: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Arial', sans-serif;
    }

    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        padding: 20px;
    }

    .login-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
        padding: 40px;
        animation: fadeIn 0.5s ease-in-out;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header h1 {
        color: #333;
        margin-bottom: 10px;
    }

    .login-header p {
        color: #777;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .input-icon input, 
    .input-icon select {
        width: 100%;
        padding: 15px 15px 15px 45px;
        border: 1px solid #e1e1e1;
        border-radius: 8px;
        font-size: 16px;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #999;
    }

    .login-button {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: transform 0.2s;
    }

    .login-button:hover {
        transform: scale(1.02);
    }

    .login-button i {
        margin-left: 10px;
    }

    .login-footer {
        text-align: center;
        margin-top: 20px;
        color: #777;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
function togglePassword(icon) {
    const passwordInput = icon.closest('.input-icon').querySelector('input');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>

<?php
// Render footer
renderFooter();
?>