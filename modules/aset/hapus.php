<?php
// modules/aset/hapus.php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check login
if (!isLoggedIn()) {
    header('Location: /modules/auth/login.php');
    exit;
}

$id_aset = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_aset > 0) {
    // Cek apakah aset memiliki transaksi aktif
    $check_transaksi = query("SELECT * FROM transaksi_sewa WHERE id_aset = $id_aset AND status_transaksi IN ('Proses')");
    
    if (num_rows($check_transaksi) > 0) {
        $_SESSION['error_message'] = 'Tidak dapat menghapus aset! Aset masih memiliki transaksi aktif.';
    } else {
        // Hapus maintenance terkait dulu
        query("DELETE FROM maintenance WHERE id_aset = $id_aset");
        
        // Hapus aset
        $result = query("DELETE FROM aset WHERE id_aset = $id_aset");
        
        if ($result) {
            $_SESSION['success_message'] = 'Aset berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus aset!';
        }
    }
} else {
    $_SESSION['error_message'] = 'ID aset tidak valid!';
}

header('Location: index.php');
exit;
?>