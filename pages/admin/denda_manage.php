<?php
include 'auth_check.php';
require '../config/db.php';
include '../includes/header.php';
function execute_stmt($conn, $sql, $types, $params, $fetch_assoc = false) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Gagal menyiapkan statement database.");
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }

    $success = $stmt->execute();
    
    if ($fetch_assoc) {
        $result = $stmt->get_result();
        $data = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $data;
    }
    
    $stmt->close();
    return $success;
}


$mode = $_GET['mode'] ?? 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : null; 
$dendaRow = $conn->query("SELECT harga_denda FROM tbl_biaya_denda WHERE stat='Aktif' LIMIT 1")->fetch_assoc();
$harga_denda = $dendaRow ? $dendaRow['harga_denda'] : 0;
if ($mode === 'hapus' && $id) {
    try {
        $sql = "DELETE FROM tbl_denda WHERE id_denda=?";
        if (execute_stmt($conn, $sql, 'i', [$id])) {
            echo "<script>alert('Data denda berhasil dihapus!');window.location='denda_manage.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data!');window.location='denda_manage.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: Gagal menghapus data denda!');window.location='denda_manage.php';</script>";
    }
    exit;
}


if ($mode === 'tambah' && isset($_POST['simpan'])) {
    $pinjam_id = trim($_POST['pinjam_id']);
    $lama = intval($_POST['lama_waktu']);
    $tgl_denda = trim($_POST['tgl_denda']);
    $total_denda = $lama * $harga_denda;

    try {
        $sql = "INSERT INTO tbl_denda (pinjam_id, denda, lama_waktu, tgl_denda)
                VALUES (?, ?, ?, ?)";
        
       
        if (execute_stmt($conn, $sql, 'sids', [$pinjam_id, $total_denda, $lama, $tgl_denda])) {
            echo "<script>alert('Data denda berhasil ditambahkan!');window.location='denda_manage.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan data denda!');window.location='denda_manage.php';</script>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Gagal menyimpan data: " . $e->getMessage() . "</div>";
    }
    exit;
}


if ($mode === 'edit' && $id && isset($_POST['update'])) {
    $lama = intval($_POST['lama_waktu']);
    $tgl_denda = trim($_POST['tgl_denda']);
    $total_denda = $lama * $harga_denda;

    try {
        $update = "UPDATE tbl_denda 
                    SET lama_waktu=?, denda=?, tgl_denda=? 
                    WHERE id_denda=?";
        
        
        if (execute_stmt($conn, $update, 'idsi', [$lama, $total_denda, $tgl_denda, $id])) {
            echo "<script>alert('Data denda berhasil diupdate!');window.location='denda_manage.php';</script>";
        } else {
            echo "<script>alert('Gagal mengupdate data denda!');window.location='denda_manage.php';</script>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Gagal mengupdate data: " . $e->getMessage() . "</div>";
    }
    exit;
}


if ($mode === 'list') {
    $sql = "SELECT d.id_denda, d.pinjam_id, d.denda, d.lama_waktu, d.tgl_denda, 
                  COALESCE(m.nama_lengkap, 'Member Dihapus') AS nama_anggota, 
                  COALESCE(b.title, 'Buku Dihapus') AS judul_buku
             FROM tbl_denda d
             LEFT JOIN tbl_pinjam p ON d.pinjam_id = p.pinjam_id
             LEFT JOIN tbl_member m ON p.id_member = m.id_member
             LEFT JOIN tbl_buku b ON p.buku_id = b.buku_id
             ORDER BY d.id_denda DESC";
    $result = $conn->query($sql);
    ?>
    <div class="container my-4">
    <h3 class="mb-3">Data Denda</h3>
    <a href="denda_manage.php?mode=tambah" class="btn btn-primary mb-3">+ Tambah Denda</a>

    <table class="table table-bordered table-striped table-hover align-middle shadow-sm">
      <thead class="table-primary text-center">
        <tr>
          <th>No</th>
          <th>Kode Pinjam</th>
          <th>Nama Member</th>
          <th>Judul Buku</th>
          <th>Lama Waktu (hari)</th>
          <th>Nominal Denda</th>
          <th>Tanggal Denda</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td class="text-center"><?= htmlspecialchars($row['pinjam_id']); ?></td>
            <td><?= htmlspecialchars($row['nama_anggota']); ?></td>
            <td><?= htmlspecialchars($row['judul_buku']); ?></td>
            <td class="text-center"><?= htmlspecialchars($row['lama_waktu']); ?></td>
            <td class="text-end">Rp <?= number_format($row['denda'], 0, ',', '.'); ?></td>
            <td class="text-center"><?= htmlspecialchars($row['tgl_denda']); ?></td>
            <td class="text-center">
              <a href="denda_manage.php?mode=edit&id=<?= urlencode($row['id_denda']); ?>" class="btn btn-warning btn-sm px-3">Edit</a>
              <a href="denda_manage.php?mode=hapus&id=<?= urlencode($row['id_denda']); ?>" class="btn btn-danger btn-sm px-3" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted py-3">Belum ada data denda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <hr class="my-5">

    <h4 class="mb-3">📚 Data Peminjaman Buku</h4>
    <?php
    $pinjam = $conn->query("
        SELECT p.pinjam_id, 
               COALESCE(m.nama_lengkap, 'Member Dihapus') AS nama_anggota, 
               COALESCE(b.title, 'Buku Dihapus') AS judul_buku, 
               p.status, p.tgl_pinjam, p.lama_pinjam, p.tgl_balik, p.tgl_kembali
        FROM tbl_pinjam p
        LEFT JOIN tbl_member m ON p.id_member = m.id_member
        LEFT JOIN tbl_buku b ON p.buku_id = b.buku_id
        ORDER BY p.id_pinjam DESC
    ");
    ?>
    <table class="table table-bordered table-striped align-middle shadow-sm">
      <thead class="table-info text-center">
        <tr>
          <th>No</th>
          <th>Kode Pinjam</th>
          <th>Nama Member</th>
          <th>Judul Buku</th>
          <th>Status</th>
          <th>Tgl Pinjam</th>
          <th>Tgl Balik</th>
          <th>Tgl Kembali</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while($p=$pinjam->fetch_assoc()): ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= htmlspecialchars($p['pinjam_id']); ?></td>
          <td><?= htmlspecialchars($p['nama_anggota']); ?></td>
          <td><?= htmlspecialchars($p['judul_buku']); ?></td>
          <td class="text-center"><?= htmlspecialchars($p['status']); ?></td>
          <td class="text-center"><?= htmlspecialchars($p['tgl_pinjam']); ?></td>
          <td class="text-center"><?= htmlspecialchars($p['tgl_balik']); ?></td>
          <td class="text-center"><?= htmlspecialchars($p['tgl_kembali']); ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($pinjam->num_rows == 0): ?>
          <tr><td colspan="8" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
<?php
}


if ($mode === 'tambah') {

 $pinjam = $conn->query("
      SELECT p.pinjam_id, COALESCE(m.nama_lengkap, 'Member Dihapus') AS nama_lengkap
      FROM tbl_pinjam p
      LEFT JOIN tbl_member m ON p.id_member = m.id_member
      ORDER BY p.id_pinjam DESC
 ");
 ?>
 <div class="container my-4">
 <h3 class="mb-3">Tambah Data Denda</h3>
 <form method="POST">
   <div class="mb-3">
     <label>Kode Pinjam & Nama Member</label>
     <select name="pinjam_id" class="form-select" required>
       <option value="">-- Pilih Kode Pinjam dan Nama --</option>
       <?php while($p = $pinjam->fetch_assoc()): ?>
       <option value="<?= htmlspecialchars($p['pinjam_id']); ?>">
         <?= htmlspecialchars($p['pinjam_id']); ?> - <?= htmlspecialchars($p['nama_lengkap']); ?>
       </option>
       <?php endwhile; ?>
     </select>
   </div>

   <div class="mb-3">
     <label>Lama Waktu (hari)</label>
     <input type="number" name="lama_waktu" class="form-control" min="1" value="1" required>
   </div>

   <div class="mb-3">
     <label>Denda per Hari (Rp)</label>
     <input type="text" class="form-control" value="<?= number_format($harga_denda, 0, ',', '.'); ?>" readonly>
   </div>

   <div class="mb-3">
     <label>Tanggal Denda</label>
     <input type="date" name="tgl_denda" class="form-control" value="<?= date('Y-m-d'); ?>" required>
   </div>

   <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
   <a href="denda_manage.php" class="btn btn-secondary">Kembali</a>
 </form>
 </div>
<?php
}


if ($mode === 'edit' && $id) {
    

    try {
        $sql_select = "SELECT d.*, m.nama_lengkap
                        FROM tbl_denda d
                        LEFT JOIN tbl_pinjam p ON d.pinjam_id = p.pinjam_id
                        LEFT JOIN tbl_member m ON p.id_member = m.id_member
                        WHERE d.id_denda=?";
        $data = execute_stmt($conn, $sql_select, 'i', [$id], true);
    } catch (Exception $e) {
        $data = null;
    }


 if (!$data) {
   echo "<script>alert('ID tidak ditemukan!');window.location='denda_manage.php';</script>";
   exit;
 }
 ?>
 <div class="container my-4">
 <h3 class="mb-3">Edit Data Denda</h3>
 <form method="POST">
   <div class="mb-3">
     <label>Kode Pinjam & Nama Member</label>
     <input type="text" class="form-control" value="<?= htmlspecialchars($data['pinjam_id']); ?> - <?= htmlspecialchars($data['nama_lengkap']); ?>" readonly>
   </div>

   <div class="mb-3">
     <label>Lama Waktu (hari)</label>
     <input type="number" name="lama_waktu" class="form-control" value="<?= htmlspecialchars($data['lama_waktu']); ?>" min="1" required>
   </div>

   <div class="mb-3">
     <label>Denda per Hari (Rp)</label>
     <input type="text" class="form-control" value="<?= number_format($harga_denda, 0, ',', '.'); ?>" readonly>
   </div>

   <div class="mb-3">
     <label>Tanggal Denda</label>
     <input type="date" name="tgl_denda" class="form-control" value="<?= htmlspecialchars($data['tgl_denda']); ?>" required>
   </div>

   <button type="submit" name="update" class="btn btn-success">Update</button>
   <a href="denda_manage.php" class="btn btn-secondary">Kembali</a>
 </form>
 </div>
<?php
}

include '../includes/footer.php';
?>