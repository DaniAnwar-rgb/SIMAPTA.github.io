<?php
// includes/functions.php
// Helper Functions untuk Putra Tunggal Audio

// Format Rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format Tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Format Tanggal Pendek (11/12/2025)
function formatTanggalPendek($tanggal) {
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . '/' . $pecahkan[1] . '/' . $pecahkan[0];
}

// Hitung Selisih Hari
function hitungHari($tanggal_mulai, $tanggal_selesai) {
    $awal = new DateTime($tanggal_mulai);
    $akhir = new DateTime($tanggal_selesai);
    $selisih = $akhir->diff($awal);
    return $selisih->days + 1; // +1 untuk include hari pertama
}

// Cek Status Login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /modules/auth/login.php');
        exit;
    }
}

// Get User Data dari Session
function getUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nama' => $_SESSION['nama'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Cek Role User
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Alert Success
function alertSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

// Alert Error
function alertError($message) {
    return '<div class="alert alert-error">' . $message . '</div>';
}

// Sanitize Input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate Nomor Seri Otomatis
function generateNomorSeri($prefix) {
    return $prefix . '-' . date('Ymd') . '-' . rand(100, 999);
}

// Cek Ketersediaan Aset
function isAsetTersedia($id_aset, $tanggal_mulai, $tanggal_selesai, $exclude_transaksi = null) {
    global $conn;
    
    $sql = "SELECT * FROM transaksi_sewa 
            WHERE id_aset = " . (int)$id_aset . "
            AND status_transaksi != 'Dibatalkan'
            AND status_transaksi != 'Selesai'
            AND (
                (tanggal_transaksi BETWEEN '$tanggal_mulai' AND '$tanggal_selesai')
                OR (tanggal_selesai BETWEEN '$tanggal_mulai' AND '$tanggal_selesai')
                OR ('$tanggal_mulai' BETWEEN tanggal_transaksi AND tanggal_selesai)
            )";
    
    // Exclude transaksi tertentu saat edit
    if ($exclude_transaksi) {
        $sql .= " AND id_transaksi != " . (int)$exclude_transaksi;
    }
    
    $result = query($sql);
    return num_rows($result) == 0;
}

// Get Status Badge Color
function getStatusColor($status) {
    $colors = [
        'Aktif' => 'success',
        'Tidak Aktif' => 'secondary',
        'Diperbaiki' => 'warning',
        'Disewa' => 'info',
        'Proses' => 'warning',
        'Selesai' => 'success',
        'Dibatalkan' => 'error'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : 'secondary';
}

// Pagination
function pagination($total_data, $data_per_halaman, $halaman_aktif, $base_url) {
    $total_halaman = ceil($total_data / $data_per_halaman);
    
    if ($total_halaman <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous
    if ($halaman_aktif > 1) {
        $html .= '<a href="' . $base_url . '?page=' . ($halaman_aktif - 1) . '" class="page-link">‹</a>';
    }
    
    // Numbers
    for ($i = 1; $i <= $total_halaman; $i++) {
        $active = ($i == $halaman_aktif) ? 'active' : '';
        $html .= '<a href="' . $base_url . '?page=' . $i . '" class="page-link ' . $active . '">' . $i . '</a>';
    }
    
    // Next
    if ($halaman_aktif < $total_halaman) {
        $html .= '<a href="' . $base_url . '?page=' . ($halaman_aktif + 1) . '" class="page-link">›</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// Debug Function (hanya untuk development)
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

?>