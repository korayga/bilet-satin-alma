<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sadece firma admin yetkisi kontrolü
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'firmadmin') {
    header('Location: ../public/login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Panel - TrendWay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Firma Yönetim Paneli
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'trips' ? 'active' : '' ?>" href="trips.php">
                            <i class="bi bi-bus-front"></i> Seferlerim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'coupons' ? 'active' : '' ?>" href="coupons.php">
                            <i class="bi bi-ticket-perforated"></i> Kuponlarım
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'reports' ? 'active' : '' ?>" href="reports.php">
                            <i class="bi bi-graph-up"></i> Raporlar
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Firma Admin') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4">
