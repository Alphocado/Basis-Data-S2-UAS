<?php
// config/koneksi.php

// Konfigurasi Database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'perpustakaan';

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi untuk mencegah SQL Injection
function mysqli_escape_string_custom($koneksi, $string) {
    return mysqli_real_escape_string($koneksi, htmlspecialchars(trim($string)));
}

// Fungsi untuk menjalankan query
function runQuery($koneksi, $query) {
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        // Catat error atau tampilkan pesan error
        die("Query error: " . mysqli_error($koneksi));
    }
    
    return $result;
}

// Fungsi untuk mengamankan input
function cleanInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan
function tampilkanPesan($pesan, $tipe = 'info') {
    $warna = ($tipe == 'error') ? 'danger' : 'success';
    echo "<div class='alert alert-$warna'>$pesan</div>";
}
?>