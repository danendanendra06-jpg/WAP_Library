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
   <style>
    body {
        background: linear-gradient(135deg, #004e92, #000428);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-container {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        width: 370px;
        padding: 30px 25px;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .login-container:hover {
        transform: translateY(-5px);
    }

    h4 {
        color: #004e92;
        font-weight: 700;
        margin-bottom: 25px;
    }

    label {
        font-weight: 500;
        color: #333;
        text-align: left;
        display: block;
        margin-bottom: 5px;
    }

    input.form-control {
        border-radius: 10px;
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 15px;
        transition: 0.2s;
    }

    input.form-control:focus {
        border-color: #0467beff;
        box-shadow: 0 0 5px rgba(0,78,146,0.4);
    }

    .btn-login {
        background: linear-gradient(90deg, #0467beff, #007aff);
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 10px;
        padding: 10px;
        transition: background 0.3s;
    }

    .btn-login:hover {
        background: linear-gradient(90deg, #0467beff, #005ecb);
    }

    .btn-guest {
        border: 2px solid #0467beff;
        color: ##0467beff;
        border-radius: 10px;
        font-weight: 500;
        padding: 10px;
        background-color: transparent;
        transition: all 0.3s ease;
    }

    .btn-guest:hover {
        background: #0467beff;
        color: white;
    }

    a {
        color: #0467beff;
        font-size: 14px;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    .alert {
        font-size: 14px;
    }
</style>

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
