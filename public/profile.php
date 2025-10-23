<?php
require_once __DIR__ . '/../config/auth.php';

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Profil güncelleme
if ($_POST && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    if (!empty($full_name) && !empty($email)) {
        $db = getDB();
        try {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $_SESSION['kullanici_id']]);
            
            $_SESSION['tam_isim'] = $full_name;
            $_SESSION['email'] = $email;
            $success_message = "Profil bilgileriniz güncellendi!";
        } catch (PDOException $e) {
            $error_message = "Güncelleme sırasında hata oluştu.";
        }
    }
}

// Şifre değiştirme
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_message = "Yeni şifreler eşleşmiyor!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Şifre en az 6 karakter olmalıdır!";
    } else {
        $db = getDB();
        try {
            // Mevcut şifreyi kontrol et
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['kullanici_id']]);
            $user = $stmt->fetch();
            
            if (password_verify($current_password, $user['password'])) {
                // Yeni şifreyi güncelle
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['kullanici_id']]);
                $success_message = "Şifreniz başarıyla güncellendi!";
            } else {
                $error_message = "Mevcut şifreniz hatalı!";
            }
        } catch (PDOException $e) {
            $error_message = "Şifre güncelleme sırasında hata oluştu.";
        }
    }
}

// Bilet istatistikleri için veri çek
$user_tickets = get_user_tickets($_SESSION['kullanici_id']);
$cancelled_tickets = array_filter($user_tickets, fn($t) => $t['status'] === 'cancelled');
$active_tickets = array_filter($user_tickets, fn($t) => $t['status'] === 'active');

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-brand fw-bold">Profil Ayarları</h2>
                <a href="my_tickets.php" class="btn btn-siyah">
                    <i class="bi bi-arrow-left me-2"></i>Biletlerime Dön
                </a>
            </div>

            <!-- Mesajlar -->
            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= $success_message ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profil Bilgileri -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person me-2"></i>Profil Bilgileri
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ad Soyad</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['tam_isim']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">E-posta</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['kullanici_adi']) ?>" disabled>
                                    <div class="form-text">Kullanıcı adı değiştirilemez.</div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-turuncu w-100">
                                    <i class="bi bi-check-lg me-2"></i>Bilgileri Güncelle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Şifre Değiştirme -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Şifre Değiştirme
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mevcut Şifre</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Yeni Şifre</label>
                                    <input type="password" name="new_password" class="form-control" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Yeni Şifre (Tekrar)</label>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           minlength="6" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-siyah w-100">
                                    <i class="bi bi-shield-check me-2"></i>Şifreyi Güncelle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hesap Bilgileri -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-wallet2 me-2"></i>Hesap Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-brand mb-1">₺<?= number_format($_SESSION['bakiye'] ?? 0, 2) ?></h4>
                                <small class="text-muted">Mevcut Bakiye</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-danger mb-1"><?= count($cancelled_tickets) ?></h4>
                                <small class="text-muted">İptal Edilen Bilet</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success mb-1"><?= count($active_tickets) ?></h4>
                                <small class="text-muted">Aktif Bilet</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>