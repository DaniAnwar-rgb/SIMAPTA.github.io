<?php
// modules/transaksi/update_status.php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: /putra_tunggal_audio/modules/auth/login.php');
    exit;
}

$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? escape($_GET['status']) : '';

if ($id_transaksi > 0 && in_array($status, ['Proses', 'Selesai', 'Dibatalkan'])) {
    // Update status transaksi
    $result = query("UPDATE transaksi_sewa SET status_transaksi = '$status' WHERE id_transaksi = $id_transaksi");
    
    if ($result) {
        // Jika status Selesai atau Dibatalkan, kembalikan status aset ke Aktif
        if ($status == 'Selesai' || $status == 'Dibatalkan') {
            $trans = fetch_assoc(query("SELECT id_aset FROM transaksi_sewa WHERE id_transaksi = $id_transaksi"));
            query("UPDATE aset SET status = 'Aktif' WHERE id_aset = " . $trans['id_aset']);
        }
        
        $_SESSION['success_message'] = 'Status transaksi berhasil diupdate!';
    } else {
        $_SESSION['error_message'] = 'Gagal mengupdate status!';
    }
} else {
    $_SESSION['error_message'] = 'Parameter tidak valid!';
}

header('Location: /putra_tunggal_audio/modules/transaksi/index.php');
exit;
?>