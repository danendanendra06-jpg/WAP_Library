<?php
session_start();
require '../config/db.php';


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


$detail = null;
if (isset($_GET['detail'])) {
    $id = (int)$_GET['detail'];
    $result = $conn->query("
        SELECT b.*, k.nama_kategori 
        FROM tbl_buku b
        LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
        WHERE b.id_buku=$id
    ");
    if ($result && $result->num_rows > 0) {
        $detail = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Buku - Guest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 220px;
            background: #0d47a1;
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .sidebar h4 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }
        .sidebar a {
            color: #e3f2fd;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #1565c0;
            color: #fff;
            font-weight: 600;
        }
        .sidebar .btn-login {
            margin-top: auto;
            margin: 20px;
            background: #ffca28;
            color: #000;
            font-weight: 600;
            border: none;
            width: calc(100% - 40px);
        }
        .sidebar .btn-login:hover {
            background: #ffc107;
        }
        .content {
            flex: 1;
            padding: 40px;
        }
        table img {
            border-radius: 5px;
        }
        .table {
            background: white;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4>📚 Sistem Perpustakaan</h4>
        <a href="buku.php" class="active">📘 Buku</a>
        <a href="kategori.php">📖 Kategori</a>
        <a href="daftar_member.php">👤 Daftar Member</a>
        <button onclick="location.href='../admin/login.php'" class="btn btn-login">Logout</button>
    </div>

    <!-- KONTEN -->
    <div class="content">
        <h3 class="text-primary fw-bold mb-4 text-center">📘 Daftar Buku</h3>

        <?php if ($detail): ?>
        <div class="card shadow-sm p-4">
            <h4 class="text-center mb-3">Detail Buku</h4>
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php if ($detail['gambar_buku']): ?>
                        <img src="../asset/img/<?= htmlspecialchars($detail['gambar_buku']); ?>" 
                             class="img-fluid rounded mb-3" style="max-height:250px;">
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
                    <a href="buku.php" class="btn btn-secondary mt-2">Kembali</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="get" class="d-flex" style="max-width: 350px;">
                <input type="text" name="keyword" class="form-control me-2" 
                       placeholder="Cari buku..." value="<?= htmlspecialchars($keyword ?? ''); ?>">
                <button type="submit" class="btn btn-outline-primary">Cari</button>
            </form>
        </div>

        <table class="table table-bordered table-striped align-middle shadow-sm">
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
                    <th>Detail</th>
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
                        <a href="?detail=<?= $row['id_buku']; ?>" class="btn btn-info btn-sm">Lihat</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</body>
</html>
