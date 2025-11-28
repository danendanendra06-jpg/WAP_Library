<?php 
session_start();


if (!isset($_SESSION['level'])) {
    $_SESSION['level'] = 'Guest';
    $_SESSION['nama'] = 'Guest';
}


header("Location: buku.php");
exit;
?>
