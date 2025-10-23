<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB(); // Doğru veritabanı bağlantısını al
    echo "Veri ekleniyor...\n";

    // Test kullanıcıları ekle
    echo "Test kullanıcıları ekleniyor...\n";
    
    $userStmt = $db->prepare("
        INSERT OR IGNORE INTO users (username, full_name, email, password, role, balance, firm_id) 
        VALUES (:username, :full_name, :email, :password, :role, :balance, :firm_id)
    ");

    $testUsers = [
        // [username, full_name, email, password, role, balance, firm_id]
        ['admin', 'Admin Kullanıcı', 'admin@trendway.com', password_hash('123456', PASSWORD_DEFAULT), 'admin', 5000, null],
        ['demo', 'Demo User', 'demo@trendway.com', password_hash('demo123', PASSWORD_DEFAULT), 'user', 250, null]
    ];

    foreach ($testUsers as $user) {
        $userStmt->execute([
            ':username' => $user[0],
            ':full_name' => $user[1],
            ':email' => $user[2],
            ':password' => $user[3],
            ':role' => $user[4],
            ':balance' => $user[5],
            ':firm_id' => $user[6]
        ]);
    }
    echo "Test kullanıcıları eklendi ✅\n";

    // 10 firmayı ekle
    echo "Firmalar ekleniyor...\n";
    $firmStmt = $db->prepare("
        INSERT OR IGNORE INTO firms (name, description, phone, email, address) 
        VALUES (:name, :description, :phone, :email, :address)
    ");

    $firms = [
        ['Metro Turizm', 'Konforlu şehirler arası taşımacılık', '0212 555 00 11', 'info@metro.com', 'İstanbul'],
        ['Kamil Koç', 'Türkiye’nin en eski otobüs markası', '0212 444 05 62', 'destek@kamilkoc.com', 'Bursa'],
        ['Pamukkale', 'Yolculukta kalite ve güven', '0258 555 12 12', 'info@pamukkale.com', 'Denizli'],
        ['Nilüfer Turizm', 'Güvenli ve hızlı ulaşım', '0224 444 00 22', 'info@niluferturizm.com', 'Bursa'],
        ['Varan Turizm', 'Konforlu ve elit seyahat', '0216 222 22 22', 'info@varan.com', 'İstanbul'],
        ['Efe Tur', 'Her yolda yanınızda', '0232 555 66 77', 'info@efetur.com', 'İzmir'],
        ['Ulusoy', 'Türkiye’nin yol klasiği', '0312 444 55 66', 'info@ulusoy.com', 'Ankara'],
        ['Lüks Artvin', 'Karadeniz’in lider firması', '0466 555 77 88', 'info@luxartvin.com', 'Artvin'],
        ['Kontur Turizm', 'Anadolu’nun her noktasına', '0332 444 88 99', 'info@kontur.com', 'Konya'],
        ['Has Turizm', 'Güvenle yollarda', '0342 222 33 44', 'info@has.com', 'Gaziantep']
    ];

    foreach ($firms as $f) {
        $firmStmt->execute([
            ':name' => $f[0],
            ':description' => $f[1],
            ':phone' => $f[2],
            ':email' => $f[3],
            ':address' => $f[4]
        ]);
    }

    echo "Firmalar eklendi ✅\n";

    // Firma adminleri ekle (firmaları ekledikten sonra)
    echo "Firma adminleri ekleniyor...\n";
    
    // İlk firmayı (Metro Turizm - ID:1) kullanarak firma admin ekle
    $firmAdminStmt = $db->prepare("
        INSERT OR IGNORE INTO users (username, full_name, email, password, role, balance, firm_id)
        VALUES (:username, :full_name, :email, :password, :role, :balance, :firm_id)
    ");
    
    $firmAdmins = [
        ['firmtest', 'Firma Test Admin', 'firmtest@example.com', password_hash('123456', PASSWORD_DEFAULT), 'firmadmin', 0, 1],
        ['metro_admin', 'Metro Admin', 'admin@metro.com', password_hash('123456', PASSWORD_DEFAULT), 'firmadmin', 0, 1],
        ['pamukkale_admin', 'Pamukkale Admin', 'admin@pamukkale.com', password_hash('123456', PASSWORD_DEFAULT), 'firmadmin', 0, 3]
    ];
    
    foreach ($firmAdmins as $admin) {
        $firmAdminStmt->execute([
            ':username' => $admin[0],
            ':full_name' => $admin[1],
            ':email' => $admin[2],
            ':password' => $admin[3],
            ':role' => $admin[4],
            ':balance' => $admin[5],
            ':firm_id' => $admin[6]
        ]);
    }
    
    echo "Firma adminleri eklendi ✅\n";

    // Popüler rotalar tanımla (gerçekçi sefer planı)
    echo "Seferler ekleniyor...\n";
    $tripStmt = $db->prepare("
        INSERT OR IGNORE INTO trips (firm_id, departure_city, arrival_city, departure_time, arrival_time, price, available_seats, total_seats, bus_type)
        VALUES (:firm_id, :departure_city, :arrival_city, :departure_time, :arrival_time, :price, :available_seats, :total_seats, :bus_type)
    ");

    // Popüler rotalar ve fiyatları
    $popular_routes = [
        // İSTANBUL çıkışlı
        ['İstanbul', 'Ankara', 350, 5], ['İstanbul', 'İzmir', 280, 4], ['İstanbul', 'Bursa', 180, 2],
        ['İstanbul', 'Antalya', 450, 8], ['İstanbul', 'Adana', 400, 7], ['İstanbul', 'Trabzon', 380, 9],
        ['İstanbul', 'Samsun', 320, 6], ['İstanbul', 'Konya', 290, 5], ['İstanbul', 'Eskişehir', 220, 3],
        ['İstanbul', 'Denizli', 350, 6], ['İstanbul', 'Gaziantep', 420, 8], ['İstanbul', 'Kayseri', 340, 6],

        // ANKARA çıkışlı  
        ['Ankara', 'İstanbul', 350, 5], ['Ankara', 'İzmir', 300, 5], ['Ankara', 'Antalya', 320, 6],
        ['Ankara', 'Bursa', 250, 4], ['Ankara', 'Konya', 180, 3], ['Ankara', 'Kayseri', 200, 4],
        ['Ankara', 'Samsun', 280, 5], ['Ankara', 'Trabzon', 350, 7], ['Ankara', 'Adana', 290, 5],

        // İZMİR çıkışlı
        ['İzmir', 'İstanbul', 280, 4], ['İzmir', 'Ankara', 300, 5], ['İzmir', 'Antalya', 250, 5],
        ['İzmir', 'Bursa', 200, 3], ['İzmir', 'Denizli', 150, 2], ['İzmir', 'Muğla', 120, 2],
        ['İzmir', 'Manisa', 80, 1], ['İzmir', 'Aydın', 100, 1], ['İzmir', 'Uşak', 130, 2],

        // BURSA çıkışlı
        ['Bursa', 'İstanbul', 180, 2], ['Bursa', 'Ankara', 250, 4], ['Bursa', 'İzmir', 200, 3],
        ['Bursa', 'Antalya', 380, 7], ['Bursa', 'Eskişehir', 120, 2], ['Bursa', 'Balıkesir', 100, 2],
        ['Bursa', 'Çanakkale', 150, 3], ['Bursa', 'Sakarya', 80, 1],

        // ANTALYA çıkışlı
        ['Antalya', 'İstanbul', 450, 8], ['Antalya', 'Ankara', 320, 6], ['Antalya', 'İzmir', 250, 5],
        ['Antalya', 'Bursa', 380, 7], ['Antalya', 'Mersin', 180, 3], ['Antalya', 'Adana', 200, 4],
        ['Antalya', 'Konya', 220, 4], ['Antalya', 'Denizli', 200, 4],

        // DİĞER ÖNEMLİ ROTALAR
        ['Adana', 'İstanbul', 400, 7], ['Adana', 'Ankara', 290, 5], ['Adana', 'Mersin', 80, 1],
        ['Trabzon', 'İstanbul', 380, 9], ['Trabzon', 'Ankara', 350, 7], ['Trabzon', 'Samsun', 120, 2],
        ['Samsun', 'İstanbul', 320, 6], ['Samsun', 'Ankara', 280, 5], ['Samsun', 'Trabzon', 120, 2],
        ['Gaziantep', 'İstanbul', 420, 8], ['Gaziantep', 'Ankara', 320, 6], ['Gaziantep', 'Adana', 150, 3],
        ['Konya', 'İstanbul', 290, 5], ['Konya', 'Ankara', 180, 3], ['Konya', 'Antalya', 220, 4],
        ['Eskişehir', 'İstanbul', 220, 3], ['Eskişehir', 'Ankara', 150, 2], ['Eskişehir', 'Bursa', 120, 2]
    ];

    // Gerçek firma ID'lerini çek
    $firm_ids = [];
    $firm_result = $db->query("SELECT id FROM firms");
    while ($row = $firm_result->fetch(PDO::FETCH_ASSOC)) {
        $firm_ids[] = $row['id'];
    }

    // Her rotayı farklı firmalar ve saatlerde oluştur
    $sefer_count = 0;
    foreach ($popular_routes as $route) {
        $departure_city = $route[0];
        $arrival_city = $route[1]; 
        $base_price = $route[2];
        $base_duration = $route[3]; // saat

        // Her rota için 2-4 farklı sefer oluştur (farklı firmalar ve saatler)
        $sefer_per_route = rand(2, 4);
        
        for ($j = 0; $j < $sefer_per_route; $j++) {
            // Rastgele firma seç (gerçek ID'lerden)
            $firm_id = $firm_ids[array_rand($firm_ids)];
            
            // Rastgele tarih (önümüzdeki 10 gün)
            $day_offset = rand(0, 10);
            
            // Farklı saatler (06:00-23:00 arası)
            $departure_hours = [6, 8, 10, 12, 14, 16, 18, 20, 22];
            $hour = $departure_hours[array_rand($departure_hours)];
            $minute = rand(0, 3) * 15; // 00, 15, 30, 45
            
            $departure_time = date('Y-m-d H:i:s', strtotime("+$day_offset days $hour:$minute"));
            $arrival_time = date('Y-m-d H:i:s', strtotime($departure_time . " +$base_duration hours"));
            
            // Fiyat varyasyonu (%±20)
            $price_variation = rand(-20, 20) / 100;
            $final_price = round($base_price * (1 + $price_variation));
            
            // Otobüs tipi rastgele seç (2+2 veya 2+1)
            $bus_types = ['2+2', '2+1'];
            $bus_type = $bus_types[array_rand($bus_types)];
            
            // Koltuk sayısı bus_type'a göre
            if ($bus_type === '2+2') {
                // 2+2 için 4'ün katları (32, 36, 40, 44, 48, 52)
                $seat_options = [32, 36, 40, 44, 48, 52];
                $total_seats = $seat_options[array_rand($seat_options)];
            } else {
                // 2+1 için 3'ün katları (27, 30, 33, 36, 39, 42, 45)
                $seat_options = [27, 30, 33, 36, 39, 42, 45];
                $total_seats = $seat_options[array_rand($seat_options)];
            }
            
            // Başlangıçta tüm koltuklar müsait
            $available_seats = $total_seats;
            
            $tripStmt->execute([
                ':firm_id' => $firm_id,
                ':departure_city' => $departure_city,
                ':arrival_city' => $arrival_city,
                ':departure_time' => $departure_time,
                ':arrival_time' => $arrival_time,
                ':price' => $final_price,
                ':available_seats' => $available_seats,
                ':total_seats' => $total_seats,
                ':bus_type' => $bus_type
            ]);
            
            $sefer_count++;
        }
    }

    echo "$sefer_count sefer başarıyla eklendi ✅\n";
    
    // SEED DATA İÇİN RASTGELE KOLTUK REZERVASYONLARI OLUŞTUR
    echo "\nRastgele koltuk rezervasyonları oluşturuluyor...\n";
    
    // İlk 50 sefere rastgele koltuk rezervasyonları ekle (henüz rezervasyonu OLMAYAN seferler)
    $booking_count = 0;
    $processed_trips = []; // İşlenmiş seferleri takip et
    $trips_to_book = $db->query("
        SELECT DISTINCT t.id, t.total_seats, t.departure_time, t.price 
        FROM trips t
        LEFT JOIN booked_seats b ON t.id = b.trips_id
        WHERE b.trips_id IS NULL
        ORDER BY RANDOM() 
        LIMIT 50
    ");
    
    foreach ($trips_to_book->fetchAll(PDO::FETCH_ASSOC) as $trip) {
        // Bu seferi daha önce işledik mi kontrol et
        if (in_array($trip['id'], $processed_trips)) {
            continue; // Zaten işlenmişse atla
        }
        $processed_trips[] = $trip['id']; // İşlenmiş olarak işaretle
        // Her sefer için rastgele %10-40 arası koltuk rezerve et
        $occupancy_rate = rand(10, 40) / 100;
        $seats_to_book = (int)round($trip['total_seats'] * $occupancy_rate);
        
        if ($seats_to_book == 0) continue; // Hiç koltuk rezerve edilmeyecekse atla
        
        // Rastgele koltuk numaraları seç (1'den total_seats'e kadar)
        $all_seats = range(1, $trip['total_seats']);
        shuffle($all_seats);
        $selected_seats = array_slice($all_seats, 0, $seats_to_book);
        
        // Bu sefer için sahte bir ticket oluştur (seed data için)
        $ticket_stmt = $db->prepare("
            INSERT INTO tickets (user_id, trips_id, ticket_code, total_price, ticket_count, status, created_at)
            VALUES (1, :trips_id, :ticket_code, :total_price, :ticket_count, 'active', :created_at)
        ");
        // Unique ticket code: SEED + timestamp + trip_id + random
        $ticket_code = 'SEED-' . time() . '-' . $trip['id'] . '-' . rand(1000, 9999);
        $ticket_stmt->execute([
            ':trips_id' => $trip['id'],
            ':ticket_code' => $ticket_code,
            ':total_price' => $trip['price'] * $seats_to_book, // Toplam fiyat (koltuk sayısı * birim fiyat)
            ':ticket_count' => $seats_to_book,
            ':created_at' => date('Y-m-d H:i:s', strtotime($trip['departure_time']) - 3600) // 1 saat önce
        ]);
        $ticket_id = $db->lastInsertId();
        
        // Seçilen koltuklara rezervasyon ekle
        $booking_stmt = $db->prepare("
            INSERT INTO booked_seats (tickets_id, trips_id, seat_number, booked_seat)
            VALUES (:ticket_id, :trips_id, :seat_number, 'booked')
        ");
        
        foreach ($selected_seats as $seat) {
            $booking_stmt->execute([
                ':ticket_id' => $ticket_id,
                ':trips_id' => $trip['id'],
                ':seat_number' => $seat
            ]);
            $booking_count++;
        }
        
        // trips tablosundaki available_seats'i güncelle
        $update_result = $db->exec("
            UPDATE trips 
            SET available_seats = total_seats - (
                SELECT COUNT(*) FROM booked_seats 
                WHERE trips_id = {$trip['id']} AND booked_seat = 'booked'
            )
            WHERE id = {$trip['id']}
        ");
    }
    
    echo "$booking_count koltuk rezervasyonu oluşturuldu ✅\n";
    
    // Özet bilgi ver
    echo "\n📊 SEFER ÖZETİ:\n";
    echo "================\n";
    $summary_stmt = $db->query("
        SELECT departure_city, COUNT(*) as sefer_sayisi 
        FROM trips 
        GROUP BY departure_city 
        ORDER BY sefer_sayisi DESC 
        LIMIT 10
    ");
    
    while ($row = $summary_stmt->fetch()) {
        echo "• " . $row['departure_city'] . ": " . $row['sefer_sayisi'] . " sefer\n";
    }
    
    echo "\n🎯 Toplam " . $sefer_count . " sefer eklendi!\n";

    // Kupon kodları ekleniyor
    echo "\nKupon kodları ekleniyor...\n";
    
    $couponStmt = $db->prepare("
        INSERT OR IGNORE INTO coupons (firm_id, code, discount_percentage, usage_limit, expire_date) 
        VALUES (:firm_id, :code, :discount_percentage, :usage_limit, :expire_date)
    ");

    // Rastgele kupon kodları oluştur
    function generateCouponCode($length = 8) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    $coupons = [
        // Global kuponlar (firm_id = NULL) - Yüzde indirimleri
        [null, generateCouponCode(6), 10, 50, '+30 days'],   // %10 indirim
        [null, generateCouponCode(6), 15, 25, '+25 days'],   // %15 indirim  
        [null, generateCouponCode(6), 20, 15, '+20 days'],   // %20 indirim
        [null, generateCouponCode(8), 25, 10, '+15 days'],   // %25 indirim
        [null, generateCouponCode(6), 30, 40, '+35 days'],   // %30 indirim
        [null, generateCouponCode(6), 35, 20, '+30 days'],   // %35 indirim
        
        // Özel global kuponlar
        [null, 'WELCOME10', 10, 100, '+60 days'],    // Hoş geldin kuponu
        [null, 'STUDENT15', 15, 50, '+90 days'],     // Öğrenci indirimi
        [null, 'WEEKEND20', 20, 25, '+7 days'],      // Hafta sonu özel
        [null, 'FLASH25', 25, 5, '+3 days']          // Flash indirim
    ];

    foreach ($coupons as $coupon) {
        $expire_date = date('Y-m-d H:i:s', strtotime($coupon[4]));
        
        $couponStmt->execute([
            ':firm_id' => $coupon[0],
            ':code' => $coupon[1],
            ':discount_percentage' => $coupon[2],
            ':usage_limit' => $coupon[3],
            ':expire_date' => $expire_date
        ]);
    }

    echo "10 kupon kodu başarıyla eklendi ✅\n";
    
    // Kupon özeti
    echo "\n🎫 KUPON KODLARI:\n";
    echo "================\n";
    $coupon_summary = $db->query("SELECT code, discount_percentage, usage_limit, expire_date FROM coupons ORDER BY discount_percentage DESC");
    
    while ($row = $coupon_summary->fetch()) {
        $expire_str = date('d.m.Y', strtotime($row['expire_date']));
        echo "• " . $row['code'] . ": %" . $row['discount_percentage'] . " indirim (Limit: " . $row['usage_limit'] . ", Son: " . $expire_str . ")\n";
    }

} catch (PDOException $e) {
    die("Veri ekleme hatası: " . $e->getMessage());
}
