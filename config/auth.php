<?php

require_once 'config.php';



function access_control() {

    if (session_status() == PHP_SESSION_NONE) {

        session_start();

    }

    if (!isset($_SESSION["kullanici_id"])) {

        header('Location: login.php');

        exit();

    }

}



function user_access($username, $password) {

    $db = getDB(); 

    if (!$db) return false;



    try {

        $sorgu = $db->prepare("SELECT * FROM users WHERE username = :username");

        $sorgu->execute([':username' => $username]);

        $kullanici = $sorgu->fetch();



        if ($kullanici && password_verify($password, $kullanici['password'])) {

            if (session_status() == PHP_SESSION_NONE) {

                session_start();

            }

            

            $_SESSION['kullanici_id'] = $kullanici['id'];

            $_SESSION['id'] = $kullanici['id']; 

            $_SESSION['kullanici_adi'] = $kullanici['username'];

            $_SESSION['kullanici_yetki'] = $kullanici['role'];

            $_SESSION['tam_isim'] = $kullanici['full_name'];

            $_SESSION['email'] = $kullanici['email'];

            $_SESSION['bakiye'] = $kullanici['balance'];

            $_SESSION['role'] = $kullanici['role'];

            

            // Firma admin ise firm_id'yi de kaydet

            if ($kullanici['role'] === 'firmadmin') {

                $_SESSION['firm_id'] = $kullanici['firm_id'];

            }

            

            return true;

        }

        return false;

        

    } catch (PDOException $e) {

        error_log("Giriş hatası: " . $e->getMessage());

        return false;

    }

}



function user_register($username, $password, $fullname, $email) {

    $db = getDB(); 



    try {

        // Kullanıcı adı ve email kontrolü

        $check = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");

        $check->execute([':username' => $username, ':email' => $email]);

        

        if ($check->fetch()) {

            return false; // Kullanıcı zaten var

        }



        // Şifreyi hashle

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        

        // Default değerleri kullan (role='user', balance=800)

        $sorgu = $db->prepare("INSERT INTO users (username, full_name, email, password) 

                              VALUES (:username, :full_name, :email, :password)");

        $sorgu->execute([

            ':username' => $username,

            ':full_name' => $fullname,

            ':email' => $email,

            ':password' => $hashed_password

        ]);

        

        return $db->lastInsertId();

        

    } catch (PDOException $e) {

        error_log("Kayıt hatası: " . $e->getMessage());

        return false;

    }

}



function logout() {

    if (session_status() == PHP_SESSION_NONE) {

        session_start();

    }

    session_destroy();

    header("Location: login.php");

    exit();

}



function require_role($role) {

    access_control();

    if (!($_SESSION['role'] === $role)) {

        http_response_code(403);

        exit('Yetkisiz erişim!');

    }

    

}

// ===== YARDIMCI FONKSİYONLAR =====



function is_logged_in() {

    return isset($_SESSION["kullanici_id"]);

}



function get_user_role() {

    return $_SESSION['role'] ?? null;

}



function get_user_data($key, $default = null) {

    return $_SESSION[$key] ?? $default;

}



function get_user_id() {

    return $_SESSION['kullanici_id'] ?? null;

}



function get_user_name() {

    return $_SESSION['tam_isim'] ?? 'Kullanıcı';

}



function is_role($role) {

    return get_user_role() === $role;

}



function is_admin() {

    return is_role('admin');

}



function is_user() {

    return is_role('user');

}



function is_firm_admin() {

    return is_role('firmadmin');

}



function require_login() {

    if (!is_logged_in()) {

        $redirect = urlencode($_SERVER['REQUEST_URI']);

        header("Location: login.php?redirect=$redirect");

        exit();

    }

}







// ===== VERİTABANI YARDIMCI FONKSİYONLARI =====



function get_random_trips($limit = 3) {

    try {

        $db = getDB();

        // Sadece gelecek tarihli seferler (geçmiş tarihli biletler satıştan kaldırıldı)
        $sorgu = $db->prepare("SELECT t.*, f.name as firm_name 

                              FROM trips t 

                              JOIN firms f ON t.firm_id = f.id 

                              WHERE t.total_seats > 0 
                              AND datetime(t.departure_time) > datetime('now')

                              ORDER BY RANDOM() 

                              LIMIT :limit");

        $sorgu->bindParam(':limit', $limit, PDO::PARAM_INT);

        $sorgu->execute();

        return $sorgu->fetchAll();

    } catch (PDOException $e) {

        error_log("Rastgele sefer getirme hatası: " . $e->getMessage());

        return [];

    }

}

function search_trips($from, $to, $date = null) {
    try {
        $db = getDB();
        // Sadece gelecek tarihli seferler
        $sql = "SELECT t.*, f.name as firm_name 
                FROM trips t 
                JOIN firms f ON t.firm_id = f.id 
                WHERE t.departure_city LIKE :from 
                AND t.arrival_city LIKE :to 
                AND t.available_seats > 0
                AND datetime(t.departure_time) > datetime('now')";
        
        $params = [
            ':from' => "%$from%",
            ':to' => "%$to%"
        ];
        
        if ($date) {
            $sql .= " AND DATE(t.departure_time) = :date";
            $params[':date'] = $date;
        }
        
        $sql .= " ORDER BY t.departure_time";
        
        $sorgu = $db->prepare($sql);
        $sorgu->execute($params);
        return $sorgu->fetchAll();
    } catch (PDOException $e) {
        error_log("Sefer arama hatası: " . $e->getMessage());
        return [];
    }
}
       
function get_trip($trips_id){
    try{
        $db=getDB();
        $sorgu=$db->prepare("SELECT t.*,f.name as firm_name FROM trips t JOIN firms f ON t.firm_id=f.id WHERE t.id=:trips_id");
        $sorgu->bindValue(":trips_id",$trips_id);
        $sorgu->execute();
        $trip = $sorgu->fetch();
        return $trip;

    }catch (PDOException $e) {
        error_log("Sefer  hatası: " . $e->getMessage());
        return [];
    }
}




function get_balance(){ 
     $db = getDB();

    if(is_logged_in() && is_user() ){
    $sorgu = $db->prepare("SELECT balance FROM users WHERE id = :user_id");
        $sorgu->bindParam(':user_id', $_SESSION["id"]);
        $sorgu->execute();
        $current_balance = $sorgu->fetchColumn();
        return $current_balance;
    }
    return null;
}

function set_balance($trips_id, $selected_seats = [1], $coupon_code = null){
    $db = getDB();
    
    if (!$db) {
        return ['success' => false, 'message' => 'Veritabanı bağlantı hatası'];
    }

    if (!is_logged_in() || !is_user()) {
        return ['success' => false, 'message' => 'Yetkisiz erişim'];
    }

    // Seçilen koltukları doğrula
    if (empty($selected_seats) || !is_array($selected_seats)) {
        $selected_seats = [1]; // Varsayılan koltuk
    }
    
    $seat_count = count($selected_seats);
    $seat_count = max(1, min(4, $seat_count)); // 1-4 arası sınırla

    // Sefer bilgilerini çek (fiyat ve firm_id)
    $s = $db->prepare("SELECT price, firm_id, departure_time FROM trips WHERE id = :trips_id");
    $s->bindParam(':trips_id', $trips_id);
    $s->execute();
    $trip = $s->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        return ['success' => false, 'message' => 'Sefer bulunamadı.'];
    }
    
    // GEÇMİŞ TARİHLİ SEFER KONTROLÜ
    if (strtotime($trip['departure_time']) <= time()) {
        return ['success' => false, 'message' => 'Bu sefer geçmiş tarihli olduğu için satışa kapalıdır!'];
    }
    
    $single_price = $trip['price'];
    $firm_id = $trip['firm_id'];
    
    // ÖNEMLİ: ÖDEME YAPMADAN ÖNCE KOLTUKLARIN MÜSAİT OLDUĞUNU KONTROL ET
    foreach ($selected_seats as $seat_number) {
        $check = $db->prepare("
            SELECT seat_number 
            FROM booked_seats 
            WHERE trips_id = :trips_id AND seat_number = :seat_number AND booked_seat = 'booked'
        ");
        $check->execute([
            ':trips_id' => $trips_id,
            ':seat_number' => $seat_number
        ]);
        
        if ($check->fetch()) {
            return ['success' => false, 'message' => "Koltuk {$seat_number} zaten rezerve edilmiş. Lütfen sayfa yenileyip farklı koltuk seçin."];
        }
    }
    
    // Toplam fiyat = koltuk sayısı × birim fiyat
    $original_price = $single_price * $seat_count;
    $final_price = $original_price; // Başlangıç fiyatı
    $discount_amount = 0;
    $coupon_message = '';
    $coupon_id = null;
    
    // Kupon kodu kontrolü (toplam fiyat üzerinden + firm_id kontrolü)
    if ($coupon_code && !empty(trim($coupon_code))) {
        $coupon_result = check_coupon_validity($coupon_code, $original_price, $_SESSION["id"], $firm_id);
        
        if ($coupon_result['valid']) {
            $final_price = $coupon_result['final_price'];
            $discount_amount = $coupon_result['discount'];
            $coupon_id = $coupon_result['coupon_id'];
            $coupon_message = ' (Kupon indirimi: -' . $discount_amount . ' TL)';
        } else {
            return ['success' => false, 'message' => $coupon_result['message']];
        }
    }
    
    $bakiye = get_balance();
    if ($bakiye < $final_price) {
        return ['success' => false, 'message' => 'Yetersiz bakiye. Mevcut bakiye: ' . $bakiye. ' TL, Gerekli: ' . $final_price . ' TL'];
    }
    
    try {
        // Ödeme işlemi
        $sorgu = $db->prepare("UPDATE users SET balance= balance- :price where id=:user_id");
        $sorgu->bindParam(':price',$final_price);
        $sorgu->bindParam(':user_id',$_SESSION["id"]);
        $result=$sorgu->execute();
        
        if (!$result) {
            return ['success' => false, 'message' => 'Ödeme işlemi başarısız'];
        }
        
        // Kupon kullanıldıysa kaydet
        if ($coupon_id !== null) {
            $insert_usage = $db->prepare("
                INSERT INTO user_coupons (coupon_id, user_id, usage_time) 
                VALUES (:coupon_id, :user_id, CURRENT_TIMESTAMP)
            ");
            
            $coupon_result = $insert_usage->execute([
                ':coupon_id' => $coupon_id,
                ':user_id' => $_SESSION["id"]
            ]);
            
            if (!$coupon_result) {
                return ['success' => false, 'message' => 'Kupon kullanımı kaydedilemedi'];
            }
        }
        
        $_SESSION['bakiye']=get_balance();
        return [
            'success' => true, 
            'message' => 'Ödeme başarılı' . $coupon_message, 
            'new_balance' => $_SESSION["bakiye"],
            'original_price' => $original_price,
            'final_price' => $final_price,
            'discount' => $discount_amount
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()];
    }
}

//Kupon kodunu kontrol et (sadece doğrulama, kullanmaz)
 
function check_coupon_validity($coupon_code, $trip_price, $user_id, $firm_id = null) {
    $db = getDB();
    
    if (!$db) {
        return ['valid' => false, 'message' => 'Veritabanı bağlantı hatası'];
    }
    
    try {
        // Kuponu çek (firm_id kontrolü ile)
        $coupon_stmt = $db->prepare("
            SELECT id, firm_id, discount_percentage, usage_limit, expire_date,
                   (SELECT COUNT(*) FROM tickets WHERE coupon_code = coupons.code) as current_uses
            FROM coupons 
            WHERE code = :code
        ");
        $coupon_stmt->execute([':code' => $coupon_code]);
        $coupon = $coupon_stmt->fetch();
        
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Geçersiz kupon kodu'];
        }
        
        // FİRMA KONTROLÜ: Kupon firma özel ise, sadece o firmaya ait seferlerde kullanılabilir
        if ($coupon['firm_id'] !== null && $coupon['firm_id'] != $firm_id) {
            return ['valid' => false, 'message' => 'Bu kupon sadece belirli bir firmada geçerlidir'];
        }
        
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Geçersiz kupon kodu'];
        }
        
        if (strtotime($coupon['expire_date']) < time()) {
            return ['valid' => false, 'message' => 'Kuponun süresi dolmuş'];
        }
        
        if ($coupon['current_uses'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Kupon kullanım limiti dolmuş'];
        }
        
        $usage_stmt = $db->prepare("
            SELECT id FROM user_coupons 
            WHERE coupon_id = :coupon_id AND user_id = :user_id
        ");
        $usage_stmt->execute([
            ':coupon_id' => $coupon['id'],
            ':user_id' => $user_id
        ]);
        
        if ($usage_stmt->fetch()) {
            return ['valid' => false, 'message' => 'Bu kuponu daha önce kullandınız'];
        }
        
        // Yüzde bazlı indirim hesapla
        $discount_percentage = (float)$coupon['discount_percentage'];
        $discount_amount = ($trip_price * $discount_percentage) / 100;
        
        // İndirim toplam fiyattan fazla olamaz
        if ($discount_amount > $trip_price) {
            $discount_amount = $trip_price;
        }
        
        $final_price = $trip_price - $discount_amount;
        
        return [
            'valid' => true,
            'discount' => $discount_amount,
            'final_price' => $final_price,
            'original_price' => $trip_price,
            'coupon_id' => $coupon['id'],
            'message' => "Kupon geçerli! {$discount_amount} TL indirim uygulanacak"
        ];
        
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()];
    }
}



function create_ticket($trips_id, $payment_details, $selected_seats = [1]) {
    $db = getDB();

    if (!$db) {
        return ['success' => false, 'message' => 'Veritabanı hatası'];
    }
    
    if (!is_logged_in() || !is_user()) {
        return ['success' => false, 'message' => "Yetkisiz erişim"];
    }

    if (!is_array($payment_details) || !isset($payment_details['success']) || !$payment_details['success']) {
        return ['success' => false, 'message' => 'Geçersiz ödeme bilgisi'];
    }

    // Seçilen koltukları doğrula
    if (empty($selected_seats) || !is_array($selected_seats)) {
        $selected_seats = [1]; // Varsayılan koltuk
    }

    $user_id = $_SESSION['id'];
    $trip = get_trip($trips_id);

    if (!$trip) {
        return ['success' => false, 'message' => "Sefer bulunamadı"];
    }
    
    // GEÇMİŞ TARİHLİ SEFER KONTROLÜ
    if (strtotime($trip['departure_time']) <= time()) {
        return ['success' => false, 'message' => 'Bu sefer geçmiş tarihli olduğu için bilet oluşturulamaz!'];
    }
    
    $original_price = $payment_details['original_price'] ?? $trip['price'];
    $final_price = $payment_details['final_price'] ?? $original_price;
    $discount = $payment_details['discount'] ?? 0;
    $ticket_count = count($selected_seats);

    // Rastgele bilet kodu oluştur
    $ticket_code = 'BLT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    try {
        // ÖNEMLİ: Bilet oluşturmadan ÖNCE koltukların müsait olduğunu kontrol et
        foreach ($selected_seats as $seat_number) {
            $check = $db->prepare("
                SELECT seat_number 
                FROM booked_seats 
                WHERE trips_id = :trips_id AND seat_number = :seat_number AND booked_seat = 'booked'
            ");
            $check->execute([
                ':trips_id' => $trips_id,
                ':seat_number' => $seat_number
            ]);
            
            if ($check->fetch()) {
                return ['success' => false, 'message' => "Koltuk {$seat_number} zaten rezerve edilmiş. Lütfen başka koltuk seçin."];
            }
        }
        
        // Bilet oluştur
        $sorgu = $db->prepare("
            INSERT INTO tickets (user_id, trips_id, ticket_code, total_price, ticket_count, discount, status) 
            VALUES (:user_id, :trips_id, :ticket_code, :total_price, :ticket_count, :discount, :status)
        ");
        
        $result = $sorgu->execute([
            ':user_id' => $user_id,
            ':trips_id' => $trips_id,
            ':ticket_code' => $ticket_code,
            ':total_price' => $final_price,
            ':ticket_count' => $ticket_count,
            ':discount' => $discount,
            ':status' => 'active'
        ]);

        if (!$result) {
            return ['success' => false, 'message' => 'Bilet oluşturulamadı'];
        }
        
        $ticket_id = $db->lastInsertId();

        // Koltukları rezerve et (kontrol zaten yapıldı)
        $insert_stmt = $db->prepare("
            INSERT INTO booked_seats (tickets_id, trips_id, seat_number, booked_seat) 
            VALUES (:ticket_id, :trips_id, :seat_number, 'booked')
        ");
        
        foreach ($selected_seats as $seat_number) {
            $result = $insert_stmt->execute([
                ':ticket_id' => $ticket_id,
                ':trips_id' => $trips_id,
                ':seat_number' => $seat_number
            ]);
            
            if (!$result) {
                // Hata durumunda bileti iptal et
                $db->exec("DELETE FROM tickets WHERE id = $ticket_id");
                return ['success' => false, 'message' => 'Koltuk rezervasyonu başarısız'];
            }
        }

        // trips tablosundaki available_seats sayısını güncelle
        $update_seats = $db->prepare("
            UPDATE trips 
            SET available_seats = available_seats - :seat_count 
            WHERE id = :trips_id AND available_seats >= :seat_count
        ");
        $update_result = $update_seats->execute([
            ':seat_count' => $ticket_count,
            ':trips_id' => $trips_id
        ]);
        
        if (!$update_result || $update_seats->rowCount() === 0) {
            // Rollback: Bileti ve rezervasyonları sil
            $db->exec("DELETE FROM booked_seats WHERE ticket_id = $ticket_id");
            $db->exec("DELETE FROM tickets WHERE id = $ticket_id");
            return ['success' => false, 'message' => 'Koltuk güncelleme başarısız - yeterli koltuk yok'];
        }
        
        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'ticket_code' => $ticket_code,
            'message' => 'Bilet başarıyla oluşturuldu',
            'selected_seats' => $selected_seats,
            'ticket_count' => $ticket_count,
            'original_price' => $original_price,
            'final_price' => $final_price,
            'discount' => $discount
        ];
        
    } catch (Exception $e) {
        error_log("Bilet oluşturma hatası: " . $e->getMessage());
        return ['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()];
    }
}


function generateSeatLayout($bus_type, $total_seats = 52, $booked_seats = []) {
    $seats = [];
    $seat_counter = 1;
    
    if ($bus_type === '2+2') {
        // 2+2 düzeni: [A][B]  koridor  [C][D]
        $rows = ceil($total_seats / 4);
        
        for ($row = 1; $row <= $rows; $row++) {
            $positions = [
                ['letter' => 'A', 'position' => 'left-window'],
                ['letter' => 'B', 'position' => 'left-aisle'],
                ['letter' => 'C', 'position' => 'right-aisle'],
                ['letter' => 'D', 'position' => 'right-window']
            ];
            
            foreach ($positions as $pos) {
                if ($seat_counter > $total_seats) break;
                
                $seat_display = $row . $pos['letter'];
                $seats[] = [
                    'number' => $seat_counter,
                    'display' => $seat_display,
                    'id' => $seat_display,
                    'row' => $row,
                    'position' => $pos['position'],
                    'status' => (in_array($seat_counter, $booked_seats) || in_array($seat_display, $booked_seats)) ? 'booked' : 'available'
                ];
                $seat_counter++;
            }
        }
    } else {
        // 2+1 düzeni: [A][B]  koridor  [C]
        $rows = ceil($total_seats / 3);
        
        for ($row = 1; $row <= $rows; $row++) {
            $positions = [
                ['letter' => 'A', 'position' => 'left-window'],
                ['letter' => 'B', 'position' => 'left-aisle'],
                ['letter' => 'C', 'position' => 'right-single']
            ];
            
            foreach ($positions as $pos) {
                if ($seat_counter > $total_seats) break;
                
                $seat_display = $row . $pos['letter'];
                $seats[] = [
                    'number' => $seat_counter,
                    'display' => $seat_display,
                    'id' => $seat_display,
                    'row' => $row,
                    'position' => $pos['position'],
                    'status' => (in_array($seat_counter, $booked_seats) || in_array($seat_display, $booked_seats)) ? 'booked' : 'available'
                ];
                $seat_counter++;
            }
        }
    }
    
    return $seats;
}

function get_seat_availability($trips_id, $selected_seat = null) {
    $db = getDB();
    
    if (!$db) {
        return ['error' => 'Veritabanı bağlantı hatası'];
    }

    try {
        // Seferin toplam koltuk sayısını ve otobüs tipini al
        $trip_query = $db->prepare("SELECT total_seats, bus_type FROM trips WHERE id = :trips_id");
        $trip_query->execute([':trips_id' => $trips_id]);
        $trip_data = $trip_query->fetch();
        
        if (!$trip_data) {
            return ['error' => 'Sefer bulunamadı'];
        }
        
        $total_seats = (int)$trip_data['total_seats'];
        $bus_type = $trip_data['bus_type'] ?? '2+2';
        
        // Tüm rezerve koltukları al (seat_number artık VARCHAR)
        $booked_query = $db->prepare("
            SELECT seat_number
            FROM booked_seats 
            WHERE trips_id = :trips_id AND booked_seat = 'booked'
            ORDER BY seat_number
        ");
        $booked_query->execute([':trips_id' => $trips_id]);
        $booked_seats = $booked_query->fetchAll(PDO::FETCH_COLUMN);
        
        // Koltuk düzenini oluştur
        $seats = generateSeatLayout($bus_type, $total_seats, $booked_seats);
        
        return [
            'trips_id' => $trips_id,
            'total_seats' => $total_seats,
            'bus_type' => $bus_type,
            'seats' => $seats,
            'booked_seats' => $booked_seats,
            'available_count' => $total_seats - count($booked_seats)
        ];
        
    } catch (PDOException $e) {
        error_log("Koltuk müsaitlik kontrol hatası: " . $e->getMessage());
        return ['error' => 'Koltuk durumu kontrol edilemedi: ' . $e->getMessage()];
    }
}

// Kullanıcının biletlerini getir 
function get_user_tickets($user_id = null) {
    $db = getDB();
    
    if (!$db) {
        return [];
    }
    
    // Eğer user_id verilmemişse, session'dan al
    if ($user_id === null) {
        if (!is_logged_in()) {
            return [];
        }
        $user_id = $_SESSION['id'];
    }
    
    // Güvenlik: Sadece kendi biletlerini görebilir (admin hariç)
    if (!is_admin() && isset($_SESSION['id']) && $user_id != $_SESSION['id']) {
        error_log("Yetkisiz bilet erişim denemesi: User {$_SESSION['id']} tried to access tickets of User $user_id");
        return [];
    }
    
    try {
        $query = $db->prepare("
            SELECT 
                t.id,
                t.trips_id,
                t.ticket_code,
                t.total_price,
                t.ticket_count,
                t.discount,
                t.status,
                t.created_at,
                tr.departure_city,
                tr.arrival_city,
                tr.departure_time,
                tr.arrival_time,
                tr.bus_type,
                f.name as firm_name,
                GROUP_CONCAT(bs.seat_number, ', ') as seat_numbers
            FROM tickets t
            JOIN trips tr ON t.trips_id = tr.id
            JOIN firms f ON tr.firm_id = f.id
            LEFT JOIN booked_seats bs ON t.id = bs.tickets_id
            WHERE t.user_id = :user_id
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
        
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Bilet getirme hatası: " . $e->getMessage());
        return [];
    }
}

// Tek bir bileti getir 
function get_ticket_by_code($ticket_code, $user_id = null) {
    $db = getDB();
    
    if (!$db) {
        return null;
    }
    
    // Eğer user_id verilmemişse, session'dan al
    if ($user_id === null) {
        if (!is_logged_in()) {
            return null;
        }
        $user_id = $_SESSION['id'];
    }
    
    // Ekstra güvenlik: Session yoksa NULL döndür
    if (!isset($_SESSION['id'])) {
        error_log("Session olmadan bilet erişim denemesi: Ticket Code $ticket_code");
        return null;
    }
    
    try {
        $query = $db->prepare("
            SELECT 
                t.*,
                tr.departure_city,
                tr.arrival_city,
                tr.departure_time,
                tr.arrival_time,
                tr.bus_type,
                f.name as firm_name,
                u.full_name as passenger_name,
                u.email as passenger_email,
                GROUP_CONCAT(bs.seat_number, ', ') as seat_numbers
            FROM tickets t
            JOIN trips tr ON t.trips_id = tr.id
            JOIN firms f ON tr.firm_id = f.id
            JOIN users u ON t.user_id = u.id
            LEFT JOIN booked_seats bs ON t.id = bs.tickets_id
            WHERE t.ticket_code = :ticket_code
            GROUP BY t.id
        ");
        
        $query->execute([':ticket_code' => $ticket_code]);
        $ticket = $query->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            return null;
        }
        
        // Güvenlik: Sadece bilet sahibi veya admin görebilir
        if (!is_admin() && $ticket['user_id'] != $_SESSION['id']) {
            error_log("Yetkisiz bilet erişim denemesi: User {$_SESSION['id']} tried to access ticket {$ticket_code} owned by User {$ticket['user_id']}");
            return null;
        }
        
        return $ticket;
        
    } catch (Exception $e) {
        error_log("Bilet getirme hatası: " . $e->getMessage());
        return null;
    }
}

function cancel_ticket($ticket_code,$user_id=null){
    $db=getDB();
   
   $ticket=get_ticket_by_code($ticket_code,$user_id);
   if(!$ticket){
        return ['success' => false, 'message' => 'Bilet bulunamadı'];
    }
    if($ticket['status']==='cancelled'){
        return ['success' => false, 'message' => 'Bilet zaten iptal edilmiş'];
    }
    $dt=strtotime($ticket['departure_time']);
    $now=time();
    $time_until_departure=($dt-$now)/3600; //saate çevirme
    if($time_until_departure < 1){
             return [
                'success' => false, 
                'message' => 'Sefer saatine 1 saatten az kaldığı için iptal işlemi yapılamaz',
                'time_remaining' => round($time_until_departure * 60) . ' dakika'
            ];
    }
   // İptal işlemini başlatma
    $db->beginTransaction();
    try{
    //1.adım tickets status cancelled yapma
    $update_ticket=$db->prepare("UPDATE tickets SET status='cancelled' WHERE id=:ticket_id");
    $update_ticket->execute([':ticket_id' => $ticket['id']]);

    //2.adım booked_seats tablosundan booked_seat not_booked yapma
    $update_booked=$db->prepare("UPDATE booked_seats SET booked_seat='not_booked' WHERE tickets_id=:ticket_id");
    $update_booked->execute([':ticket_id' => $ticket['id']]);

    //3.adım trips tablosundan available_seats artırma
    $update_trips=$db->prepare("UPDATE trips SET available_seats=available_seats + :ticket_count WHERE id=:trips_id");
    $update_trips->execute([':ticket_count' => $ticket['ticket_count'],
                            ':trips_id' => $ticket['trips_id']]);

    $refund=$ticket['total_price'];
    
    $update_balance=$db->prepare("UPDATE users SET balance=balance + :refund WHERE id=:user_id");
    $update_balance->execute([':refund' => $refund,
                              'user_id' => $ticket['user_id']]);  

    $db->commit();// Tüm işlemler başarılıysa Transaction'a commit

    if ($user_id == $_SESSION['id']) {
                $_SESSION['bakiye'] = get_balance();
            }
            
            return [
                'success' => true,
                'message' => "Bilet başarıyla iptal edildi. {$refund}₺ iade yapıldı.",
                'refund_amount' => $refund,
                'cancelled_seats' => $ticket['ticket_count'],
                'new_balance' => get_balance()
            ];

    }catch (Exception $e) {
            // Hata olursa geri al
            $db->rollBack();
            error_log("Bilet iptal hatası: " . $e->getMessage());
            return ['success' => false, 'message' => 'İptal işlemi başarısız: ' . $e->getMessage()];
    }




}

// ========================================
// ADMIN PANEL - BACKEND FONKSİYONLARI
// ========================================

/**
 * Sistem geneli istatistikler (Sadece Admin)
 */
function get_system_stats() {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return [
            'total_firms' => 0,
            'total_users' => 0,
            'total_trips' => 0,
            'total_tickets_sold' => 0
        ];
    }
    
    try {
        $stats = [];
        
        // Toplam firma
        $stats['total_firms'] = $db->query("SELECT COUNT(*) FROM firms")->fetchColumn();
        
        // Toplam kullanıcı
        $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        // Toplam sefer
        $stats['total_trips'] = $db->query("SELECT COUNT(*) FROM trips")->fetchColumn();
        
        // Satılan bilet sayısı
        $stats['total_tickets_sold'] = $db->query("SELECT COUNT(*) FROM tickets WHERE status='active'")->fetchColumn();
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Sistem istatistik hatası: " . $e->getMessage());
        return [
            'total_firms' => 0,
            'total_users' => 0,
            'total_trips' => 0,
            'total_tickets_sold' => 0
        ];
    }
}

/**
 * Tüm firmaları listele (Sadece Admin)
 */
function get_all_firms($filters = []) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return [];
    }
    
    try {
        $sql = "SELECT * FROM firms WHERE 1=1";
        $params = [];
        
        // Arama filtresi
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Durum filtresi
        if (isset($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY name ASC";
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Firma listesi hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Tek firma getir (ID ile)
 */
function get_firm($firm_id) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return null;
    }
    
    try {
        $query = $db->prepare("SELECT * FROM firms WHERE id = :firm_id");
        $query->execute([':firm_id' => $firm_id]);
        return $query->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Firma getirme hatası: " . $e->getMessage());
        return null;
    }
}

/**
 * Yeni firma oluştur
 */
function create_firm($data) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return false;
    }
    
    try {
        $query = $db->prepare("
            INSERT INTO firms (name, description, phone, email, address, logo, status) 
            VALUES (:name, :description, :phone, :email, :address, :logo, :status)
        ");
        
        $result = $query->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':logo' => $data['logo'] ?? 'default-logo.png',
            ':status' => $data['status'] ?? 'active'
        ]);
        
        return $result ? $db->lastInsertId() : false;
        
    } catch (Exception $e) {
        error_log("Firma oluşturma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Firma güncelle
 */
function update_firm($firm_id, $data) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return false;
    }
    
    try {
        $query = $db->prepare("
            UPDATE firms 
            SET name = :name, 
                description = :description, 
                phone = :phone, 
                email = :email, 
                address = :address, 
                logo = :logo, 
                status = :status 
            WHERE id = :firm_id
        ");
        
        return $query->execute([
            ':firm_id' => $firm_id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':logo' => $data['logo'] ?? 'default-logo.png',
            ':status' => $data['status'] ?? 'active'
        ]);
        
    } catch (Exception $e) {
        error_log("Firma güncelleme hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Firma sil (soft delete - status='inactive')
 */
function delete_firm($firm_id) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return false;
    }
    
    try {
        $query = $db->prepare("UPDATE firms SET status = 'inactive' WHERE id = :firm_id");
        return $query->execute([':firm_id' => $firm_id]);
        
    } catch (Exception $e) {
        error_log("Firma silme hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Tüm kullanıcıları listele (Sadece Admin)
 */
function get_all_users($role = null) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return [];
    }
    
    try {
        $sql = "SELECT u.*, f.name as firm_name FROM users u LEFT JOIN firms f ON u.firm_id = f.id WHERE 1=1";
        $params = [];
        
        if ($role !== null) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $role;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Kullanıcı listesi hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Firma admin kullanıcısı oluştur
 */
function create_firm_admin($username, $password, $fullname, $email, $firm_id) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return false;
    }
    
    try {
        // Kullanıcı adı kontrolü
        $check = $db->prepare("SELECT id FROM users WHERE username = :username");
        $check->execute([':username' => $username]);
        
        if ($check->fetch()) {
            return false; // Kullanıcı zaten var
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = $db->prepare("
            INSERT INTO users (username, password, full_name, email, role, balance, firm_id) 
            VALUES (:username, :password, :full_name, :email, :role, 0, :firm_id)
        ");
        
        $result = $query->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':full_name' => $fullname,
            ':email' => $email,
            ':role' => 'firmadmin',
            ':firm_id' => $firm_id
        ]);
        
        return $result ? $db->lastInsertId() : false;
        
    } catch (Exception $e) {
        error_log("Firma admin oluşturma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Tüm seferleri listele (Sadece Admin)
 */
function get_all_trips_admin($filters = []) {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return [];
    }
    
    try {
        $sql = "
            SELECT 
                t.id,
                t.departure_city,
                t.arrival_city,
                t.departure_time,
                t.arrival_time,
                t.price,
                t.total_seats,
                t.available_seats,
                t.bus_type,
                f.name as firm_name,
                (t.total_seats - t.available_seats) as booked_seats
            FROM trips t
            JOIN firms f ON t.firm_id = f.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Firma filtresi
        if (isset($filters['firm_id'])) {
            $sql .= " AND t.firm_id = :firm_id";
            $params[':firm_id'] = $filters['firm_id'];
        }
        
        // Tarih filtresi
        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(t.departure_time) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        $sql .= " ORDER BY t.departure_time DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Admin sefer listesi hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Global kuponları listele (Sadece Admin)
 */
function get_global_coupons() {
    $db = getDB();
    
    if (!$db || !is_admin()) {
        return [];
    }
    
    try {
        $query = $db->prepare("
            SELECT 
                c.id,
                c.code,
                c.discount_percentage,
                c.usage_limit,
                c.expire_date,
                (SELECT COUNT(*) FROM tickets WHERE coupon_code = c.code) as used_count
            FROM coupons c
            WHERE c.firm_id IS NULL
            ORDER BY c.expire_date DESC
        ");
        
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Global kupon listesi hatası: " . $e->getMessage());
        return [];
    }
}

// ========================================
// FİRMA ADMİN - BACKEND FONKSİYONLARI
// ========================================

/**
 * Firma dashboard istatistikleri
 */
function get_firm_dashboard_stats($firm_id) {
    $db = getDB();
    
    if (!$db) {
        return [
            'my_trips' => 0,
            'tickets_sold' => 0,
            'total_revenue' => 0,
            'active_coupons' => 0
        ];
    }
    
    // Yetki kontrolü
    if (is_firm_admin()) {
        $user_firm_id = $_SESSION['firm_id'] ?? null;
        if ($user_firm_id != $firm_id) {
            error_log("Yetkisiz erişim: Firma $firm_id istatistikleri");
            return ['error' => 'Yetkisiz erişim'];
        }
    }
    
    try {
        // Toplam sefer sayısı
        $trips_query = $db->prepare("SELECT COUNT(*) FROM trips WHERE firm_id = :firm_id");
        $trips_query->execute([':firm_id' => $firm_id]);
        $my_trips = $trips_query->fetchColumn();
        
        // Satılan bilet sayısı ve toplam gelir
        $sales_query = $db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) as tickets_sold,
                COALESCE(SUM(t.total_price), 0) as total_revenue
            FROM tickets t
            JOIN trips tr ON t.trips_id = tr.id
            WHERE tr.firm_id = :firm_id 
            AND t.status = 'active'
        ");
        $sales_query->execute([':firm_id' => $firm_id]);
        $sales = $sales_query->fetch(PDO::FETCH_ASSOC);
        
        // Aktif kupon sayısı
        $coupons_query = $db->prepare("
            SELECT COUNT(*) 
            FROM coupons 
            WHERE firm_id = :firm_id 
            AND expire_date > CURRENT_TIMESTAMP
        ");
        $coupons_query->execute([':firm_id' => $firm_id]);
        $active_coupons = $coupons_query->fetchColumn();
        
        return [
            'my_trips' => (int)$my_trips,
            'tickets_sold' => (int)$sales['tickets_sold'],
            'total_revenue' => (float)$sales['total_revenue'],
            'active_coupons' => (int)$active_coupons
        ];
        
    } catch (Exception $e) {
        error_log("Firma istatistik hatası: " . $e->getMessage());
        return [
            'my_trips' => 0,
            'tickets_sold' => 0,
            'total_revenue' => 0,
            'active_coupons' => 0
        ];
    }
}

/**
 * Firmaya ait seferleri getir
 */
function get_firm_trips($firm_id, $filters = []) {
    $db = getDB();
    
    if (!$db) {
        return [];
    }
    
    // Yetki kontrolü
    if (is_firm_admin()) {
        $user_firm_id = $_SESSION['firm_id'] ?? null;
        if ($user_firm_id != $firm_id) {
            error_log("Yetkisiz erişim: Firma $firm_id seferleri");
            return [];
        }
    }
    
    try {
        $sql = "
            SELECT 
                t.id,
                t.departure_city,
                t.arrival_city,
                t.departure_time,
                t.arrival_time,
                t.price,
                t.total_seats,
                t.available_seats,
                t.bus_type,
                (t.total_seats - t.available_seats) as booked_seats
            FROM trips t
            WHERE t.firm_id = :firm_id
        ";
        
        $params = [':firm_id' => $firm_id];
        
        // Sadece gelecekteki seferler
        if (isset($filters['upcoming']) && $filters['upcoming']) {
            $sql .= " AND t.departure_time > CURRENT_TIMESTAMP";
            // Yaklaşan seferler için en yakın tarih önce (ASC)
            $sql .= " ORDER BY t.departure_time ASC";
        } else {
            // Tüm seferler için en yeni tarih önce (DESC)
            $sql .= " ORDER BY t.departure_time DESC";
        }
        
        // Limit
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Firma seferleri hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Firmaya ait kuponları getir
 */
function get_firm_coupons($firm_id) {
    $db = getDB();
    
    if (!$db) {
        return [];
    }
    
    // Yetki kontrolü
    if (is_firm_admin()) {
        $user_firm_id = $_SESSION['firm_id'] ?? null;
        if ($user_firm_id != $firm_id) {
            error_log("Yetkisiz erişim: Firma $firm_id kuponları");
            return [];
        }
    }
    
    try {
        $query = $db->prepare("
            SELECT 
                c.id,
                c.code,
                c.discount_percentage,
                c.usage_limit,
                c.expire_date,
                (SELECT COUNT(*) FROM tickets WHERE coupon_code = c.code) as used_count
            FROM coupons c
            WHERE c.firm_id = :firm_id
            AND c.expire_date > CURRENT_TIMESTAMP
            ORDER BY c.expire_date ASC
        ");
        
        $query->execute([':firm_id' => $firm_id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Firma kuponları hatası: " . $e->getMessage());
        return [];
    }
}

// ========================================
// ORTAK FONKSİYONLAR (Admin + FirmaAdmin)
// ========================================

/**
 * Popüler güzergahlar
 */
function get_popular_routes($firm_id = null, $limit = 5) {
    $db = getDB();
    
    if (!$db) {
        return [];
    }
    
    // Firma admin ise sadece kendi firması
    if (is_firm_admin() && $firm_id === null) {
        $firm_id = $_SESSION['firm_id'] ?? null;
    }
    
    if (is_firm_admin() && $firm_id != $_SESSION['firm_id']) {
        return [];
    }
    
    try {
        $sql = "
            SELECT 
                CONCAT(tr.departure_city, ' → ', tr.arrival_city) as route,
                COUNT(t.id) as sales
            FROM tickets t
            JOIN trips tr ON t.trips_id = tr.id
            WHERE t.status = 'active'
        ";
        
        $params = [];
        
        if ($firm_id !== null) {
            $sql .= " AND tr.firm_id = :firm_id";
            $params[':firm_id'] = $firm_id;
        }
        
        $sql .= " 
            GROUP BY route
            ORDER BY sales DESC
            LIMIT " . (int)$limit;
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Popüler güzergahlar hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Gelir grafiği
 */
function get_revenue_chart($firm_id = null, $days = 7) {
    $db = getDB();
    
    if (!$db) {
        return ['dates' => [], 'revenues' => []];
    }
    
    // Firma admin ise sadece kendi firması
    if (is_firm_admin() && $firm_id === null) {
        $firm_id = $_SESSION['firm_id'] ?? null;
    }
    
    if (is_firm_admin() && $firm_id != $_SESSION['firm_id']) {
        return ['dates' => [], 'revenues' => []];
    }
    
    try {
        $sql = "
            SELECT 
                DATE(t.created_at) as date,
                COALESCE(SUM(t.total_price), 0) as revenue
            FROM tickets t
        ";
        
        if ($firm_id !== null) {
            $sql .= " JOIN trips tr ON t.trips_id = tr.id WHERE tr.firm_id = :firm_id AND";
        } else {
            $sql .= " WHERE";
        }
        
        $sql .= " t.status = 'active'
            AND t.created_at >= DATE('now', '-" . (int)$days . " days')
            GROUP BY DATE(t.created_at)
            ORDER BY date ASC
        ";
        
        $query = $db->prepare($sql);
        
        if ($firm_id !== null) {
            $query->execute([':firm_id' => $firm_id]);
        } else {
            $query->execute();
        }
        
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $dates = [];
        $revenues = [];
        
        foreach ($results as $row) {
            $dates[] = $row['date'];
            $revenues[] = (float)$row['revenue'];
        }
        
        return [
            'dates' => $dates,
            'revenues' => $revenues
        ];
        
    } catch (Exception $e) {
        error_log("Gelir grafiği hatası: " . $e->getMessage());
        return ['dates' => [], 'revenues' => []];
    }
}

/**
 * Son bilet satışlarını getir (Admin ve Firma Admin için)
 */
function recent_ticket($firm_id = null) {
    $db = getDB();
    
    if (!$db) {
        return [];
    }
    
    try {
        $sql = "
            SELECT 
                t.id,
                t.ticket_code,
                t.total_price,
                t.ticket_count,
                t.created_at,
                tr.departure_city,
                tr.arrival_city,
                tr.departure_time,
                u.full_name,
                f.name as firm_name
            FROM tickets t
            JOIN trips tr ON t.trips_id = tr.id
            JOIN users u ON t.user_id = u.id
            JOIN firms f ON tr.firm_id = f.id
            WHERE t.status = 'active'
        ";
        
        $params = [];
        
        // Firma admin ise sadece kendi firmasının biletleri
        if (is_firm_admin() && isset($_SESSION['firm_id'])) {
            $sql .= " AND tr.firm_id = :firm_id";
            $params[':firm_id'] = $_SESSION['firm_id'];
        } elseif ($firm_id !== null) {
            // Admin belirli bir firma filtresi uygulayabilir
            $sql .= " AND tr.firm_id = :firm_id";
            $params[':firm_id'] = $firm_id;
        }
        
        // En yeni biletler önce (created_at veya id'ye göre)
        $sql .= " ORDER BY COALESCE(t.created_at, datetime(t.id, 'unixepoch')) DESC LIMIT 10";
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Son satışlar hatası: " . $e->getMessage());
        return [];
    }
}


function get_trip_by_id($trip_id) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "SELECT 
                    tr.*,
                    f.name as firm_name
                FROM trips tr
                LEFT JOIN firms f ON tr.firm_id = f.id
                WHERE tr.id = :trip_id
                LIMIT 1";
        
        $query = $db->prepare($sql);
        $query->execute([':trip_id' => $trip_id]);
        return $query->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Sefer detay hatası: " . $e->getMessage());
        return false;
    }
}

// ========================================
// SEFER YÖNETİMİ - CRUD FONKSİYONLARI
// ========================================

/**
 * Yeni sefer oluştur
 * @param array $data Sefer bilgileri
 * @return bool Başarılı ise true
 */
function create_trip($data) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO trips (
                    firm_id, 
                    departure_city, 
                    arrival_city, 
                    departure_time, 
                    arrival_time, 
                    price, 
                    total_seats, 
                    available_seats, 
                    bus_type
                ) VALUES (
                    :firm_id, 
                    :departure_city, 
                    :arrival_city, 
                    :departure_time, 
                    :arrival_time, 
                    :price, 
                    :total_seats, 
                    :available_seats, 
                    :bus_type
                )";
        
        $query = $db->prepare($sql);
        $result = $query->execute([
            ':firm_id' => $data['firm_id'],
            ':departure_city' => $data['departure_city'],
            ':arrival_city' => $data['arrival_city'],
            ':departure_time' => $data['departure_time'],
            ':arrival_time' => $data['arrival_time'],
            ':price' => $data['price'],
            ':total_seats' => $data['total_seats'],
            ':available_seats' => $data['available_seats'],
            ':bus_type' => $data['bus_type']
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Sefer oluşturma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Sefer güncelle
 * @param int $trip_id Sefer ID
 * @param array $data Güncellenecek bilgiler
 * @return bool Başarılı ise true
 */
function update_trip($trip_id, $data) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "UPDATE trips SET
                    firm_id = :firm_id,
                    departure_city = :departure_city,
                    arrival_city = :arrival_city,
                    departure_time = :departure_time,
                    arrival_time = :arrival_time,
                    price = :price,
                    total_seats = :total_seats,
                    available_seats = :available_seats,
                    bus_type = :bus_type
                WHERE id = :trip_id";
        
        $query = $db->prepare($sql);
        $result = $query->execute([
            ':trip_id' => $trip_id,
            ':firm_id' => $data['firm_id'],
            ':departure_city' => $data['departure_city'],
            ':arrival_city' => $data['arrival_city'],
            ':departure_time' => $data['departure_time'],
            ':arrival_time' => $data['arrival_time'],
            ':price' => $data['price'],
            ':total_seats' => $data['total_seats'],
            ':available_seats' => $data['available_seats'],
            ':bus_type' => $data['bus_type']
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Sefer güncelleme hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Sefer sil
 * @param int $trip_id Sefer ID
 * @return bool Başarılı ise true
 */
function delete_trip($trip_id) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        // Önce bu sefere ait bilet var mı kontrol et
        $check = $db->prepare("SELECT COUNT(*) as count FROM tickets WHERE trips_id = :trip_id");
        $check->execute([':trip_id' => $trip_id]);
        $result = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            error_log("Sefer silinemez: Aktif biletler var (trip_id: $trip_id)");
            return false;
        }
        
        // Bilet yoksa seferi sil
        $query = $db->prepare("DELETE FROM trips WHERE id = :trip_id");
        return $query->execute([':trip_id' => $trip_id]);
        
    } catch (Exception $e) {
        error_log("Sefer silme hatası: " . $e->getMessage());
        return false;
    }
}

// ========================================
// KUPON YÖNETİMİ - CRUD FONKSİYONLARI
// ========================================

/**
 * Yeni kupon oluştur
 * @param array $data Kupon bilgileri
 * @return bool Başarılı ise true
 */
function create_coupon($data) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO coupons (
                    code, 
                    discount_percentage, 
                    usage_limit, 
                    expire_date,
                    firm_id
                ) VALUES (
                    :code, 
                    :discount_percentage, 
                    :usage_limit, 
                    :expire_date,
                    :firm_id
                )";
        
        $query = $db->prepare($sql);
        $result = $query->execute([
            ':code' => strtoupper($data['code']),
            ':discount_percentage' => $data['discount_percentage'],
            ':usage_limit' => $data['usage_limit'],
            ':expire_date' => $data['expire_date'],
            ':firm_id' => $data['firm_id'] ?? null // NULL ise global kupon
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Kupon oluşturma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Kupon güncelle
 * @param int $coupon_id Kupon ID
 * @param array $data Güncellenecek bilgiler
 * @return bool Başarılı ise true
 */
function update_coupon($coupon_id, $data) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "UPDATE coupons SET
                    code = :code,
                    discount_percentage = :discount_percentage,
                    usage_limit = :usage_limit,
                    expire_date = :expire_date
                WHERE id = :coupon_id";
        
        $query = $db->prepare($sql);
        $result = $query->execute([
            ':coupon_id' => $coupon_id,
            ':code' => strtoupper($data['code']),
            ':discount_percentage' => $data['discount_percentage'],
            ':usage_limit' => $data['usage_limit'],
            ':expire_date' => $data['expire_date']
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Kupon güncelleme hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Kupon sil
 * @param int $coupon_id Kupon ID
 * @return bool Başarılı ise true
 */
function delete_coupon($coupon_id) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        // Kuponu sil (kullanım geçmişi korunur)
        $query = $db->prepare("DELETE FROM coupons WHERE id = :coupon_id");
        return $query->execute([':coupon_id' => $coupon_id]);
        
    } catch (Exception $e) {
        error_log("Kupon silme hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Kupon detayını ID'ye göre getir
 * @param int $coupon_id Kupon ID
 * @return array|false Kupon detayları veya false
 */
function get_coupon_by_id($coupon_id) {
    $db = getDB();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "SELECT 
                    c.*,
                    f.name as firm_name,
                    (SELECT COUNT(*) FROM tickets WHERE coupon_code = c.code) as used_count
                FROM coupons c
                LEFT JOIN firms f ON c.firm_id = f.id
                WHERE c.id = :coupon_id
                LIMIT 1";
        
        $query = $db->prepare($sql);
        $query->execute([':coupon_id' => $coupon_id]);
        return $query->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Kupon detay hatası: " . $e->getMessage());
        return false;
    }
}






