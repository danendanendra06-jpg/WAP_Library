<?php
session_start();
include "../config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']); 

    $sql = "SELECT * FROM tbl_petugas WHERE user = ? AND level = 'Petugas'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        if ($password === $data['pass']) {
            $_SESSION['id_login'] = $data['id_login'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['level'] = $data['level'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan atau bukan petugas!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/CSS_login/style.css">

</head>
<body>
    <div class="login-container">
        <h4>Login Admin</h4>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="text-start">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>

                <div class="d-flex justify-content-between mb-3">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login w-100 mb-3">Login</button>
            <a href="../guest/index.php" class="btn btn-guest w-100">Masuk sebagai Guest</a>
        </form>
    </div>
</body>
</html>
