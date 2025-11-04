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
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    try {
        $sql_select = "SELECT buku_id, status FROM tbl_pinjam WHERE id_pinjam=?";
        $pinjam = execute_stmt($conn, $sql_select, 'i', [$id], true);
    } catch (Exception $e) {
        echo "<script>alert('Gagal mengambil data peminjaman!');window.location='pinjam_manage.php';</script>";
        exit;
    }

    if ($pinjam) {
        $conn->begin_transaction();
        $success = false;
        
        try {
            if ($pinjam['status'] == 'Dipinjam') {
                $sql_update_stok = "UPDATE tbl_buku SET jml = jml + 1 WHERE buku_id=?";
                execute_stmt($conn, $sql_update_stok, 's', [$pinjam['buku_id']]);
            }

            $sql_delete = "DELETE FROM tbl_pinjam WHERE id_pinjam=?";
            if (execute_stmt($conn, $sql_delete, 'i', [$id])) {
                $conn->commit();
                $success = true;
            } else {
                $conn->rollback();
            }
        } catch (Exception $e) {
            $conn->rollback();
        }

        if ($success) {
            echo "<script>alert('Data peminjaman dihapus!');window.location='pinjam_manage.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data!');window.location='pinjam_manage.php';</script>";
        }
    } else {
        echo "<script>alert('Data peminjaman tidak ditemukan!');window.location='pinjam_manage.php';</script>";
    }
    exit;
}


$editMode = false;
$data = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    
    try {
        $sql_select = "SELECT * FROM tbl_pinjam WHERE id_pinjam=?";
        $data = execute_stmt($conn, $sql_select, 'i', [$id], true);
        if (!$data) {
            $editMode = false;
            echo "<script>alert('Data tidak ditemukan!');window.location='pinjam_manage.php';</script>";
        }
    } catch (Exception $e) {
        $editMode = false;
    }
}


if (isset($_POST['simpan'])) {
 
    $kode = "PJ" . rand(100, 999);
    $member = trim($_POST['id_member']);
    $buku = trim($_POST['buku_id']);
    $tgl_pinjam = trim($_POST['tgl_pinjam']);
    $lama = intval($_POST['lama_pinjam']);

   
    $cek = $conn->prepare("SELECT id_member FROM tbl_member WHERE id_member=?");
    $cek->bind_param("i", $member); 
    $cek->execute();
    if ($cek->get_result()->num_rows == 0) {
        echo "<script>alert('Member tidak ditemukan!');window.location='pinjam_manage.php?tambah=true';</script>";
        $cek->close();
        exit;
    }
    $cek->close();

    
    $cekBuku = execute_stmt($conn, "SELECT jml FROM tbl_buku WHERE buku_id=?", 's', [$buku], true); // buku_id VARCHAR
    
    if (!$cekBuku) {
        echo "<script>alert('Buku tidak ditemukan!');window.location='pinjam_manage.php?tambah=true';</script>";
        exit;
    }

    if ($cekBuku['jml'] <= 0) {
        
        echo "<script>alert('Gagal meminjam: Stok buku ini sudah 0!');window.location='pinjam_manage.php?tambah=true';</script>";
        exit;
    }

 
    $tgl_balik = date('Y-m-d', strtotime("+$lama days", strtotime($tgl_pinjam)));

    
    $conn->begin_transaction();
    try {
        
        $sql = "INSERT INTO tbl_pinjam (pinjam_id, id_member, buku_id, status, tgl_pinjam, lama_pinjam, tgl_balik) 
                VALUES (?, ?, ?, 'Dipinjam', ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql);
        
       
        $stmt_insert->bind_param('sissis', $kode, $member, $buku, $tgl_pinjam, $lama, $tgl_balik);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Gagal menyimpan data peminjaman. Error: " . $stmt_insert->error);
        }
        $stmt_insert->close();

   
        $sql_update_stok = "UPDATE tbl_buku SET jml = jml - 1 WHERE buku_id=?";
        $stmt_update = $conn->prepare($sql_update_stok);
        $stmt_update->bind_param('s', $buku); 

        if (!$stmt_update->execute()) {
            throw new Exception("Gagal mengurangi stok buku.");
        }
        $stmt_update->close();

    
        $conn->commit(); 
        echo "<script>alert('Data peminjaman berhasil ditambahkan!');window.location='pinjam_manage.php';</script>";
        
    } catch (Exception $e) {

        $conn->rollback();
        echo "<script>alert('Gagal meminjam: Terjadi kesalahan. Pesan: " . addslashes($e->getMessage()) . "');window.location='pinjam_manage.php?tambah=true';</script>";
    }
    exit;
}



if (isset($_POST['update'])) {
    $status = $_POST['status'];
    
    $tgl_kembali = empty($_POST['tgl_kembali']) ? NULL : $_POST['tgl_kembali'];
    $id = intval($_GET['edit']);

    try {
        $sql_select = "SELECT buku_id, status FROM tbl_pinjam WHERE id_pinjam=?";
        $pinjam = execute_stmt($conn, $sql_select, 'i', [$id], true);
    } catch (Exception $e) {
        echo "<script>alert('Gagal mengambil data peminjaman saat update!');window.location='pinjam_manage.php';</script>";
        exit;
    }

    if (!$pinjam) {
        echo "<script>alert('Data peminjaman tidak ditemukan!');window.location='pinjam_manage.php';</script>";
        exit;
    }

    $conn->begin_transaction();
    $success = false;

    try {
        // 2. Update status dan tgl_kembali
        $update_sql = "UPDATE tbl_pinjam SET status=?, tgl_kembali=? WHERE id_pinjam=?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param('ssi', $status, $tgl_kembali, $id);

        if (!$stmt_update->execute()) {
             throw new Exception("Gagal memperbarui data peminjaman.");
        }
        $stmt_update->close();
        
        // 3. Tambah stok buku jika dikembalikan dan status sebelumnya BUKAN 'Dikembalikan'
        if ($status == 'Dikembalikan' && $pinjam['status'] != 'Dikembalikan') {
            $sql_update_stok = "UPDATE tbl_buku SET jml = jml + 1 WHERE buku_id=?";
            execute_stmt($conn, $sql_update_stok, 's', [$pinjam['buku_id']]);
        }
        
        $conn->commit();
        $success = true;

    } catch (Exception $e) {
        $conn->rollback();
    }

    if ($success) {
        echo "<script>alert('Data berhasil diupdate!');window.location='pinjam_manage.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update data!</div>";
    }
    exit;
}


$member = $conn->query("SELECT id_member, nama_lengkap FROM tbl_member ORDER BY nama_lengkap ASC");
$buku = $conn->query("SELECT buku_id, title, jml FROM tbl_buku WHERE jml > 0 ORDER BY title ASC");


$sql = "SELECT p.id_pinjam, p.pinjam_id, 
             COALESCE(m.nama_lengkap, 'Member Dihapus') AS nama_member, 
             COALESCE(b.title, 'Buku Dihapus') AS judul_buku, 
             p.status, p.tgl_pinjam, p.lama_pinjam, p.tgl_balik, p.tgl_kembali
         FROM tbl_pinjam p
         LEFT JOIN tbl_member m ON p.id_member = m.id_member
         LEFT JOIN tbl_buku b ON p.buku_id = b.buku_id
         ORDER BY p.id_pinjam DESC";
$result = $conn->query($sql);


if ($result) {
    $result->data_seek(0);
}
?>

<div class="container my-4">
    <h3 class="mb-3">
        <?= $editMode ? "Edit Data Peminjaman" : "Data Peminjaman Buku" ?>
    </h3>

    <?php if ($editMode): ?>
        <form method="POST">
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="Dipinjam" <?= $data['status']=='Dipinjam'?'selected':'' ?>>Dipinjam</option>
                    <option value="Dikembalikan" <?= $data['status']=='Dikembalikan'?'selected':'' ?>>Dikembalikan</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Tanggal Kembali</label>
                <input type="date" name="tgl_kembali" class="form-control" value="<?= htmlspecialchars($data['tgl_kembali'] ?? date('Y-m-d')); ?>">
            </div>
            <button type="submit" name="update" class="btn btn-success">Update</button>
            <a href="pinjam_manage.php" class="btn btn-secondary">Kembali</a>
        </form>

    <?php else: ?>
        <a href="?tambah=true" class="btn btn-primary mb-3">+ Tambah Peminjaman</a>

        <?php if (isset($_GET['tambah'])): ?>
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label>Nama Member</label>
                    <select name="id_member" class="form-select" required>
                        <option value="">-- Pilih Member --</option>
                        <?php $member->data_seek(0); while($m = $member->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($m['id_member']); ?>"><?= htmlspecialchars($m['nama_lengkap']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Judul Buku (Stok > 0)</label>
                    <select name="buku_id" class="form-select" required>
                        <option value="">-- Pilih Buku --</option>
                        <?php $buku->data_seek(0); while($b = $buku->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($b['buku_id']); ?>">
                            <?= htmlspecialchars($b['title']); ?> (Stok: <?= $b['jml']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Lama Pinjam (hari)</label>
                    <input type="number" name="lama_pinjam" class="form-control" value="7" min="1" required>
                </div>

                <div class="mb-3">
                    <label>Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                <a href="pinjam_manage.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>

        <table class="table table-bordered table-striped align-middle shadow-sm">
            <thead class="table-primary text-center">
                <tr>
                    <th>No</th>
                    <th>Kode Pinjam</th>
                    <th>Nama Member</th>
                    <th>Judul Buku</th>
                    <th>Status</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tgl Balik</th>
                    <th>Tgl Kembali</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row=$result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['pinjam_id']); ?></td>
                    <td><?= htmlspecialchars($row['nama_member']); ?></td>
                    <td><?= htmlspecialchars($row['judul_buku']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['status']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['tgl_pinjam']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['tgl_balik']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['tgl_kembali'] ?? '-'); ?></td>
                    <td class="text-center">
                        <a href="?edit=<?= $row['id_pinjam']; ?>" class="btn btn-warning btn-sm px-3">Edit</a>
                        <a href="?hapus=<?= $row['id_pinjam']; ?>" class="btn btn-danger btn-sm px-3" onclick="return confirm('Hapus data peminjaman ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
