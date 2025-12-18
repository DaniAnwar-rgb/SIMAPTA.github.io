<?php
// modules/dashboard/index.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

// Set page info
$current_page = 'dashboard';
$page_title = 'Dashboard';
$page_icon = '📊';

// Get Statistics
$total_aset = num_rows(query("SELECT * FROM aset"));
$aset_aktif = num_rows(query("SELECT * FROM aset WHERE status = 'Aktif'"));
$total_maintenance = num_rows(query("SELECT * FROM maintenance WHERE status_maintenance != 'Selesai'"));
$total_transaksi = num_rows(query("SELECT * FROM transaksi_sewa WHERE status_transaksi = 'Proses'"));

// Get recent transactions
$recent_transactions = query("
    SELECT ts.*, a.nama_aset 
    FROM transaksi_sewa ts 
    JOIN aset a ON ts.id_aset = a.id_aset 
    ORDER BY ts.created_at DESC 
    LIMIT 5
");

// Get maintenance schedule
$maintenance_schedule = query("
    SELECT m.*, a.nama_aset 
    FROM maintenance m 
    JOIN aset a ON m.id_aset = a.id_aset 
    WHERE m.status_maintenance != 'Selesai'
    ORDER BY m.tanggal_maintenance ASC 
    LIMIT 5
");

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border-radius: 15px;
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-card.green {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .stat-card.orange {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .stat-card.purple {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    
    .stat-icon {
        font-size: 48px;
        opacity: 0.9;
    }
    
    .stat-info h3 {
        font-size: 14px;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: 700;
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 968px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .activity-item {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 5px;
    }
    
    .activity-info p {
        font-size: 13px;
        color: #64748b;
    }
</style>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <h3>Total Aset</h3>
            <div class="stat-value"><?php echo $total_aset; ?></div>
        </div>
    </div>
    
    <div class="stat-card green">
        <div class="stat-icon">✓</div>
        <div class="stat-info">
            <h3>Aset Aktif</h3>
            <div class="stat-value"><?php echo $aset_aktif; ?></div>
        </div>
    </div>
    
    <div class="stat-card purple">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <h3>Transaksi</h3>
            <div class="stat-value"><?php echo $total_transaksi; ?></div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="dashboard-grid">
    <!-- Recent Transactions -->
    <div class="card">
        <h2 class="card-title">📋 Transaksi Terbaru</h2>
        
        <?php if (num_rows($recent_transactions) > 0): ?>
            <?php while($trans = fetch_assoc($recent_transactions)): ?>
                <div class="activity-item">
                    <div class="activity-info">
                        <h4><?php echo $trans['nama_aset']; ?></h4>
                        <p>
                            <?php echo $trans['nama_penyewa'] ?: 'No Name'; ?> - 
                            <?php echo formatTanggalPendek($trans['tanggal_transaksi']); ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo getStatusColor($trans['status_transaksi']); ?>">
                        <?php echo $trans['status_transaksi']; ?>
                    </span>
                </div>
            <?php endwhile; ?>
            
            <a href="/putra_tunggal_audio/modules/transaksi/index.php" class="btn btn-primary" style="margin-top: 15px;">
                Lihat Semua →
            </a>
        <?php else: ?>
            <p style="color: #64748b;">Belum ada transaksi</p>
        <?php endif; ?>
    </div>
    
    <!-- Maintenance Schedule -->
    <div class="card">
        <h2 class="card-title">🔧 Jadwal Maintenance</h2>
        
        <?php if (num_rows($maintenance_schedule) > 0): ?>
            <?php while($maint = fetch_assoc($maintenance_schedule)): ?>
                <div class="activity-item">
                    <div class="activity-info">
                        <h4><?php echo $maint['nama_aset']; ?></h4>
                        <p>
                            <?php echo $maint['tipe_maintenance']; ?> - 
                            <?php echo formatTanggalPendek($maint['tanggal_maintenance']); ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo getStatusColor($maint['status_maintenance']); ?>">
                        <?php echo $maint['status_maintenance']; ?>
                    </span>
                </div>
            <?php endwhile; ?>
            
            <a href="/putra_tunggal_audio/modules/maintenance/index.php" class="btn btn-primary" style="margin-top: 15px;">
                Lihat Semua →
            </a>
        <?php else: ?>
            <p style="color: #64748b;">Tidak ada maintenance terjadwal</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>