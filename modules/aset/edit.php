<?php
// modules/aset/edit.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'aset';
$page_title = 'Edit Aset';
$page_icon = '✏️';

$error = '';
$id_aset = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get aset data
$result = query("SELECT * FROM aset WHERE id_aset = $id_aset");
if (num_rows($result) == 0) {
    $_SESSION['error_message'] = 'Aset tidak ditemukan!';
    header('Location: index.php');
    exit;
}
$aset = fetch_assoc($result);

// Get kategori & kondisi
$kategori_list = query("SELECT * FROM kategori_aset ORDER BY nama_kategori");
$kondisi_list = query("SELECT * FROM kondisi_aset ORDER BY id_kondisi");

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_aset = escape(sanitize($_POST['nama_aset']));
    $nomor_seri = escape(sanitize($_POST['nomor_seri']));
    $id_kategori = (int)$_POST['id_kategori'];
    $id_kondisi = (int)$_POST['id_kondisi'];
    $harga_aset = (int)str_replace('.', '', $_POST['harga_aset']);
    $tanggal_perolehan = escape($_POST['tanggal_perolehan']);
    $lokasi = escape(sanitize($_POST['lokasi']));
    $status = escape($_POST['status']);
    
    // Validasi
    if (empty($nama_aset) || empty($nomor_seri)) {
        $error = 'Nama aset dan nomor seri harus diisi!';
    } else {
        // Cek nomor seri duplikat (exclude current)
        $check = query("SELECT * FROM aset WHERE nomor_seri = '$nomor_seri' AND id_aset != $id_aset");
        if (num_rows($check) > 0) {
            $error = 'Nomor seri sudah digunakan!';
        } else {
            // Update data
            $sql = "UPDATE aset SET 
                    id_kategori = $id_kategori,
                    id_kondisi = $id_kondisi,
                    nama_aset = '$nama_aset',
                    nomor_seri = '$nomor_seri',
                    harga_aset = $harga_aset,
                    tanggal_perolehan = '$tanggal_perolehan',
                    lokasi = '$lokasi',
                    status = '$status'
                    WHERE id_aset = $id_aset";
            
            if (query($sql)) {
                $_SESSION['success_message'] = 'Aset berhasil diupdate!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Gagal mengupdate aset!';
            }
        }
    }
    
    // Jika error, gunakan data dari POST
    $aset = array_merge($aset, $_POST);
} else {
    // Format harga untuk display
    $aset['harga_aset'] = number_format($aset['harga_aset'], 0, ',', '.');
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width: 800px;">
    <h2 class="card-title">✏️ Edit Aset</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">✕ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="nama_aset">Nama Aset *</label>
            <input type="text" id="nama_aset" name="nama_aset" class="form-control" 
                   placeholder="Microphone Shure" required value="<?php echo $aset['nama_aset']; ?>">
        </div>
        
        <div class="form-group">
            <label for="nomor_seri">Nomor Seri *</label>
            <input type="text" id="nomor_seri" name="nomor_seri" class="form-control" 
                   placeholder="Beta 501" required value="<?php echo $aset['nomor_seri']; ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="id_kategori">Kategori *</label>
                <select id="id_kategori" name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while($kat = fetch_assoc($kategori_list)): ?>
                        <option value="<?php echo $kat['id_kategori']; ?>" 
                            <?php echo $aset['id_kategori'] == $kat['id_kategori'] ? 'selected' : ''; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_kondisi">Kondisi *</label>
                <select id="id_kondisi" name="id_kondisi" class="form-control" required>
                    <?php while($kon = fetch_assoc($kondisi_list)): ?>
                        <option value="<?php echo $kon['id_kondisi']; ?>" 
                            <?php echo $aset['id_kondisi'] == $kon['id_kondisi'] ? 'selected' : ''; ?>>
                            <?php echo $kon['status_kondisi']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="harga_aset">Harga Aset (Rp) *</label>
                <input type="text" id="harga_aset" name="harga_aset" class="form-control" 
                       required value="<?php echo $aset['harga_aset']; ?>"
                       oninput="formatRupiahInput(this)">
            </div>
            
            <div class="form-group">
                <label for="tanggal_perolehan">Tanggal Perolehan *</label>
                <input type="date" id="tanggal_perolehan" name="tanggal_perolehan" class="form-control" 
                       required value="<?php echo $aset['tanggal_perolehan']; ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="lokasi">Lokasi *</label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" 
                       placeholder="Cerme" required value="<?php echo $aset['lokasi']; ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Aktif" <?php echo $aset['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo $aset['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                    <option value="Diperbaiki" <?php echo $aset['status'] == 'Diperbaiki' ? 'selected' : ''; ?>>Diperbaiki</option>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                💾 Update
            </button>
            <a href="index.php" class="btn" style="background: #e2e8f0; color: #334155;">
                ✕ Batal
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>