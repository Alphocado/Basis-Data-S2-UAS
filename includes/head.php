<?php
// includes/head.php

// Cek dan definisikan fungsi hanya jika belum ada
if (!function_exists('renderHead')) {
    function renderHead($title = "Perpustakaan") {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($title) . '</title>
            
            <!-- Font Awesome untuk icon -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
            
            <!-- CSS Utama -->
            <link rel="stylesheet" href="' . getRelativePath() . 'assets/css/style.css">
            
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>';
    }
}

if (!function_exists('renderFooter')) {
    function renderFooter() {
        echo '
            <!-- jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            
            <!-- Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            
            <!-- Script Utama -->
            <script src="' . getRelativePath() . 'assets/js/script.js"></script>
        </body>
        </html>';
    }
}

if (!function_exists('getRelativePath')) {
    function getRelativePath() {
        // Fungsi untuk mendapatkan path relatif
        $current_file = $_SERVER['PHP_SELF'];
        $path_parts = pathinfo($current_file);
        $directory = $path_parts['dirname'];
        
        // Hitung kedalaman direktori
        $depth = substr_count($directory, '/');
        
        // Buat path relatif
        return str_repeat('../', max(0, $depth)) . '';
    }
}

// Hapus fungsi tampilkanPesan dari sini karena sudah ada di koneksi.php
?>