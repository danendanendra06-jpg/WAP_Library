<?php include 'auth_check.php'; ?>
<?php
require '../config/db.php';
include '../includes/header.php';


$pesan = "";

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = $conn->query("SELECT gambar_buku FROM tbl_buku WHERE id_buku=$id");
    if ($q && $r = $q->fetch_assoc()) {
        if ($r['gambar_buku'] && file_exists("../asset/img//" . $r['gambar_buku'])) {
            unlink("../asset/img//" . $r['gambar_buku']);
        }
    }
    if ($conn->query("DELETE FROM tbl_buku WHERE id_buku=$id")) {
        echo "<script>alert('Buku berhasil dihapus.');window.location='buku_manage.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus buku.');window.location='buku_manage.php';</script>";
    }
    exit;
}
if (isset($_GET['detail'])) {
    $id = (int)$_GET['detail'];
    $result = $conn->query("SELECT b.*, k.nama_kategori 
                            FROM tbl_buku b
                            LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
                            WHERE b.id_buku=$id");
    if ($result && $result->num_rows > 0) {
        $detail = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>Data tidak ditemukan.</div>";
        include 'footer.php';
        exit;
    }
}


$editMode = false;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM tbl_buku WHERE id_buku=$id");
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
        include 'footer.php';
        exit;
    }
}


$kategoriList = $conn->query("SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");


if (isset($_POST['simpan'])) {
    $buku_id     = $_POST['buku_id'];
    $id_kategori = (int)$_POST['id_kategori'];
    $id_rak      = (int)$_POST['id_rak'];
    $isbn        = $_POST['isbn'];
    $title       = $_POST['title'];
    $penerbit    = $_POST['penerbit'];
    $pengarang   = $_POST['pengarang'];
    $thn_buku    = $_POST['thn_buku'];
    $isi         = $_POST['isi'];
    $jml         = (int)$_POST['jml'];
    $tgl_masuk   = date("Y-m-d H:i:s");


    $gambar_buku = null;
    if (!empty($_FILES['gambar_buku']['name'])) {
        $dir = "../asset/img//";
        if (!is_dir($dir)) mkdir($dir);
        $file_name = time() . "_" . basename($_FILES["gambar_buku"]["name"]);
        $target = $dir . $file_name;
        if (move_uploaded_file($_FILES["gambar_buku"]["tmp_name"], $target)) {
            $gambar_buku = $file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO tbl_buku 
        (buku_id, id_kategori, id_rak, isbn, title, penerbit, pengarang, thn_buku, isi, jml, tgl_masuk, gambar_buku)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisssssisss", $buku_id, $id_kategori, $id_rak, $isbn, $title, $penerbit, $pengarang, $thn_buku, $isi, $jml, $tgl_masuk, $gambar_buku);

    if ($stmt->execute()) {
        echo "<script>alert('Buku berhasil ditambahkan.');window.location='buku_manage.php';</script>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal menambah buku: {$stmt->error}</div>";
    }
}


if (isset($_POST['update'])) {
    $buku_id     = $_POST['buku_id'];
    $id_kategori = (int)$_POST['id_kategori'];
    $id_rak      = (int)$_POST['id_rak'];
    $isbn        = $_POST['isbn'];
    $title       = $_POST['title'];
    $penerbit    = $_POST['penerbit'];
    $pengarang   = $_POST['pengarang'];
    $thn_buku    = $_POST['thn_buku'];
    $isi         = $_POST['isi'];
    $jml         = (int)$_POST['jml'];

    $gambar_baru = $data['gambar_buku'];
    if (!empty($_FILES['gambar_buku']['name'])) {
        $dir = "../asset/img//";
        if (!is_dir($dir)) mkdir($dir);
        $file_name = time() . "_" . basename($_FILES["gambar_buku"]["name"]);
        $target = $dir . $file_name;
        if (move_uploaded_file($_FILES["gambar_buku"]["tmp_name"], $target)) {
            if ($data['gambar_buku'] && file_exists("../asset/img//" . $data['gambar_buku'])) {
                unlink("../asset/img//" . $data['gambar_buku']);
            }
            $gambar_baru = $file_name;
        }
    }

    $stmt = $conn->prepare("UPDATE tbl_buku 
        SET buku_id=?, id_kategori=?, id_rak=?, isbn=?, title=?, penerbit=?, pengarang=?, thn_buku=?, isi=?, jml=?, gambar_buku=? 
        WHERE id_buku=?");
    $stmt->bind_param("siisssssissi", $buku_id, $id_kategori, $id_rak, $isbn, $title, $penerbit, $pengarang, $thn_buku, $isi, $jml, $gambar_baru, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data buku berhasil diperbarui.');window.location='buku_manage.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update: {$stmt->error}</div>";
    }
}


$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$sql = "
    SELECT b.*, k.nama_kategori 
    FROM tbl_buku b
    LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
";

if ($keyword !== '') {
    $sql .= " WHERE 
        b.buku_id LIKE '%$keyword%' OR
        b.title LIKE '%$keyword%' OR
        b.pengarang LIKE '%$keyword%' OR
        b.penerbit LIKE '%$keyword%' OR
        k.nama_kategori LIKE '%$keyword%'";
}

$sql .= " ORDER BY b.id_buku DESC";
$bukuList = $conn->query($sql);

?>

<div class="container my-4">
    <h3 class="mb-3 text-center">Manajemen Buku</h3>

    <?php if (isset($_GET['detail'])): ?>
        <div class="card p-4 shadow-sm">
            <h4 class="mb-3 text-center">Detail Buku</h4>
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php if ($detail['gambar_buku']): ?>
                        <img src="../asset/img//<?= htmlspecialchars($detail['gambar_buku']); ?>" class="img-fluid rounded mb-3" style="max-height:250px;">
                    <?php else: ?>
                        <div class="text-muted">Tidak ada gambar</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <p><strong>Kode Buku:</strong> <?= htmlspecialchars($detail['buku_id']); ?></p>
                    <p><strong>Judul:</strong> <?= htmlspecialchars($detail['title']); ?></p>
                    <p><strong>Kategori:</strong> <?= htmlspecialchars($detail['nama_kategori'] ?? '-'); ?></p>
                    <p><strong>Pengarang:</strong> <?= htmlspecialchars($detail['pengarang']); ?></p>
                    <p><strong>Penerbit:</strong> <?= htmlspecialchars($detail['penerbit']); ?></p>
                    <p><strong>Tahun:</strong> <?= htmlspecialchars($detail['thn_buku']); ?></p>
                    <p><strong>Jumlah:</strong> <?= htmlspecialchars($detail['jml']); ?></p>
                    <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($detail['isi'])); ?></p>
                    <a href="buku_manage.php" class="btn btn-secondary mt-2">Kembali</a>
                </div>
            </div>
        </div>
<?php elseif ($editMode): ?>
    <div class="card p-4 shadow-sm mb-4">
        <h4 class="mb-3 text-center">Edit Data Buku</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Buku</label>
                    <input type="text" name="buku_id" class="form-control" 
                           value="<?= htmlspecialchars($data['buku_id']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" 
                           value="<?= htmlspecialchars($data['isbn']); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Buku</label>
                <input type="text" name="title" class="form-control" 
                       value="<?= htmlspecialchars($data['title']); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="id_kategori" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $kategoriList2 = $conn->query("SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                        while ($kat = $kategoriList2->fetch_assoc()):
                        ?>
                            <option value="<?= $kat['id_kategori']; ?>" 
                                <?= ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ID Rak</label>
                    <input type="number" name="id_rak" class="form-control" 
                           value="<?= htmlspecialchars($data['id_rak']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" 
                           value="<?= htmlspecialchars($data['penerbit']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pengarang</label>
                    <input type="text" name="pengarang" class="form-control" 
                           value="<?= htmlspecialchars($data['pengarang']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tahun Buku</label>
                    <input type="text" name="thn_buku" class="form-control" 
                           value="<?= htmlspecialchars($data['thn_buku']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jumlah Buku</label>
                    <input type="number" name="jml" class="form-control" 
                           value="<?= htmlspecialchars($data['jml']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gambar Buku</label>
                    <?php if ($data['gambar_buku']): ?>
                        <div class="mb-2">
                            <img src="../asset/img//<?= htmlspecialchars($data['gambar_buku']); ?>" 
                                 width="80" class="rounded border">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="gambar_buku" class="form-control">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi / Isi Buku</label>
                <textarea name="isi" class="form-control" rows="4"><?= htmlspecialchars($data['isi']); ?></textarea>
            </div>

            <div class="text-center">
                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <a href="buku_manage.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>


    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
    <form method="get" class="d-flex" style="max-width: 350px;">
        <input type="text" name="keyword" class="form-control me-2" 
               placeholder="Cari buku..." 
               value="<?= htmlspecialchars($keyword ?? ''); ?>">
        <button type="submit" class="btn btn-outline-primary">Cari</button>
    </form>

    <a href="?tambah=true" class="btn btn-success">+ Tambah Buku</a>
</div>

<?= $pesan; ?>


        <?php if (isset($_GET['tambah'])): ?>
            <div class="card p-4 shadow-sm mb-4">
                <h4 class="mb-3 text-center">Tambah Buku Baru</h4>
                <form method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Buku</label>
                            <input type="text" name="buku_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $kategoriList2 = $conn->query("SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                                while ($kat = $kategoriList2->fetch_assoc()):
                                ?>
                                    <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Rak</label>
                            <input type="number" name="id_rak" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tahun Buku</label>
                            <input type="text" name="thn_buku" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Buku</label>
                            <input type="number" name="jml" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gambar Buku</label>
                            <input type="file" name="gambar_buku" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Isi Buku</label>
                        <textarea name="isi" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        <a href="buku_manage.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Kode Buku</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>Tahun</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $bukuList->fetch_assoc()): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center">
                        <?php if ($row['gambar_buku']): ?>
                            <img src="../asset/img/<?= htmlspecialchars($row['gambar_buku']); ?>" width="60">
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['buku_id']); ?></td>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($row['pengarang']); ?></td>
                    <td><?= htmlspecialchars($row['penerbit']); ?></td>
                    <td><?= htmlspecialchars($row['thn_buku']); ?></td>
                    <td><?= htmlspecialchars($row['jml']); ?></td>
                    <td class="text-center">
                        <a href="?detail=<?= $row['id_buku']; ?>" class="btn btn-primary btn-sm">Detail</a>
                        <a href="?edit=<?= $row['id_buku']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?hapus=<?= $row['id_buku']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus buku ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
