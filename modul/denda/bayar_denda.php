<?php
include '../../config/koneksi.php';
session_start();

$id_denda = $_GET['id'];

// Update status menjadi Lunas
mysqli_query($koneksi, "UPDATE denda SET status = 'Lunas' WHERE id_denda = '$id_denda'");

header('Location: ../../admin/dashboard.php?page=denda');
exit;
?>
