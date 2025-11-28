<?php
include 'auth_check.php';
require '../config/db.php';
include '../includes/header.php';

// ====== HAPUS PETUGAS ======
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if ($conn->query("DELETE FROM tbl_petugas WHERE id_login='$id'")) {
        echo "<script>alert('Data petugas berhasil dihapus!');window.location='petu&mem_manage.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data petugas!');window.location='petu&mem_manage.php';</script>";
    }
    exit;
}

// ====== HAPUS MEMBER ======
if (isset($_GET['hapus_member'])) {
    $id = $_GET['hapus_member'];
    if ($conn->query("DELETE FROM tbl_member WHERE id_member='$id'")) {
        echo "<script>alert('Data member berhasil dihapus!');window.location='petu&mem_manage.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data member!');window.location='petu&mem_manage.php';</script>";
    }
    exit;
}

// ====== TAMBAH DATA (PETUGAS / MEMBER) ======
if (isset($_POST['simpan_data'])) {
    $role = $_POST['role'];

    if ($role == 'Petugas') {
        $idNew = "AG" . rand(100, 999);
        $nama = $_POST['nama'];
        $user = $_POST['user'];
        $pass = md5($_POST['pass']);
        $telepon = $_POST['telepon'];
        $email = $_POST['email'];

        $sql = "INSERT INTO tbl_petugas (anggota_id, user, pass, level, nama, telepon, email)
                VALUES ('$idNew', '$user', '$pass', 'Petugas', '$nama', '$telepon', '$email')";
    } else {
        $idNew = "MB" . rand(100, 999);
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $alamat = $_POST['alamat'];
        $telepon = $_POST['telepon'];

        $sql = "INSERT INTO tbl_member (id_member, nama_lengkap, username, alamat, nomor_telepon)
                VALUES ('$idNew', '$nama', '$username', '$alamat', '$telepon')";
    }

    if ($conn->query($sql)) {
        echo "<script>alert('Data berhasil ditambahkan!');window.location='petu&mem_manage.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan data!</div>";
    }
    exit;
}
?>

<div class="container my-4">
    <h3>Manajemen Data Petugas & Member</h3>

    <!-- ==== FORM TAMBAH DATA  ==== -->
    <a href="?tambah=true" class="btn btn-primary mb-3">+ Tambah Data</a>

    <?php if (isset($_GET['tambah'])): ?>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" id="roleSelect" onchange="toggleRoleFields()" required>
                    <option value="">-- Pilih --</option>
                    <option value="Petugas">Petugas</option>
                    <option value="Member">Member</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <!-- === FIELD PETUGAS === -->
            <div id="petugasFields" style="display:none;">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="user" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="pass" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Telepon</label>
                    <input type="text" name="telepon" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>

            <!-- === FIELD MEMBER === -->
            <div id="memberFields" style="display:none;">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Nomor Telepon</label>
                    <input type="text" name="telepon" class="form-control">
                </div>
            </div>

            <button type="submit" name="simpan_data" class="btn btn-success">Simpan</button>
            <a href="petu&mem_manage.php" class="btn btn-secondary">Batal</a>
        </form>
    <?php endif; ?>

    <!-- ===== TABEL PETUGAS ===== -->
    <h4 class="mt-4 text-primary">📋 Data Petugas</h4>
    <?php $petugas = $conn->query("SELECT * FROM tbl_petugas ORDER BY id_login DESC"); ?>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-info">
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($petugas->num_rows > 0): $no=1; while($row=$petugas->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['anggota_id']); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['user']); ?></td>
                    <td><?= htmlspecialchars($row['telepon']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td>
                        <a href="?hapus=<?= $row['id_login']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center">Belum ada data petugas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ===== TABEL MEMBER ===== -->
    <h4 class="mt-5 text-success">👤 Data Member</h4>
    <?php $anggota = $conn->query("SELECT * FROM tbl_member ORDER BY id_member DESC"); ?>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-success">
            <tr>
                <th>No</th>
                <th>ID Member</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($anggota->num_rows > 0): $no=1; while($row=$anggota->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['id_member']); ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td><?= htmlspecialchars($row['nomor_telepon']); ?></td>
                    <td>
                        <a href="?hapus_member=<?= $row['id_member']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center">Belum ada data member.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleRoleFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('petugasFields').style.display = (role === 'Petugas') ? 'block' : 'none';
    document.getElementById('memberFields').style.display = (role === 'Member') ? 'block' : 'none';
}
</script>
