<?php
// config/database.php
// Konfigurasi Database untuk Putra Tunggal Audio

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'putra_tunggal_audio');

// Membuat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk query dengan error handling
function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return $result;
}

// Fungsi untuk escape string (mencegah SQL injection)
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}

// Fungsi untuk fetch data sebagai array associative
function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk fetch semua data
function fetch_all($result) {
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan jumlah rows
function num_rows($result) {
    return mysqli_num_rows($result);
}

// Fungsi untuk mendapatkan ID terakhir yang di-insert
function insert_id() {
    global $conn;
    return mysqli_insert_id($conn);
}

?>