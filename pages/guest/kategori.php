<?php
session_start();
require '../config/db.php';


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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori Buku - Guest</title>
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
        .table {
            background: white;
        }
    </style>
</head>
<body>


<!-- SIDEBAR -->
<div class="sidebar">
    <h4>📚 Sistem Perpustakaan</h4>
    <a href="buku.php">📘 Buku</a>
    <a href="kategori.php" class="active">📖 Kategori</a>
    <a href="daftar_member.php">👤 Daftar Member</a>
    <button onclick="location.href='../admin/login.php'" class="btn btn-login">Logout</button>
</div>


<!-- KONTEN -->
<div class="content">
    <h3 class="text-primary fw-bold mb-4 text-center">📖 Kategori Buku</h3>

    <!-- TABEL KATEGORI -->
    <table class="table table-bordered table-striped align-middle shadow-sm">
        <thead class="table-primary text-center">
            <tr>
                <th width="60">No</th>
                <th>Nama Kategori</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                <td class="text-center">
                    <a href="?lihat=<?= $row['id_kategori']; ?>" class="btn btn-info btn-sm">Lihat Buku</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (!empty($bukuKategori)): ?>
        <hr>
        <h4 class="mt-4 text-center text-dark">
            Daftar Buku dalam Kategori: 
            <span class="text-primary"><?= htmlspecialchars($kategoriNama); ?></span>
        </h4>

        <table class="table table-bordered table-striped mt-3 align-middle shadow-sm">
            <thead class="table-secondary text-center">
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
                            <img src="../asset/img/<?= htmlspecialchars($b['gambar_buku']); ?>" width="60" class="rounded">
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
        <div class="alert alert-warning mt-3 text-center">
            Tidak ada buku dalam kategori ini.
        </div>
    <?php endif; ?>
</div>

</body>
</html>
