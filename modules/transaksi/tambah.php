<?php
// modules/transaksi/tambah.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'transaksi';
$page_title = 'Tambah Transaksi';
$page_icon = '📋';

$error = '';
$aset_terpilih = [];

// Get aset yang tersedia (status Aktif saja)
$aset_list = query("SELECT a.*, k.nama_kategori 
                    FROM aset a 
                    JOIN kategori_aset k ON a.id_kategori = k.id_kategori 
                    WHERE a.status = 'Aktif' 
                    ORDER BY a.nama_aset");

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penyewa = escape(sanitize($_POST['nama_penyewa'] ?? ''));
    $tipe_transaksi = escape($_POST['tipe_transaksi']);
    $tanggal_mulai = escape($_POST['tanggal_mulai']);
    $tanggal_selesai = escape($_POST['tanggal_selesai']);
    $lokasi = escape(sanitize($_POST['lokasi']));
    $total_biaya = (int)str_replace('.', '', $_POST['total_biaya'] ?? '0');
    $dp_uang_muka = (int)str_replace('.', '', $_POST['dp_uang_muka'] ?? '0');
    $keterangan = escape(sanitize($_POST['keterangan'] ?? ''));
    $aset_ids = $_POST['aset_ids'] ?? [];
    
    // Validasi
    if (empty($aset_ids)) {
        $error = 'Pilih minimal 1 aset!';
    } elseif (empty($lokasi)) {
        $error = 'Lokasi harus diisi!';
    } else {
        // Cek ketersediaan setiap aset
        $aset_tidak_tersedia = [];
        foreach ($aset_ids as $id_aset) {
            if (!isAsetTersedia($id_aset, $tanggal_mulai, $tanggal_selesai)) {
                $aset_result = query("SELECT nama_aset FROM aset WHERE id_aset = $id_aset");
                $aset_data = fetch_assoc($aset_result);
                $aset_tidak_tersedia[] = $aset_data['nama_aset'];
            }
        }
        
        if (!empty($aset_tidak_tersedia)) {
            $error = 'Aset berikut tidak tersedia pada tanggal tersebut: ' . implode(', ', $aset_tidak_tersedia);
        } else {
            // Insert transaksi untuk setiap aset
            $success_count = 0;
            foreach ($aset_ids as $id_aset) {
                $sql = "INSERT INTO transaksi_sewa 
                        (id_aset, nama_penyewa, tipe_transaksi, tanggal_transaksi, tanggal_selesai, lokasi_baru, total_biaya, dp_uang_muka, status_transaksi, keterangan) 
                        VALUES 
                        ($id_aset, '$nama_penyewa', '$tipe_transaksi', '$tanggal_mulai', '$tanggal_selesai', '$lokasi', $total_biaya, $dp_uang_muka, 'Proses', '$keterangan')";
                
                if (query($sql)) {
                    $success_count++;
                    
                    // Update status aset menjadi Disewa (opsional)
                    // query("UPDATE aset SET status = 'Disewa' WHERE id_aset = $id_aset");
                }
            }
            
            if ($success_count > 0) {
                $_SESSION['success_message'] = "$success_count transaksi dan status aset berhasil ditambahkan/diperbarui!";
                header('Location: index.php');
                exit;
            } else {
                $error = 'Gagal menambahkan transaksi!';
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .aset-selector {
        max-height: 400px;
        overflow-y: auto;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        background: #f8fafc;
    }
    
    .aset-item {
        padding: 12px;
        margin-bottom: 10px;
        background: white;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .aset-item:hover {
        border-color: var(--primary);
        transform: translateX(5px);
    }
    
    .aset-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .aset-info {
        flex: 1;
    }
    
    .aset-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
    }
    
    .aset-info small {
        color: #64748b;
        font-size: 13px;
    }
    
    .selected-count {
        background: var(--primary);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 15px;
    }
</style>

<div class="card" style="max-width: 1000px;">
    <h2 class="card-title">📋 Tambah Transaksi</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">✕ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" id="transaksiForm">
        <div class="form-group">
            <label for="nama_penyewa">Nama Penyewa</label>
            <input type="text" id="nama_penyewa" name="nama_penyewa" class="form-control" 
                   placeholder="Dimas Makhzum" value="<?php echo $_POST['nama_penyewa'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Pilih Aset (Hanya aset "Aktif", "Disewa", atau "Perbaikan") *</label>
            <div class="selected-count">
                Aset Dipilih: <span id="selectedCount">0</span>
            </div>
            
            <div class="aset-selector">
                <?php while($aset = fetch_assoc($aset_list)): ?>
                    <label class="aset-item">
                        <input type="checkbox" name="aset_ids[]" value="<?php echo $aset['id_aset']; ?>" 
                               onchange="updateCount()" class="aset-checkbox">
                        <div class="aset-info">
                            <h4><?php echo $aset['nama_aset']; ?></h4>
                            <small>
                                <?php echo $aset['nomor_seri']; ?> - 
                                <?php echo $aset['nama_kategori']; ?>
                            </small>
                        </div>
                        <span class="badge badge-success"><?php echo $aset['status']; ?></span>
                    </label>
                <?php endwhile; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="tipe_transaksi">Tipe Transaksi (Akan mengubah status Aset) *</label>
            <select id="tipe_transaksi" name="tipe_transaksi" class="form-control" required>
                <option value="Sewa">Sewa (Aset akan jadi 'Disewa')</option>
                <option value="Pergeseran">Pergeseran</option>
                <option value="Perbaikan">Perbaikan (Aset akan jadi 'Diperbaiki')</option>
                <option value="Penghapusan">Penghapusan</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="tanggal_mulai">Tanggal Mulai *</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" 
                       required value="<?php echo $_POST['tanggal_mulai'] ?? date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="tanggal_selesai">Tanggal Selesai *</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" 
                       required value="<?php echo $_POST['tanggal_selesai'] ?? ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="lokasi">Lokasi Event *</label>
            <input type="text" id="lokasi" name="lokasi" class="form-control" 
                   placeholder="Jl. Peganden Raya" required value="<?php echo $_POST['lokasi'] ?? ''; ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="total_biaya">Total Biaya (Rp) *</label>
                <input type="text" id="total_biaya" name="total_biaya" class="form-control" 
                       placeholder="1000000" required value="<?php echo $_POST['total_biaya'] ?? ''; ?>"
                       oninput="formatRupiahInput(this)">
            </div>
            
            <div class="form-group">
                <label for="dp_uang_muka">DP / Uang Muka (Rp)</label>
                <input type="text" id="dp_uang_muka" name="dp_uang_muka" class="form-control" 
                       placeholder="100000" value="<?php echo $_POST['dp_uang_muka'] ?? '0'; ?>"
                       oninput="formatRupiahInput(this)">
                <small style="color: #64748b;">Opsional - Kosongkan jika belum ada DP</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="keterangan">Keterangan Tambahan</label>
            <textarea id="keterangan" name="keterangan" class="form-control" rows="3" 
                      placeholder="Dikirim Malam"><?php echo $_POST['keterangan'] ?? ''; ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="button" onclick="showPreview()" class="btn btn-primary">
                👁️ Preview & Simpan
            </button>
            <a href="index.php" class="btn" style="background: #e2e8f0; color: #334155;">
                ✕ Batal
            </a>
        </div>
    </form>
</div>

<!-- Modal Preview -->
<div id="previewModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">🎵 Putra Tunggal Audio<br><small>Bukti Transaksi</small></h3>
            <button class="modal-close" onclick="closeModal('previewModal')">✕</button>
        </div>
        
        <div id="previewContent" style="padding: 20px 0;">
            <!-- Content will be inserted by JavaScript -->
        </div>
        
        <div style="display: flex; gap: 10px; padding-top: 20px; border-top: 2px dashed #e2e8f0;">
            <button onclick="document.getElementById('transaksiForm').submit()" class="btn btn-success" style="flex: 1;">
                💾 Simpan
            </button>
            <button onclick="closeModal('previewModal')" class="btn" style="background: #e2e8f0; color: #334155;">
                ✕ Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function updateCount() {
        const checkboxes = document.querySelectorAll('.aset-checkbox:checked');
        document.getElementById('selectedCount').textContent = checkboxes.length;
    }
    
    function showPreview() {
        const form = document.getElementById('transaksiForm');
        const checkboxes = document.querySelectorAll('.aset-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Pilih minimal 1 aset!');
            return;
        }
        
        if (!form.checkValidity()) {
            alert('Lengkapi semua field yang wajib diisi!');
            return;
        }
        
        // Build preview content
        let asetList = '<div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
        asetList += '<strong>Detail Aset (' + checkboxes.length + '):</strong><br><br>';
        
        checkboxes.forEach((cb, index) => {
            const item = cb.closest('.aset-item');
            const asetName = item.querySelector('h4').textContent;
            const asetSerial = item.querySelector('small').textContent;
            asetList += '<div style="margin-bottom: 10px;">';
            asetList += '<strong>Aset ' + (index + 1) + ':</strong> ' + asetName + '<br>';
            asetList += '<small style="color: #64748b;">' + asetSerial + '</small>';
            asetList += '</div>';
        });
        asetList += '</div>';
        
        const content = `
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Nama Penyewa:</strong></td>
                    <td>${document.getElementById('nama_penyewa').value || '-'}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Tipe:</strong></td>
                    <td>${document.getElementById('tipe_transaksi').value}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Tanggal Mulai:</strong></td>
                    <td>${document.getElementById('tanggal_mulai').value}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Tanggal Selesai:</strong></td>
                    <td>${document.getElementById('tanggal_selesai').value}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Lokasi Event:</strong></td>
                    <td>${document.getElementById('lokasi').value}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Total Biaya:</strong></td>
                    <td style="color: #10b981; font-weight: bold;">Rp ${document.getElementById('total_biaya').value || '0'}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>DP / Uang Muka:</strong></td>
                    <td style="color: #f59e0b; font-weight: bold;">Rp ${document.getElementById('dp_uang_muka').value || '0'}</td>
                </tr>
            </table>
            ${asetList}
            <div style="background: #fff3cd; padding: 12px; border-radius: 8px; color: #856404; border: 1px solid #ffeaa7;">
                <strong>⚠️ Perhatian:</strong><br>
                ${checkboxes.length} Transaksi dan status aset akan ditambahkan/diperbarui!
            </div>
        `;
        
        document.getElementById('previewContent').innerHTML = content;
        openModal('previewModal');
    }
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>