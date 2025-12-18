<?php
// modules/transaksi/index.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'transaksi';
$page_title = 'Transaksi Sewa';
$page_icon = '📋';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total
$total_query = query("SELECT COUNT(DISTINCT id_transaksi) as total FROM transaksi_sewa");
$total_data = fetch_assoc($total_query)['total'];

// Get transaksi (group by transaksi untuk multi-aset)
$sql = "SELECT 
            ts.id_transaksi,
            ts.nama_penyewa,
            ts.tipe_transaksi,
            ts.tanggal_transaksi,
            ts.tanggal_selesai,
            ts.lokasi_baru,
            ts.status_transaksi,
            ts.keterangan,
            GROUP_CONCAT(a.nama_aset SEPARATOR ', ') as aset_list,
            COUNT(DISTINCT ts.id_aset) as jumlah_aset
        FROM transaksi_sewa ts
        LEFT JOIN aset a ON ts.id_aset = a.id_aset
        GROUP BY ts.id_transaksi, ts.nama_penyewa, ts.tipe_transaksi, ts.tanggal_transaksi, 
                 ts.tanggal_selesai, ts.lokasi_baru, ts.status_transaksi, ts.keterangan
        ORDER BY ts.created_at DESC
        LIMIT $limit OFFSET $offset";

$transaksi_list = query($sql);

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .status-filter {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .filter-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .filter-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
</style>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;">Transaksi Sewa</h2>
        <a href="tambah.php" class="btn btn-primary">
            ➕ Tambah Transaksi
        </a>
    </div>
    
    <!-- Table -->
    <?php if (num_rows($transaksi_list) > 0): ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Aset</th>
                        <th>Tipe</th>
                        <th>Tgl Mulai</th>
                        <th>Tgl Selesai</th>
                        <th>Lokasi/Penyewa</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trans = fetch_assoc($transaksi_list)): ?>
                        <tr>
                            <td><?php echo $trans['id_transaksi']; ?></td>
                            <td>
                                <strong><?php echo $trans['aset_list']; ?></strong><br>
                                <small style="color: #64748b;">(<?php echo $trans['jumlah_aset']; ?> item)</small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $trans['tipe_transaksi']; ?>
                                </span>
                            </td>
                            <td><?php echo formatTanggalPendek($trans['tanggal_transaksi']); ?></td>
                            <td><?php echo formatTanggalPendek($trans['tanggal_selesai']); ?></td>
                            <td>
                                <?php if ($trans['tipe_transaksi'] == 'Sewa'): ?>
                                    <strong><?php echo $trans['nama_penyewa']; ?></strong><br>
                                <?php endif; ?>
                                <small><?php echo $trans['lokasi_baru']; ?></small>
                            </td>
                            <td><?php echo $trans['keterangan'] ?: '-'; ?></td>
                            <td>
                                <span class="badge badge-<?php echo getStatusColor($trans['status_transaksi']); ?>">
                                    <?php echo $trans['status_transaksi']; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="detail.php?id=<?php echo $trans['id_transaksi']; ?>" 
                                       class="btn btn-info" style="padding: 8px 12px;" title="Detail">
                                        👁️
                                    </a>
                                    
                                    <?php if ($trans['status_transaksi'] == 'Proses'): ?>
                                        <a href="update_status.php?id=<?php echo $trans['id_transaksi']; ?>&status=Selesai" 
                                           onclick="return confirm('Tandai transaksi ini selesai?')"
                                           class="btn btn-success" style="padding: 8px 12px;" title="Selesai">
                                            ✓
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="hapus.php?id=<?php echo $trans['id_transaksi']; ?>" 
                                       onclick="return confirmDelete('Yakin ingin menghapus transaksi ini?')" 
                                       class="btn btn-danger" style="padding: 8px 12px;" title="Hapus">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php echo pagination($total_data, $limit, $page, 'index.php'); ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <div style="font-size: 48px; margin-bottom: 15px;">📋</div>
            <p>Belum ada transaksi</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>