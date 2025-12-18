<?php
// modules/transaksi/hapus.php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: /putra_tunggal_audio/modules/auth/login.php');
    exit;
}

$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_transaksi > 0) {
    // Ambil data transaksi untuk kembalikan status aset
    $trans = fetch_assoc(query("SELECT id_aset, status_transaksi FROM transaksi_sewa WHERE id_transaksi = $id_transaksi"));
    
    // Hapus transaksi
    $result = query("DELETE FROM transaksi_sewa WHERE id_transaksi = $id_transaksi");
    
    if ($result) {
        // Jika transaksi aktif, kembalikan status aset
        if ($trans && $trans['status_transaksi'] == 'Proses') {
            query("UPDATE aset SET status = 'Aktif' WHERE id_aset = " . $trans['id_aset']);
        }
        
        $_SESSION['success_message'] = 'Transaksi berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus transaksi!';
    }
} else {
    $_SESSION['error_message'] = 'ID transaksi tidak valid!';
}

header('Location: /putra_tunggal_audio/modules/transaksi/index.php');
exit;
?>