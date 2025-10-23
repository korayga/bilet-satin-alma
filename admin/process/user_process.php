<?php
session_start();
require_once '../../config/auth.php';

// Sadece admin erişebilir
if (!is_logged_in() || !is_admin()) {
    header('Location: ../../public/login.php?message=Yetkisiz erişim');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_POST['user_id'] ?? $_GET['id'] ?? null;

// ========================================
// KULLANICI OLUŞTURMA (Admin veya Firma Admin)
// ========================================
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $firm_id = !empty($_POST['firm_id']) ? intval($_POST['firm_id']) : null;
        
        // Validasyon
        if (empty($username) || empty($password) || empty($full_name)) {
            throw new Exception('Kullanıcı adı, şifre ve ad soyad zorunludur');
        }
        
        if (strlen($username) < 4) {
            throw new Exception('Kullanıcı adı en az 4 karakter olmalıdır');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Şifre en az 6 karakter olmalıdır');
        }
        
        if (!in_array($role, ['firmadmin', 'admin'])) {
            throw new Exception('Sadece admin veya firma admin oluşturabilirsiniz');
        }
        
        if ($role === 'firmadmin' && empty($firm_id)) {
            throw new Exception('Firma admin için firma seçimi zorunludur');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçerli bir email adresi girin');
        }
        
        // Backend fonksiyonunu kullan
        if ($role === 'firmadmin') {
            // Firma var mı kontrol et
            $firm = get_firm($firm_id);
            if (!$firm) {
                throw new Exception('Geçersiz firma seçimi');
            }
            
            $result = create_firm_admin($username, $password, $full_name, $email, $firm_id);
            
            if ($result) {
                header('Location: ../users.php?message=Firma admin başarıyla oluşturuldu&type=success');
                exit();
            } else {
                throw new Exception('Bu kullanıcı adı zaten kullanılıyor');
            }
        } else {
            // Admin oluşturma
            $db = getDB();
            
            // Kullanıcı adı kontrolü
            $check = $db->prepare("SELECT id FROM users WHERE username = :username");
            $check->execute([':username' => $username]);
            if ($check->fetch()) {
                throw new Exception('Bu kullanıcı adı zaten kullanılıyor');
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = $db->prepare("
                INSERT INTO users (username, password, full_name, email, role, balance, created_at) 
                VALUES (:username, :password, :full_name, :email, 'admin', 0, CURRENT_TIMESTAMP)
            ");
            
            $result = $query->execute([
                ':username' => $username,
                ':password' => $hashed_password,
                ':full_name' => $full_name,
                ':email' => $email
            ]);
            
            if ($result) {
                header('Location: ../users.php?message=Admin başarıyla oluşturuldu&type=success');
                exit();
            } else {
                throw new Exception('Admin oluşturulurken bir hata oluştu');
            }
        }
        
    } catch (Exception $e) {
        header('Location: ../users.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// KULLANICI GÜNCELLEME
// ========================================
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_POST['user_id'] ?? null;
        
        if (empty($user_id)) {
            throw new Exception('Kullanıcı ID gereklidir');
        }
        
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email'] ?? '');
        $balance = floatval($_POST['balance'] ?? 0);
        $new_password = trim($_POST['new_password'] ?? '');
        
        // Validasyon
        if (empty($full_name)) {
            throw new Exception('Ad soyad zorunludur');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçerli bir email adresi girin');
        }
        
        $db = getDB();
        
        // Şifre değiştirilecek mi?
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                throw new Exception('Yeni şifre en az 6 karakter olmalıdır');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = $db->prepare("
                UPDATE users 
                SET full_name = :full_name, 
                    email = :email, 
                    balance = :balance,
                    password = :password,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :user_id
            ");
            
            $result = $query->execute([
                ':user_id' => $user_id,
                ':full_name' => $full_name,
                ':email' => $email,
                ':balance' => $balance,
                ':password' => $hashed_password
            ]);
        } else {
            // Şifre değiştirilmeyecek
            $query = $db->prepare("
                UPDATE users 
                SET full_name = :full_name, 
                    email = :email, 
                    balance = :balance,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :user_id
            ");
            
            $result = $query->execute([
                ':user_id' => $user_id,
                ':full_name' => $full_name,
                ':email' => $email,
                ':balance' => $balance
            ]);
        }
        
        if ($result) {
            header('Location: ../users.php?message=Kullanıcı başarıyla güncellendi&type=success');
            exit();
        } else {
            throw new Exception('Kullanıcı güncellenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../users.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// FİRMA ADMİN OLUŞTURMA
// ========================================
elseif ($action === 'create_firm_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $firm_id = intval($_POST['firm_id']);
        
        // Validasyon
        if (empty($username) || empty($password) || empty($fullname) || empty($email) || empty($firm_id)) {
            throw new Exception('Tüm alanlar zorunludur');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçerli bir email adresi girin');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Şifre en az 6 karakter olmalıdır');
        }
        
        if (strlen($username) < 4) {
            throw new Exception('Kullanıcı adı en az 4 karakter olmalıdır');
        }
        
        // Firma var mı kontrol et
        $firm = get_firm($firm_id);
        if (!$firm) {
            throw new Exception('Geçersiz firma seçimi');
        }
        
        $result = create_firm_admin($username, $password, $fullname, $email, $firm_id);
        
        if ($result) {
            header('Location: ../users.php?message=Firma admin başarıyla oluşturuldu&type=success');
            exit();
        } else {
            throw new Exception('Bu kullanıcı adı zaten kullanılıyor');
        }
        
    } catch (Exception $e) {
        header('Location: ../users.php?action=add&message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// KULLANICI SİLME
// ========================================
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_POST['user_id'] ?? null;
        
        if (empty($user_id)) {
            throw new Exception('Kullanıcı ID gereklidir');
        }
        
        $db = getDB();
        
        // Kendini silmeye çalışıyor mu?
        if ($user_id == $_SESSION['id']) {
            throw new Exception('Kendi hesabınızı silemezsiniz');
        }
        
        // Kullanıcıyı getir
        $user_query = $db->prepare("SELECT role FROM users WHERE id = :user_id");
        $user_query->execute([':user_id' => $user_id]);
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('Kullanıcı bulunamadı');
        }
        
        // Admin kullanıcıları silinemez
        if ($user['role'] === 'admin') {
            throw new Exception('Admin kullanıcıları silinemez');
        }
        
        // Kullanıcının aktif biletleri var mı?
        $ticket_check = $db->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = :user_id AND status = 'active'");
        $ticket_check->execute([':user_id' => $user_id]);
        $active_tickets = $ticket_check->fetchColumn();
        
        if ($active_tickets > 0) {
            throw new Exception("Bu kullanıcının {$active_tickets} adet aktif bileti var. Silme işlemi yapılamaz.");
        }
        
        // Kullanıcıyı sil
        $delete = $db->prepare("DELETE FROM users WHERE id = :user_id");
        $result = $delete->execute([':user_id' => $user_id]);
        
        if ($result) {
            header('Location: ../users.php?message=Kullanıcı başarıyla silindi&type=success');
            exit();
        } else {
            throw new Exception('Kullanıcı silinirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../users.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// KULLANICI ROL DEĞİŞTİRME
// ========================================
elseif ($action === 'change_role' && !empty($user_id)) {
    try {
        $db = getDB();
        $new_role = $_POST['new_role'] ?? '';
        
        if (!in_array($new_role, ['user', 'firmadmin'])) {
            throw new Exception('Geçersiz rol');
        }
        
        // Kendinin rolünü değiştiremez
        if ($user_id == $_SESSION['id']) {
            throw new Exception('Kendi rolünüzü değiştiremezsiniz');
        }
        
        $update = $db->prepare("UPDATE users SET role = :role WHERE id = :user_id");
        $result = $update->execute([
            ':role' => $new_role,
            ':user_id' => $user_id
        ]);
        
        if ($result) {
            header('Location: ../users.php?message=Kullanıcı rolü güncellendi&type=success');
            exit();
        } else {
            throw new Exception('Rol güncellenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../users.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// Geçersiz işlem
else {
    header('Location: ../users.php?message=Geçersiz işlem&type=danger');
    exit();
}
?>
