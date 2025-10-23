<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/auth.php';

// Sadece Admin kontrolü (Firma Admin'ler firm_admin/ klasörüne yönlendirilir)
if (!is_logged_in() || !is_admin()) {
    if (is_firm_admin()) {
        header('Location: ../firm_admin/index.php');
        exit();
    }
    header('Location: ../public/login.php?message=Yetkisiz erişim');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrendWay Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --turuncu: #FF6B35;
            --siyah: #2C3E50;
            --gri: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: var(--siyah);
            color: white;
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-logo h4 {
            color: var(--turuncu);
            margin: 0;
        }
        
        .sidebar-logo small {
            color: #bdc3c7;
            font-size: 12px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--turuncu);
            color: white;
            border-left: 4px solid white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--siyah);
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--turuncu);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--turuncu);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--siyah);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .btn-turuncu {
            background: var(--turuncu);
            color: white;
            border: none;
        }
        
        .btn-turuncu:hover {
            background: #e55a2b;
            color: white;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .badge-aktif {
            background: #27ae60;
            color: white;
        }
        
        .badge-pasif {
            background: #e74c3c;
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <h4><i class="fas fa-bus"></i> TrendWay</h4>
            <small>Admin Paneli</small>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="<?= $current_page == 'index' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="firms.php" class="<?= $current_page == 'firms' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> Firmalar
                </a>
            </li>
            <li>
                <a href="users.php" class="<?= $current_page == 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Kullanıcılar
                </a>
            </li>
            <li>
                <a href="trips.php" class="<?= $current_page == 'trips' ? 'active' : '' ?>">
                    <i class="fas fa-bus"></i> Tüm Seferler
                </a>
            </li>
            <li>
                <a href="coupons.php" class="<?= $current_page == 'coupons' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Global Kuponlar
                </a>
            </li>
          
            <li>
                <a href="../public/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h1 class="page-title">
                <?php
                $titles = [
                    'index' => '📊 Dashboard',
                    'firms' => '🏢 Firma Yönetimi',
                    'users' => '👥 Kullanıcı Yönetimi',
                    'coupons' => '🎟️ Global Kupon Yönetimi',
                    'trips' => '🚌 Tüm Seferler'
                ];
                echo $titles[$current_page] ?? 'Admin Panel';
                ?>
            </h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['tam_isim'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight: bold;"><?= htmlspecialchars($_SESSION['tam_isim'] ?? 'Admin') ?></div>
                    <small class="text-muted">Sistem Yöneticisi</small>
                </div>
            </div>
        </div>
