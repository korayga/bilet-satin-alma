<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sadece firma admin yetkisi kontrolü
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'firmadmin') {
    header('Location: ../../public/login.php');
    exit;
}

$firm_id = $_SESSION['firm_id'] ?? null;

if (!$firm_id) {
    $_SESSION['error'] = 'Firma bilgisi bulunamadı.';
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $discount_percentage = floatval($_POST['discount_percentage'] ?? 0);
            $usage_limit = intval($_POST['usage_limit'] ?? 0);
            $expire_date = $_POST['expire_date'] ?? '';
            
            // Validasyon
            if (empty($code)) {
                $_SESSION['error'] = 'Kupon kodu gereklidir.';
                break;
            }
            
            if (strlen($code) < 4 || strlen($code) > 20) {
                $_SESSION['error'] = 'Kupon kodu 4-20 karakter arasında olmalıdır.';
                break;
            }
            
            if (!preg_match('/^[A-Z0-9]+$/', $code)) {
                $_SESSION['error'] = 'Kupon kodu sadece büyük harf ve rakam içerebilir.';
                break;
            }
            
            if ($discount_percentage <= 0 || $discount_percentage > 100) {
                $_SESSION['error'] = 'İndirim oranı 1-100 arasında olmalıdır.';
                break;
            }
            
            if ($usage_limit < 1) {
                $_SESSION['error'] = 'Kullanım limiti en az 1 olmalıdır.';
                break;
            }
            
            if (strtotime($expire_date) < time()) {
                $_SESSION['error'] = 'Son kullanma tarihi geçmiş olamaz.';
                break;
            }
            
            // Kupon kodu benzersiz mi kontrolü
            $db = getDB();
            $check = $db->prepare("SELECT id FROM coupons WHERE code = :code");
            $check->execute([':code' => $code]);
            
            if ($check->fetch()) {
                $_SESSION['error'] = 'Bu kupon kodu zaten kullanılıyor.';
                break;
            }
            
            // Backend fonksiyonunu kullan
            $result = create_coupon([
                'code' => $code,
                'discount_percentage' => $discount_percentage,
                'usage_limit' => $usage_limit,
                'expire_date' => $expire_date,
                'firm_id' => $firm_id
            ]);
            
            if ($result) {
                $_SESSION['success'] = 'Kupon başarıyla oluşturuldu.';
            } else {
                $_SESSION['error'] = 'Kupon oluşturulurken bir hata oluştu.';
            }
            break;
            
        case 'update':
            $coupon_id = intval($_POST['coupon_id'] ?? 0);
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $discount_percentage = floatval($_POST['discount_percentage'] ?? 0);
            $usage_limit = intval($_POST['usage_limit'] ?? 0);
            $expire_date = $_POST['expire_date'] ?? '';
            
            if (empty($coupon_id)) {
                $_SESSION['error'] = 'Kupon ID gereklidir.';
                break;
            }
            
            // Yetki kontrolü - Bu kupon bu firmaya ait mi?
            $coupon = get_coupon_by_id($coupon_id);
            if (!$coupon || $coupon['firm_id'] != $firm_id) {
                $_SESSION['error'] = 'Bu kuponu güncelleme yetkiniz yok.';
                break;
            }
            
            // Validasyon
            if ($discount_percentage <= 0 || $discount_percentage > 100) {
                $_SESSION['error'] = 'İndirim oranı 1-100 arasında olmalıdır.';
                break;
            }
            
            // Kullanım sayısından az limit olamaz
            if ($usage_limit < $coupon['used_count']) {
                $_SESSION['error'] = "Bu kupon {$coupon['used_count']} kez kullanıldı. Kullanım limiti bundan az olamaz.";
                break;
            }
            
            // Backend fonksiyonunu kullan
            $result = update_coupon($coupon_id, [
                'code' => $code,
                'discount_percentage' => $discount_percentage,
                'usage_limit' => $usage_limit,
                'expire_date' => $expire_date
            ]);
            
            if ($result) {
                $_SESSION['success'] = 'Kupon başarıyla güncellendi.';
            } else {
                $_SESSION['error'] = 'Kupon güncellenirken bir hata oluştu.';
            }
            break;
            
        case 'delete':
            $coupon_id = intval($_POST['coupon_id'] ?? 0);
            
            if (empty($coupon_id)) {
                $_SESSION['error'] = 'Kupon ID gereklidir.';
                break;
            }
            
            // Yetki kontrolü + Kullanıldı mı kontrolü
            $coupon = get_coupon_by_id($coupon_id);
            if (!$coupon || $coupon['firm_id'] != $firm_id) {
                $_SESSION['error'] = 'Bu kuponu silme yetkiniz yok.';
                break;
            }
            
            if ($coupon['used_count'] > 0) {
                $_SESSION['error'] = "Bu kupon {$coupon['used_count']} kez kullanıldı. Silinemiyor.";
                break;
            }
            
            // Backend fonksiyonunu kullan
            $result = delete_coupon($coupon_id);
            
            if ($result) {
                $_SESSION['success'] = 'Kupon başarıyla silindi.';
            } else {
                $_SESSION['error'] = 'Kupon silinirken bir hata oluştu.';
            }
            break;
            
        default:
            $_SESSION['error'] = 'Geçersiz işlem.';
            break;
    }
} catch (Exception $e) {
    error_log("Kupon işlemi hatası: " . $e->getMessage());
    $_SESSION['error'] = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
}

header('Location: ../coupons.php');
exit;
