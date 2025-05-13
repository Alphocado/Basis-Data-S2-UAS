<?php
/**
 * File: logout.php
 * Berisi proses logout untuk menghapus session dan mengarahkan ke halaman login
 */
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Hapus session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit();
?>