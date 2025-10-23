<?php
session_start();
require_once '../../config/auth.php';

// Sadece admin erişebilir
if (!is_logged_in() || !is_admin()) {
    header('Location: ../../public/login.php?message=Yetkisiz erişim');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$firm_id = $_POST['firm_id'] ?? $_GET['id'] ?? null;

// ========================================
// FİRMA EKLEME
// ========================================
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'logo' => trim($_POST['logo'] ?? 'default-logo.png'),
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            throw new Exception('Firma adı gereklidir');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçerli bir email adresi girin');
        }
        
        $result = create_firm($data);
        
        if ($result) {
            header('Location: ../firms.php?message=Firma başarıyla eklendi&type=success');
            exit();
        } else {
            throw new Exception('Firma eklenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../firms.php?action=add&message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// FİRMA DÜZENLEME
// ========================================
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $firm_id = $_POST['firm_id'] ?? null;
        
        if (empty($firm_id)) {
            throw new Exception('Firma ID gereklidir');
        }
        
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'logo' => trim($_POST['logo'] ?? 'default-logo.png'),
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            throw new Exception('Firma adı gereklidir');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçerli bir email adresi girin');
        }
        
        $result = update_firm($firm_id, $data);
        
        if ($result) {
            header('Location: ../firms.php?message=Firma başarıyla güncellendi&type=success');
            exit();
        } else {
            throw new Exception('Firma güncellenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../firms.php?action=edit&id=' . $firm_id . '&message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// FİRMA SİLME (Soft Delete)
// ========================================
elseif ($action === 'delete' && !empty($firm_id)) {
    try {
        // Firmaya ait aktif seferler var mı kontrol et
        $db = getDB();
        $check = $db->prepare("SELECT COUNT(*) FROM trips WHERE firm_id = :firm_id AND departure_time > CURRENT_TIMESTAMP");
        $check->execute([':firm_id' => $firm_id]);
        $active_trips = $check->fetchColumn();
        
        if ($active_trips > 0) {
            throw new Exception("Bu firmaya ait {$active_trips} adet gelecek tarihli sefer bulunuyor. Önce seferleri iptal edin.");
        }
        
        $result = delete_firm($firm_id);
        
        if ($result) {
            header('Location: ../firms.php?message=Firma başarıyla silindi&type=success');
            exit();
        } else {
            throw new Exception('Firma silinirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../firms.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// Geçersiz işlem
else {
    header('Location: ../firms.php?message=Geçersiz işlem&type=danger');
    exit();
}
?>
