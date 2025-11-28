<?php include 'auth_check.php'; ?>
<?php
require '../config/db.php';
$pageTitle = "Dashboard";
include '../includes/header.php';


$cekPinjam = $conn->query("SHOW TABLES LIKE 'tbl_pinjam'");
$totalPinjam = 0;
if ($cekPinjam && $cekPinjam->num_rows > 0) {
  $totalPinjam = $conn->query("SELECT COUNT(*) AS total FROM tbl_pinjam")->fetch_assoc()['total'] ?? 0;
}


$totalBuku = $conn->query("SELECT COUNT(*) AS total FROM tbl_buku")->fetch_assoc()['total'] ?? 0;
$totalKategori = $conn->query("SELECT COUNT(*) AS total FROM tbl_kategori")->fetch_assoc()['total'] ?? 0;


$cekMember = $conn->query("SHOW TABLES LIKE 'tbl_member'");
$totalMember = 0;
if ($cekMember && $cekMember->num_rows > 0) {
    
    $totalMember = $conn->query("SELECT COUNT(*) AS total FROM tbl_member")->fetch_assoc()['total'] ?? 0;
}
?>

<div class="container-fluid mt-4">
  <h2 class="fw-bold text-primary mb-4">📚 Dashboard Perpustakaan</h2>

  <div class="row text-center">
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm p-4 bg-light">
        <h4 class="text-primary fw-bold"><?= $totalBuku ?></h4>
        <p class="text-muted mb-2">Total Buku</p>
        <a href="buku_manage.php" class="btn btn-primary btn-sm">Lihat Buku</a>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm p-4 bg-light">
        <h4 class="text-success fw-bold"><?= $totalKategori ?></h4>
        <p class="text-muted mb-2">Total Kategori</p>
        <a href="kategori.php" class="btn btn-success btn-sm">Lihat Kategori</a>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm p-4 bg-light">
        <h4 class="text-warning fw-bold"><?= $totalMember ?></h4>
        <p class="text-muted mb-2">Total Member</p>
        <a href="rak.php" class="btn btn-warning btn-sm">Lihat Member</a>
      </div>
    </div>
  </div>

  <div class="mt-5 text-center">
    <h5 class="fw-semibold">Selamat datang di Sistem Informasi Perpustakaan!</h5>
    <p class="text-muted">Kelola data buku, kategori, anggota, dan peminjaman dengan mudah.</p>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
