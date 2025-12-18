<?php
// modules/auth/auth_check.php
// Middleware untuk cek autentikasi user

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database & functions
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    // Simpan URL yang dituju untuk redirect setelah login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect ke halaman login
    header('Location: /putra_tunggal_audio/modules/auth/login.php');
    exit;
}

// Refresh data user dari database (optional, untuk data terbaru)
$user_id = $_SESSION['user_id'];
$result = query("SELECT * FROM pengguna WHERE id_pengguna = $user_id");
$user_data = fetch_assoc($result);

// Jika user tidak ditemukan (mungkin sudah dihapus)
if (!$user_data) {
    session_destroy();
    header('Location: /putra_tunggal_audio/modules/auth/login.php?error=user_not_found');
    exit;
}

// Update session dengan data terbaru
$_SESSION['username'] = $user_data['username'];
$_SESSION['nama'] = $user_data['nama'];
$_SESSION['role'] = $user_data['role'];
$_SESSION['email'] = $user_data['email'];

?>