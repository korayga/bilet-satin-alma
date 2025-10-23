<?php
session_start();
require_once '../../config/auth.php';

// Sadece admin erişebilir
if (!is_logged_in() || !is_admin()) {
    header('Location: ../../public/login.php?message=Yetkisiz erişim');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$coupon_id = $_POST['coupon_id'] ?? $_GET['id'] ?? null;

// ========================================
// KUPON EKLEME (CREATE)
// ========================================
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'code' => strtoupper(trim($_POST['code'])),
            'discount_percentage' => floatval($_POST['discount_percentage']),
            'usage_limit' => intval($_POST['usage_limit']),
            'expire_date' => $_POST['expire_date'],
            'firm_id' => null // Admin için her zaman global kupon (firm_id = NULL)
        ];
        
        // Validasyon
        if (empty($data['code'])) {
            throw new Exception('Kupon kodu gereklidir');
        }
        
        if (strlen($data['code']) < 4 || strlen($data['code']) > 20) {
            throw new Exception('Kupon kodu 4-20 karakter arasında olmalıdır');
        }
        
        if (!preg_match('/^[A-Z0-9]+$/', $data['code'])) {
            throw new Exception('Kupon kodu sadece büyük harf ve rakam içerebilir');
        }
        
        if ($data['discount_percentage'] <= 0 || $data['discount_percentage'] > 100) {
            throw new Exception('İndirim oranı 1-100 arasında olmalıdır');
        }
        
        if ($data['usage_limit'] < 1) {
            throw new Exception('Kullanım limiti en az 1 olmalıdır');
        }
        
        if (strtotime($data['expire_date']) < time()) {
            throw new Exception('Son kullanma tarihi geçmiş olamaz');
        }
        
        // Aynı kod var mı kontrol et
        $db = getDB();
        $check = $db->prepare("SELECT id FROM coupons WHERE code = :code");
        $check->execute([':code' => $data['code']]);
        if ($check->fetch()) {
            throw new Exception('Bu kupon kodu zaten kullanılıyor');
        }
        
        // Backend fonksiyonunu kullan
        $result = create_coupon($data);
        
        if ($result) {
            header('Location: ../coupons.php?message=Global kupon başarıyla oluşturuldu&type=success');
            exit();
        } else {
            throw new Exception('Kupon oluşturulurken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../coupons.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// KUPON GÜNCELLEME (UPDATE)
// ========================================
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($coupon_id)) {
            throw new Exception('Kupon ID gereklidir');
        }
        
        // Mevcut kuponu getir
        $coupon = get_coupon_by_id($coupon_id);
        if (!$coupon) {
            throw new Exception('Kupon bulunamadı');
        }
        
        $data = [
            'code' => strtoupper(trim($_POST['code'])),
            'discount_percentage' => floatval($_POST['discount_percentage']),
            'usage_limit' => intval($_POST['usage_limit']),
            'expire_date' => $_POST['expire_date']
        ];
        
        // Validasyon
        if ($data['discount_percentage'] <= 0 || $data['discount_percentage'] > 100) {
            throw new Exception('İndirim oranı 1-100 arasında olmalıdır');
        }
        
        // Kullanım sayısından az limit olamaz
        if ($data['usage_limit'] < $coupon['used_count']) {
            throw new Exception("Bu kupon {$coupon['used_count']} kez kullanıldı. Kullanım limiti bundan az olamaz.");
        }
        
        // Backend fonksiyonunu kullan
        $result = update_coupon($coupon_id, $data);
        
        if ($result) {
            header('Location: ../coupons.php?message=Kupon başarıyla güncellendi&type=success');
            exit();
        } else {
            throw new Exception('Kupon güncellenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../coupons.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// KUPON SİLME (DELETE)
// ========================================
elseif ($action === 'delete' && !empty($coupon_id)) {
    try {
        // Mevcut kuponu getir
        $coupon = get_coupon_by_id($coupon_id);
        if (!$coupon) {
            throw new Exception('Kupon bulunamadı');
        }
        
        // Kullanılmış mı kontrol et
        if ($coupon['used_count'] > 0) {
            throw new Exception("Bu kupon {$coupon['used_count']} kez kullanıldı. Silinemiyor. (Son kullanma tarihini değiştirerek devre dışı bırakabilirsiniz)");
        }
        
        // Backend fonksiyonunu kullan
        $result = delete_coupon($coupon_id);
        
        if ($result) {
            header('Location: ../coupons.php?message=Kupon başarıyla silindi&type=success');
            exit();
        } else {
            throw new Exception('Kupon silinirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../coupons.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// Geçersiz işlem
else {
    header('Location: ../coupons.php?message=Geçersiz işlem&type=danger');
    exit();
}
?>