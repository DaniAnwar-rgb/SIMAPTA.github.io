<?php
// modules/aset/tambah.php
session_start();
require_once __DIR__ . '/../../modules/auth/auth_check.php';

$current_page = 'aset';
$page_title = 'Tambah Aset';
$page_icon = '➕';

$error = '';
$success = '';

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
    
    // Handle upload foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'aset_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = __DIR__ . '/../../uploads/aset/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $new_filename;
            }
        } else {
            $error = 'Format foto harus JPG, JPEG, PNG, atau GIF!';
        }
    }
    
    // Validasi
    if (empty($error) && (empty($nama_aset) || empty($nomor_seri))) {
        $error = 'Nama aset dan nomor seri harus diisi!';
    } else if (empty($error)) {
        // Cek nomor seri duplikat
        $check = query("SELECT * FROM aset WHERE nomor_seri = '$nomor_seri'");
        if (num_rows($check) > 0) {
            $error = 'Nomor seri sudah digunakan!';
        } else {
            // Insert data
            $foto_value = $foto ? "'$foto'" : "NULL";
            $sql = "INSERT INTO aset (id_kategori, id_kondisi, nama_aset, nomor_seri, harga_aset, tanggal_perolehan, lokasi, foto, status) 
                    VALUES ($id_kategori, $id_kondisi, '$nama_aset', '$nomor_seri', $harga_aset, '$tanggal_perolehan', '$lokasi', $foto_value, '$status')";
            
            if (query($sql)) {
                $_SESSION['success_message'] = 'Aset berhasil ditambahkan!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Gagal menambahkan aset!';
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width: 800px;">
    <h2 class="card-title">➕ Tambah Aset Baru</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">✕ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="nama_aset">Nama Aset *</label>
            <input type="text" id="nama_aset" name="nama_aset" class="form-control" 
                   placeholder="Microphone Shure" required value="<?php echo $_POST['nama_aset'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="nomor_seri">Nomor Seri *</label>
            <input type="text" id="nomor_seri" name="nomor_seri" class="form-control" 
                   placeholder="Beta 501" required value="<?php echo $_POST['nomor_seri'] ?? ''; ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="id_kategori">Kategori *</label>
                <select id="id_kategori" name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    mysqli_data_seek($kategori_list, 0);
                    while($kat = fetch_assoc($kategori_list)): 
                    ?>
                        <option value="<?php echo $kat['id_kategori']; ?>" 
                            <?php echo (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
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
                            <?php echo (isset($_POST['id_kondisi']) && $_POST['id_kondisi'] == $kon['id_kondisi']) ? 'selected' : ''; ?>>
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
                       placeholder="500000" required value="<?php echo $_POST['harga_aset'] ?? ''; ?>"
                       oninput="formatRupiahInput(this)">
            </div>
            
            <div class="form-group">
                <label for="tanggal_perolehan">Tanggal Perolehan *</label>
                <input type="date" id="tanggal_perolehan" name="tanggal_perolehan" class="form-control" 
                       required value="<?php echo $_POST['tanggal_perolehan'] ?? date('Y-m-d'); ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="lokasi">Lokasi *</label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" 
                       placeholder="Cerme" required value="<?php echo $_POST['lokasi'] ?? 'Cerme'; ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                    <option value="Diperbaiki">Diperbaiki</option>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                💾 Simpan
            </button>
            <a href="index.php" class="btn" style="background: #e2e8f0; color: #334155;">
                ✕ Batal
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>