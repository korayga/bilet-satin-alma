<?php
session_start();
require_once '../../config/auth.php';

// Sadece admin erişebilir
if (!is_logged_in() || !is_admin()) {
    header('Location: ../../public/login.php?message=Yetkisiz erişim');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$trip_id = $_POST['trip_id'] ?? $_GET['id'] ?? null;

// ========================================
// SEFER EKLEME (CREATE)
// ========================================
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'firm_id' => intval($_POST['firm_id']),
            'departure_city' => trim($_POST['departure_city']),
            'arrival_city' => trim($_POST['arrival_city']),
            'departure_time' => $_POST['departure_time'],
            'arrival_time' => $_POST['arrival_time'],
            'price' => floatval($_POST['price']),
            'total_seats' => intval($_POST['total_seats']),
            'available_seats' => intval($_POST['available_seats']),
            'bus_type' => $_POST['bus_type'] ?? '2+2'
        ];
        
        // Validasyon
        if (empty($data['departure_city']) || empty($data['arrival_city'])) {
            throw new Exception('Kalkış ve varış şehirleri gereklidir');
        }
        
        if ($data['departure_city'] === $data['arrival_city']) {
            throw new Exception('Kalkış ve varış şehirleri aynı olamaz');
        }
        
        if ($data['price'] <= 0) {
            throw new Exception('Fiyat 0\'dan büyük olmalıdır');
        }
        
        if ($data['total_seats'] < 20 || $data['total_seats'] > 60) {
            throw new Exception('Koltuk sayısı 20-60 arasında olmalıdır');
        }
        
        // Tarih kontrolü
        if (strtotime($data['departure_time']) < time()) {
            throw new Exception('Kalkış tarihi geçmiş olamaz');
        }
        
        if (strtotime($data['arrival_time']) <= strtotime($data['departure_time'])) {
            throw new Exception('Varış tarihi kalkış tarihinden sonra olmalıdır');
        }
        
        // Backend fonksiyonunu kullan
        $result = create_trip($data);
        
        if ($result) {
            header('Location: ../trips.php?message=Sefer başarıyla eklendi&type=success');
            exit();
        } else {
            throw new Exception('Sefer eklenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../trips.php?action=add&message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// SEFER GÜNCELLEME (UPDATE)
// ========================================
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($trip_id)) {
            throw new Exception('Sefer ID gereklidir');
        }
        
        // Mevcut seferi getir
        $trip = get_trip_by_id($trip_id);
        if (!$trip) {
            throw new Exception('Sefer bulunamadı');
        }
        
        $data = [
            'firm_id' => intval($_POST['firm_id']),
            'departure_city' => trim($_POST['departure_city']),
            'arrival_city' => trim($_POST['arrival_city']),
            'departure_time' => $_POST['departure_time'],
            'arrival_time' => $_POST['arrival_time'],
            'price' => floatval($_POST['price']),
            'total_seats' => intval($_POST['total_seats']),
            'available_seats' => intval($_POST['available_seats']),
            'bus_type' => $_POST['bus_type'] ?? '2+2'
        ];
        
        // Validasyon
        if (empty($data['departure_city']) || empty($data['arrival_city'])) {
            throw new Exception('Kalkış ve varış şehirleri gereklidir');
        }
        
        if ($data['price'] <= 0) {
            throw new Exception('Fiyat 0\'dan büyük olmalıdır');
        }
        
        // Satılan bilet sayısından az koltuk olamaz
        $sold_seats = $trip['total_seats'] - $trip['available_seats'];
        if ($data['total_seats'] < $sold_seats) {
            throw new Exception("Bu sefer için {$sold_seats} koltuk satıldı. Toplam koltuk sayısı bundan az olamaz.");
        }
        
        // Backend fonksiyonunu kullan
        $result = update_trip($trip_id, $data);
        
        if ($result) {
            header('Location: ../trips.php?message=Sefer başarıyla güncellendi&type=success');
            exit();
        } else {
            throw new Exception('Sefer güncellenirken bir hata oluştu');
        }
        
    } catch (Exception $e) {
        header('Location: ../trips.php?action=edit&id=' . $trip_id . '&message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// ========================================
// SEFER SİLME (DELETE)
// ========================================
elseif ($action === 'delete' && !empty($trip_id)) {
    try {
        // Backend fonksiyonunu kullan
        $result = delete_trip($trip_id);
        
        if ($result) {
            header('Location: ../trips.php?message=Sefer başarıyla silindi&type=success');
            exit();
        } else {
            throw new Exception('Sefer silinemedi. Bu sefere ait aktif biletler var.');
        }
        
    } catch (Exception $e) {
        header('Location: ../trips.php?message=' . urlencode($e->getMessage()) . '&type=danger');
        exit();
    }
}

// Geçersiz işlem
else {
    header('Location: ../trips.php?message=Geçersiz işlem&type=danger');
    exit();
}
?>