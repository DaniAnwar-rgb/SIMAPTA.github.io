<?php
// modules/transaksi/detail.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'transaksi';
$page_title = 'Detail Transaksi';
$page_icon = '👁️';

$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get transaksi detail (ambil semua aset dalam 1 transaksi)
$sql = "SELECT 
            ts.*,
            a.nama_aset,
            a.nomor_seri,
            k.nama_kategori
        FROM transaksi_sewa ts
        JOIN aset a ON ts.id_aset = a.id_aset
        JOIN kategori_aset k ON a.id_kategori = k.id_kategori
        WHERE ts.id_transaksi = $id_transaksi";

$result = query($sql);

if (num_rows($result) == 0) {
    $_SESSION['error_message'] = 'Transaksi tidak ditemukan!';
    header('Location: index.php');
    exit;
}

// Ambil data pertama untuk info umum
$transaksi = fetch_assoc($result);

// Hitung durasi
$durasi = hitungHari($transaksi['tanggal_transaksi'], $transaksi['tanggal_selesai']);

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .receipt-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 2px dashed #e2e8f0;
        margin-bottom: 25px;
    }
    
    .receipt-header h2 {
        font-size: 28px;
        color: var(--primary);
        margin-bottom: 5px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .info-label {
        font-weight: 600;
        color: #64748b;
    }
    
    .info-value {
        color: #1e293b;
    }
    
    .aset-list {
        background: #f8fafc;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
    }
    
    .aset-item-detail {
        padding: 15px;
        background: white;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 4px solid var(--primary);
    }
</style>

<div class="detail-card" id="printArea">
    <div class="receipt-header">
        <div style="font-size: 48px; margin-bottom: 10px;">🎵</div>
        <h2>Putra Tunggal Audio</h2>
        <p style="color: #64748b;">Bukti Transaksi</p>
    </div>
    
    <div class="info-grid">
        <div class="info-label">ID Transaksi:</div>
        <div class="info-value"><strong><?php echo $transaksi['id_transaksi']; ?></strong></div>
        
        <div class="info-label">Nama Penyewa:</div>
        <div class="info-value"><strong><?php echo $transaksi['nama_penyewa'] ?: '-'; ?></strong></div>
        
        <div class="info-label">Tipe:</div>
        <div class="info-value">
            <span class="badge badge-info"><?php echo $transaksi['tipe_transaksi']; ?></span>
        </div>
        
        <div class="info-label">Tanggal Mulai:</div>
        <div class="info-value"><?php echo formatTanggal($transaksi['tanggal_transaksi']); ?></div>
        
        <div class="info-label">Tanggal Selesai:</div>
        <div class="info-value"><?php echo formatTanggal($transaksi['tanggal_selesai']); ?></div>
        
        <div class="info-label">Durasi:</div>
        <div class="info-value"><strong><?php echo $durasi; ?> hari</strong></div>
        
        <div class="info-label">Lokasi Event:</div>
        <div class="info-value"><?php echo $transaksi['lokasi_baru']; ?></div>
        
        <div class="info-label">Total Biaya:</div>
        <div class="info-value" style="color: #10b981; font-weight: bold; font-size: 18px;">
            <?php echo formatRupiah($transaksi['total_biaya']); ?>
        </div>
        
        <div class="info-label">DP / Uang Muka:</div>
        <div class="info-value" style="color: #f59e0b; font-weight: bold;">
            <?php echo formatRupiah($transaksi['dp_uang_muka']); ?>
        </div>
        
        <div class="info-label">Sisa Pembayaran:</div>
        <div class="info-value" style="color: #ef4444; font-weight: bold; font-size: 18px;">
            <?php echo formatRupiah($transaksi['total_biaya'] - $transaksi['dp_uang_muka']); ?>
        </div>
        
        <div class="info-label">Status:</div>
        <div class="info-value">
            <span class="badge badge-<?php echo getStatusColor($transaksi['status_transaksi']); ?>">
                <?php echo $transaksi['status_transaksi']; ?>
            </span>
        </div>
    </div>
    
    <?php if ($transaksi['keterangan']): ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <strong>📝 Keterangan:</strong><br>
            <?php echo nl2br($transaksi['keterangan']); ?>
        </div>
    <?php endif; ?>
    
    <div class="aset-list">
        <h3 style="margin-bottom: 15px;">📦 Detail Aset (1 item)</h3>
        
        <div class="aset-item-detail">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="font-size: 16px; margin-bottom: 5px;"><?php echo $transaksi['nama_aset']; ?></h4>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">
                        No. Seri: <?php echo $transaksi['nomor_seri']; ?><br>
                        Kategori: <?php echo $transaksi['nama_kategori']; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div style="border-top: 2px dashed #e2e8f0; padding-top: 20px; text-align: center; color: #64748b; font-size: 13px;">
        <p>Terima kasih telah menggunakan layanan kami!</p>
        <p style="margin: 0;">Cerme - Gresik</p>
    </div>
</div>

<div style="display: flex; gap: 10px; margin-top: 20px;">
    <button onclick="window.print()" class="btn btn-primary">
        🖨️ Cetak Struk
    </button>
    <a href="index.php" class="btn" style="background: #e2e8f0; color: #334155;">
        ← Kembali
    </a>
</div>

<style media="print">
    .sidebar, .topbar, .btn, .pagination {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
    
    .content {
        padding: 0 !important;
    }
    
    .detail-card {
        box-shadow: none !important;
    }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>