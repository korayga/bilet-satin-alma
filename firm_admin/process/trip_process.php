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

$db = getDB();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $departure_city = $_POST['departure_city'] ?? '';
            $arrival_city = $_POST['arrival_city'] ?? '';
            $departure_time = $_POST['departure_time'] ?? '';
            $arrival_time = $_POST['arrival_time'] ?? '';
            $price = $_POST['price'] ?? 0;
            $total_seats = $_POST['total_seats'] ?? 0;
            $bus_type = $_POST['bus_type'] ?? '2+1';
            
            $stmt = $db->prepare("
                INSERT INTO trips (firm_id, departure_city, arrival_city, departure_time, arrival_time, price, total_seats, available_seats, bus_type)
                VALUES (:firm_id, :departure_city, :arrival_city, :departure_time, :arrival_time, :price, :total_seats, :total_seats, :bus_type)
            ");
            
            $stmt->execute([
                ':firm_id' => $firm_id,
                ':departure_city' => $departure_city,
                ':arrival_city' => $arrival_city,
                ':departure_time' => $departure_time,
                ':arrival_time' => $arrival_time,
                ':price' => $price,
                ':total_seats' => $total_seats,
                ':bus_type' => $bus_type
            ]);
            
            $_SESSION['success'] = 'Sefer başarıyla eklendi.';
            break;
            
        case 'update':
            $trip_id = $_POST['trip_id'] ?? 0;
            $departure_city = $_POST['departure_city'] ?? '';
            $arrival_city = $_POST['arrival_city'] ?? '';
            $departure_time = $_POST['departure_time'] ?? '';
            $arrival_time = $_POST['arrival_time'] ?? '';
            $price = $_POST['price'] ?? 0;
            $bus_type = $_POST['bus_type'] ?? '2+1';
            
            // Yetki kontrolü: Bu sefer bu firmaya ait mi?
            $check = $db->prepare("SELECT id FROM trips WHERE id = :trip_id AND firm_id = :firm_id");
            $check->execute([':trip_id' => $trip_id, ':firm_id' => $firm_id]);
            
            if (!$check->fetch()) {
                $_SESSION['error'] = 'Bu seferi düzenleme yetkiniz yok.';
                break;
            }
            
            $stmt = $db->prepare("
                UPDATE trips 
                SET departure_city = :departure_city,
                    arrival_city = :arrival_city,
                    departure_time = :departure_time,
                    arrival_time = :arrival_time,
                    price = :price,
                    bus_type = :bus_type
                WHERE id = :trip_id AND firm_id = :firm_id
            ");
            
            $stmt->execute([
                ':trip_id' => $trip_id,
                ':firm_id' => $firm_id,
                ':departure_city' => $departure_city,
                ':arrival_city' => $arrival_city,
                ':departure_time' => $departure_time,
                ':arrival_time' => $arrival_time,
                ':price' => $price,
                ':bus_type' => $bus_type
            ]);
            
            $_SESSION['success'] = 'Sefer başarıyla güncellendi.';
            break;
            
        case 'delete':
            $trip_id = $_POST['trip_id'] ?? 0;
            
            // Yetki kontrolü + Satılan bilet var mı kontrolü
            $check = $db->prepare("
                SELECT t.id, 
                       (SELECT COUNT(*) FROM tickets WHERE trips_id = t.id) as ticket_count
                FROM trips t
                WHERE t.id = :trip_id AND t.firm_id = :firm_id
            ");
            $check->execute([':trip_id' => $trip_id, ':firm_id' => $firm_id]);
            $trip = $check->fetch(PDO::FETCH_ASSOC);
            
            if (!$trip) {
                $_SESSION['error'] = 'Bu seferi silme yetkiniz yok.';
                break;
            }
            
            if ($trip['ticket_count'] > 0) {
                $_SESSION['error'] = 'Bu sefere ait satılmış biletler olduğu için silinemez.';
                break;
            }
            
            $stmt = $db->prepare("DELETE FROM trips WHERE id = :trip_id AND firm_id = :firm_id");
            $stmt->execute([':trip_id' => $trip_id, ':firm_id' => $firm_id]);
            
            $_SESSION['success'] = 'Sefer başarıyla silindi.';
            break;
            
        default:
            $_SESSION['error'] = 'Geçersiz işlem.';
            break;
    }
} catch (Exception $e) {
    error_log("Sefer işlemi hatası: " . $e->getMessage());
    $_SESSION['error'] = 'İşlem sırasında bir hata oluştu.';
}

header('Location: ../trips.php');
exit;
