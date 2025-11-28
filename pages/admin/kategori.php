<?php include 'auth_check.php'; ?>
<?php
require '../config/db.php';
include '../includes/header.php';



if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kategori']);
    if ($nama != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_kategori (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama);
        if ($stmt->execute()) {
            header("Location: kategori.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Gagal menambah kategori: " . $stmt->error . "</div>";
        }
    }
}


if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM tbl_kategori WHERE id_kategori=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: kategori.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Gagal menghapus kategori: " . $stmt->error . "</div>";
    }
}

$result = $conn->query("SELECT * FROM tbl_kategori ORDER BY id_kategori DESC");


$bukuKategori = [];
$kategoriNama = "";
if (isset($_GET['lihat'])) {
    $id_kat = (int)$_GET['lihat'];
    $katQ = $conn->query("SELECT nama_kategori FROM tbl_kategori WHERE id_kategori=$id_kat");
    if ($katQ && $katQ->num_rows > 0) {
        $kategoriNama = $katQ->fetch_assoc()['nama_kategori'];
    }

    $bukuQ = $conn->query("
        SELECT b.*, k.nama_kategori 
        FROM tbl_buku b
        LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
        WHERE b.id_kategori = $id_kat
        ORDER BY b.id_buku DESC
    ");
    while ($r = $bukuQ->fetch_assoc()) {
        $bukuKategori[] = $r;
    }
}
?>

<div class="container my-4">
    <h3>Kategori Buku</h3>

   
    <form method="post" class="mb-3 d-flex gap-2">
        <input type="text" name="nama_kategori" class="form-control" placeholder="Tambah kategori baru..." required>
        <button class="btn btn-primary" name="tambah">Tambah</button>
    </form>

  
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                <td class="text-center">
                    <a href="?lihat=<?= $row['id_kategori']; ?>" class="btn btn-info btn-sm">Lihat Buku</a>
                    <a href="?hapus=<?= $row['id_kategori']; ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (!empty($bukuKategori)): ?>
        <hr>
        <h4 class="mt-4">Daftar Buku dalam Kategori: <span class="text-primary"><?= htmlspecialchars($kategoriNama); ?></span></h4>

        <table class="table table-bordered table-striped mt-3 align-middle">
            <thead class="table-secondary">
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Kode Buku</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>Tahun</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach($bukuKategori as $b): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center">
                        <?php if ($b['gambar_buku']): ?>
                            <img src="../asset/img//<?= htmlspecialchars($b['gambar_buku']); ?>" width="60">
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($b['buku_id']); ?></td>
                    <td><?= htmlspecialchars($b['title']); ?></td>
                    <td><?= htmlspecialchars($b['pengarang']); ?></td>
                    <td><?= htmlspecialchars($b['penerbit']); ?></td>
                    <td><?= htmlspecialchars($b['thn_buku']); ?></td>
                    <td><?= htmlspecialchars($b['jml']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['lihat'])): ?>
        <hr>
        <div class="alert alert-warning mt-3">
            Tidak ada buku dalam kategori ini.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
