<?php
include "../config/db.php";


$message = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $new_password = md5($_POST['new_password']);

    $sql = "UPDATE tbl_petugas SET pass = ? WHERE user = ? AND level = 'Petugas'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $new_password, $username);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message = "Password berhasil diubah!";
    } else {
        $message = "Username tidak ditemukan!";
    }
}
?>=
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h4 class="text-center mb-3">Reset Password</h4>
                <?php if ($message) : ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100">Reset Password</button>
                    <a href="login.php" class="btn btn-outline-secondary w-100 mt-2">Kembali ke Login</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
