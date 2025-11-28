<?php
session_start();
if (!isset($_SESSION['id_login']) && (!isset($_SESSION['level']) || $_SESSION['level'] !== 'Guest')) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['level']) && $_SESSION['level'] !== 'Petugas' && $_SESSION['level'] !== 'Guest') {
    header("Location: login.php");
    exit();
}
?>
