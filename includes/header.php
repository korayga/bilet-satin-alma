<?php                                              
require_once '../config/config.php';
require_once '../config/auth.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRENDWAY BİLET - Güvenilir Online Bilet Satış Platformu</title>
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS (5.3.3) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    
    <!-- -->
    <header class="mb-5">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid container-xl">
               
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-ticket-alt me-2"></i>TRENDWAY BİLET
                </a>
                
             
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Menü Öğeleri -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Ana Sayfa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="route_detail.php">Seferler</a>
                        </li>
                        
                        <?php if (is_logged_in()): ?>
                            <!-- Giriş yapmış kullanıcı menüsü -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?= htmlspecialchars($_SESSION['tam_isim'] ?? 'Kullanıcı') ?>
                                </a>
                                <ul class="dropdown-menu">
                        
                                    <li><a class="dropdown-item text-dark" href="my_tickets.php">
                                        <i class="bi bi-ticket me-2"></i>Biletlerim
                                    </a></li>
                                    <li><a class="dropdown-item text-dark" href="profile.php">
                                        <i class="bi bi-person-gear me-2"></i>Hesabım
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-dark" href="logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
                                    </a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Misafir kullanıcı menüsü -->
                            <li class="nav-item">
                                <a class="btn btn-siyah btn-sm ms-2" href="login.php">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-turuncu btn-sm ms-2" href="register.php">
                                    <i class="bi bi-person-plus me-1"></i>Üye Ol
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- ANA İÇERİK BAŞLANGICI -->
    <main class="container-xl flex-grow-1">
        <!-- İçerik bu noktadan sonra gelecek -->
