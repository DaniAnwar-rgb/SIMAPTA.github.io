<?php
// modules/auth/logout.php
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header('Location: /putra_tunggal_audio/modules/auth/login.php');
exit;
?>