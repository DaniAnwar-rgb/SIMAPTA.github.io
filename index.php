<?php
// index.php (di root folder)
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /putra_tunggal_audio/modules/dashboard/index.php');
} else {
    // Jika belum login, redirect ke login
    header('Location: /putra_tunggal_audio/modules/auth/login.php');
}
exit;
?>