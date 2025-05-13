<?php
// register.php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/head.php';

// Cek apakah sudah login
if (isset($_SESSION['login'])) {
    if ($_SESSION['level'] == 'admin') {
        redirect('admin/index.php');
    } else {
        redirect('anggota/index.php');
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses Registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    // Bersihkan dan validasi input
    $username = cleanInput($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $no_hp = cleanInput($_POST['no_telepon']); // Gunakan no_hp
    $jenis_kelamin = isset($_POST['jenis_kelamin']) ? cleanInput($_POST['jenis_kelamin']) : '';
    $kelas = isset($_POST['kelas']) ? cleanInput($_POST['kelas']) : '';
    $alamat = isset($_POST['alamat']) ? cleanInput($_POST['alamat']) : '';

    // Validasi input
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 4 || strlen($username) > 20) {
        $errors[] = "Username harus antara 4-20 karakter";
    }

    if (!$email) {
        $errors[] = "Email tidak valid";
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }

    if ($password !== $konfirmasi_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    if (empty($no_hp)) {
        $errors[] = "Nomor HP harus diisi";
    }

    if (empty($jenis_kelamin)) {
        $errors[] = "Jenis kelamin harus dipilih";
    }

    // Cek ketersediaan username dan email
    $stmt_username = $koneksi->prepare("SELECT * FROM anggota WHERE username = ?");
    $stmt_username->bind_param("s", $username);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    if ($result_username->num_rows > 0) {
        $errors[] = "Username sudah digunakan";
    }

    $stmt_email = $koneksi->prepare("SELECT * FROM anggota WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    if ($result_email->num_rows > 0) {
        $errors[] = "Email sudah terdaftar";
    }

    // Jika tidak ada error, proses registrasi
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $koneksi->prepare("
            INSERT INTO anggota 
            (username, email, password, no_hp, jenis_kelamin, kelas, alamat, tanggal_daftar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)
        ");
        $stmt->bind_param("sssssss", 
            $username, 
            $email, 
            $hashed_password, 
            $no_hp,  // Ubah dari no_telepon ke no_hp 
            $jenis_kelamin, 
            $kelas, 
            $alamat
);

        try {
            if ($stmt->execute()) {
                // Registrasi berhasil
                $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
                redirect('login.php');
            } else {
                $errors[] = "Gagal mendaftar: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Render header
renderHead("Registrasi Anggota Perpustakaan");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Anggota Perpustakaan</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background: linear-gradient(to right, #667eea, #764ba2);
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
            font-weight: bold;
        }

        .login-header p {
            color: #777;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .input-icon input, 
        .input-icon select,
        textarea {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-button:hover {
            opacity: 0.9;
        }

        .login-button i {
            margin-left: 10px;
        }

        textarea[name="alamat"] {
            min-height: 100px;
            resize: vertical;
        }

        .error-message {
            background-color: #ffeeee;
            border: 1px solid #ff0000;
            color: #ff0000;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <h1>Sistem Perpustakaan</h1>
                <p>Registrasi Anggota</p>
            </div>
            
            <?php 
            // Tampilkan pesan error jika ada
            if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
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
                            pattern="[A-Za-z0-9_]+" 
                            title="Hanya huruf, angka, dan underscore"
                            minlength="4"
                            maxlength="20"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Email" 
                            required 
                            maxlength="100"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                            minlength="8"
                        >
                        <span class="password-toggle">
                            <i class="fas fa-eye-slash" onclick="togglePassword(this)"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            name="konfirmasi_password" 
                            placeholder="Konfirmasi Password" 
                            required 
                            minlength="8"
                        >
                        <span class="password-toggle">
                            <i class="fas fa-eye-slash" onclick="togglePassword(this)"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input 
                            type="tel" 
                            name="no_telepon" 
                            placeholder="Nomor Telepon" 
                            required 
                            pattern="[0-9]+" 
                            title="Hanya angka"
                            minlength="10"
                            maxlength="15"
                            value="<?php echo isset($_POST['no_telepon']) ? htmlspecialchars($_POST['no_telepon']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-venus-mars"></i>
                        <select name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-graduation-cap"></i>
                        <input 
                            type="text" 
                            name="kelas" 
                            placeholder="Kelas" 
                            maxlength="100"
                            value="<?php echo isset($_POST['kelas']) ? htmlspecialchars($_POST['kelas']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <textarea 
                            name="alamat" 
                            placeholder="Alamat" 
                            maxlength="255"
                        ><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="login-button">
                    <span>Daftar</span>
                    <i class="fas fa-user-plus"></i>
                </button>

                <div class="login-footer">
                    <p>Sudah punya akun? <a href="login.php" style="color: #764ba2; text-decoration: none;">Login di sini</a></p>
                </div>
            </form>
        </div>
    </div>

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
</body>
</html>