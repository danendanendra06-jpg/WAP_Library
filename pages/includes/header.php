<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    Perpustakaan - <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?>
  </title>

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../asset/css/style.css" rel="stylesheet">
</head>

<body>


<nav class="navbar navbar-dark bg-primary shadow-sm fixed-top">
  <div class="container-fluid d-flex justify-content-between align-items-center px-4">
    <a class="navbar-brand fw-bold text-white" href="../admin/index.php">
      📚 Sistem Perpustakaan
    </a>
    <a href="../admin/logout.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</nav>


<div class="sidebar">
  <ul class="nav flex-column p-3">
    <?php 
      $navItems = [
        '../admin/index.php' => 'Dashboard',
        '../admin/buku_manage.php' => 'Buku',
        '../admin/kategori.php' => 'Kategori',
        '../admin/petu&mem_manage.php' => 'Petugas/Member',
        '../admin/pinjam_manage.php' => 'Peminjaman',
        '../admin/denda_manage.php' => 'Denda'
      ];
      $currentPage = basename($_SERVER['PHP_SELF']);
      foreach ($navItems as $file => $label):
    ?>
      <li class="nav-item mb-2">
        <a href="<?= $file ?>" 
           class="nav-link <?= $currentPage == $file ? 'active bg-light text-primary fw-bold' : 'text-white'; ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<div class="main-content">



<script>
document.addEventListener("DOMContentLoaded", () => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});
</script>
