<?php
// modules/aset/index.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'aset';
$page_title = 'Kelola Aset';
$page_icon = '📦';

// Search & Filter
$search = isset($_GET['search']) ? escape($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (a.nama_aset LIKE '%$search%' OR a.nomor_seri LIKE '%$search%')";
}
if ($kategori_filter) {
    $where .= " AND a.id_kategori = $kategori_filter";
}

// Get total for pagination
$total_query = query("SELECT COUNT(*) as total FROM aset a $where");
$total_data = fetch_assoc($total_query)['total'];

// Get aset data
$sql = "SELECT a.*, k.nama_kategori, ko.status_kondisi 
        FROM aset a
        JOIN kategori_aset k ON a.id_kategori = k.id_kategori
        JOIN kondisi_aset ko ON a.id_kondisi = ko.id_kondisi
        $where
        ORDER BY a.created_at DESC
        LIMIT $limit OFFSET $offset";
$aset_list = query($sql);

// Get kategori for filter
$kategori_list = query("SELECT * FROM kategori_aset ORDER BY nama_kategori");

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;">Kelola Aset</h2>
        <a href="tambah.php" class="btn btn-primary">
            ➕ Tambah Aset
        </a>
    </div>
    
    <!-- Search & Filter -->
    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <form method="GET" style="flex: 1; min-width: 250px;">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Cari aset..." value="<?php echo $search; ?>">
            </div>
        </form>
        
        <form method="GET" style="min-width: 200px;">
            <select name="kategori" class="form-control" onchange="this.form.submit()">
                <option value="0">Semua Kategori</option>
                <?php while($kat = fetch_assoc($kategori_list)): ?>
                    <option value="<?php echo $kat['id_kategori']; ?>" <?php echo $kategori_filter == $kat['id_kategori'] ? 'selected' : ''; ?>>
                        <?php echo $kat['nama_kategori']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
    
    <!-- Table -->
    <?php if (num_rows($aset_list) > 0): ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Kondisi</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($aset = fetch_assoc($aset_list)): ?>
                        <tr>
                            <td><?php echo $aset['id_aset']; ?></td>
                            <td>
                                <strong><?php echo $aset['nama_aset']; ?></strong><br>
                                <small style="color: #64748b;"><?php echo $aset['nomor_seri']; ?></small>
                            </td>
                            <td><?php echo $aset['nama_kategori']; ?></td>
                            <td><?php echo $aset['status_kondisi']; ?></td>
                            <td><?php echo $aset['lokasi']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo getStatusColor($aset['status']); ?>">
                                    <?php echo $aset['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="edit.php?id=<?php echo $aset['id_aset']; ?>" class="btn btn-warning" style="padding: 8px 12px;">
                                        ✏️
                                    </a>
                                    <a href="hapus.php?id=<?php echo $aset['id_aset']; ?>" 
                                       onclick="return confirmDelete('Yakin ingin menghapus aset ini?')" 
                                       class="btn btn-danger" style="padding: 8px 12px;">
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
            <div style="font-size: 48px; margin-bottom: 15px;">📦</div>
            <p>Tidak ada data aset ditemukan</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>