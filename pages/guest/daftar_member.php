<?php
session_start();
require '../config/db.php';

$success = "";
$error = "";

if (isset($_POST['daftar'])) {
  
    $nama = trim($_POST['nama']);
    $user = trim($_POST['username']);
    $alamat = trim($_POST['alamat']);
    $telp = trim($_POST['telepon']);
    
   

    try {
        $cek = $conn->prepare("SELECT username FROM tbl_member WHERE username=?");
        $cek->bind_param("s", $user);
        $cek->execute();
        $result = $cek->get_result();
        $cek->close(); 

        if ($result->num_rows > 0) {
            $error = "Username sudah digunakan! Silakan ganti username.";
        } else {
          
            $stmt_member = $conn->prepare("INSERT INTO tbl_member (nama_lengkap, username, alamat, nomor_telepon) VALUES (?, ?, ?, ?)");
            
            $stmt_member->bind_param("ssss", $nama, $user, $alamat, $telp);
            
            if ($stmt_member->execute()) {
                $success = "Pendaftaran berhasil! Data telah disimpan.";
            } else {
                $error = "Gagal mendaftar: " . $stmt_member->error;
            }
            $stmt_member->close();
        }
    } catch (Exception $e) {
        $error = "Pendaftaran gagal: Terjadi kesalahan database.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>👤 Daftar Member - Guest</title>
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
        .form-box {
            background: #fff;
            border-radius: 10px;
            padding: 25px 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            max-width: 550px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>📚 Sistem Perpustakaan</h4>
    <a href="buku.php">📘 Buku</a>
    <a href="kategori.php">📖 Kategori</a>
    <a href="daftar_member.php" class="active">👤 Daftar Member</a>
    <button onclick="location.href='../admin/login.php'" class="btn btn-login">Logout</button>
</div>

<div class="content">
    <h3 class="text-primary fw-bold mb-4 text-center">👤 Form Pendaftaran Member</h3>

    <div class="form-box">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="telepon" class="form-control" required>
            </div>

            <button type="submit" name="daftar" class="btn btn-primary w-100 fw-semibold">Daftar Sekarang</button>
        </form>
    </div>
</div>

</body>
</html>