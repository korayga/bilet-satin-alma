<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB(); // Doğru veritabanı bağlantısını al
    echo "Veritabanı kurulumu başlatıldı...\n";



    // === 1. FİRMLAR TABLOSU ===
    $db->exec("
        CREATE TABLE IF NOT EXISTS firms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            description TEXT,
            phone TEXT,
            email TEXT,
            address TEXT,
            logo TEXT,
            status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // === 2. KULLANICILAR TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            role TEXT DEFAULT 'user',
            balance REAL DEFAULT 800.00,
            firm_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (firm_id) REFERENCES firms(id)
        )
    ");

    // === 3. SEFERLER TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS trips (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firm_id INTEGER NOT NULL,
            departure_city TEXT NOT NULL,
            arrival_city TEXT NOT NULL,
            departure_time DATETIME NOT NULL,
            arrival_time DATETIME NOT NULL,
            price REAL NOT NULL,
            total_seats INTEGER DEFAULT 40,
            available_seats INTEGER DEFAULT 40,
            bus_type TEXT DEFAULT '2+2' CHECK(bus_type IN ('2+1', '2+2')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (firm_id) REFERENCES firms(id)
        )
    ");

    // === 4. KUPONLAR TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firm_id INTEGER DEFAULT NULL,
            code TEXT UNIQUE NOT NULL,
            discount_percentage REAL NOT NULL,
            usage_limit INTEGER DEFAULT 1,
            expire_date DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
        )
    ");

    // === 5. BİLETLER TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            trips_id INTEGER NOT NULL,
            ticket_code VARCHAR(20) UNIQUE NOT NULL,
            total_price REAL NOT NULL,
            ticket_count INTEGER DEFAULT 1,
            discount REAL DEFAULT 0,
            coupon_code VARCHAR(50),
            status TEXT CHECK(status IN ('active', 'cancelled', 'used')) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (trips_id) REFERENCES trips(id)
        )
    ");

    // === 6. KOLTUKLAR TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS booked_seats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tickets_id INTEGER NOT NULL,
            trips_id INTEGER NOT NULL,
            seat_number VARCHAR(10) NOT NULL,
            booked_seat TEXT CHECK(booked_seat IN ('booked','not_booked')) DEFAULT 'not_booked',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tickets_id) REFERENCES tickets(id),
            FOREIGN KEY (trips_id) REFERENCES trips(id)
        )
    ");

    // === 7. USER-COUPON TABLOSU ===
   $db->exec("
        CREATE TABLE IF NOT EXISTS user_coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            coupon_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            usage_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (coupon_id) REFERENCES coupons(id),
            UNIQUE (coupon_id, user_id)
        )
    ");

    // === ÖRNEK VERİLER ===
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $firm_admin_password = password_hash('firm123', PASSWORD_DEFAULT);

    // Firma
   $db->exec("
        INSERT OR IGNORE INTO firms (id, name, description, phone, email, address, status)
        VALUES (1, 'Metro Turizm', 'Kaliteli ve güvenli yolculuk', '0212 444 34 56', 'info@metro.com', 'İstanbul', 'active')
    ");

    // user kullanıcı
    $stmt =$db->prepare("
        INSERT OR IGNORE INTO users ( username,full_name, email, password, role, balance)
        VALUES ( ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([ 'a', 'a', 'a@a.com', 1234, 'user', 1000.00]);

    // Firma admin (firm_id parametresiyle)
    $stmt =$db->prepare("
        INSERT OR IGNORE INTO users (id, username,full_name, email, password, role, balance, firm_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([2, 'Metro Yetkilisi', 'metroadmin', 'admin@metro.com', $firm_admin_password, 'firm_admin', 500.00, 1]);

    // Örnek sefer
   $db->exec("
        INSERT OR IGNORE INTO trips (firm_id, departure_city, arrival_city, departure_time, arrival_time, price)
        VALUES (1, 'İstanbul', 'Ankara', '2025-10-09 09:00:00', '2025-10-09 13:30:00', 150.00)
    ");

    // Kupon (Global - firm_id NULL)
   $db->exec("
        INSERT OR IGNORE INTO coupons (firm_id, code, discount_percentage, usage_limit, expire_date)
        VALUES (NULL, 'HOSGELDIN10', 10.00, 100, '2025-12-31 23:59:59')
    ");
    
    $stmt =$db->prepare("
        INSERT OR IGNORE INTO users (id, username,full_name, email, password, role, balance)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([1, 'Sistem Yöneticisi', 'adminuser', 'admin@trendway.com', $admin_password, 'admin', 1000.00]);


    echo "<div style='background:#0f0;color:#000;padding:15px;font-weight:bold;text-align:center;border-radius:8px;'>
        ✅ Veritabanı başarıyla kuruldu! <br>
        Tablolar oluşturuldu ve örnek veriler eklendi.
    </div>";

} catch (PDOException $e) {
    echo "<div style='background:#f33;color:#fff;padding:15px;font-family:monospace;'>
        ❌ Hata: " . htmlspecialchars($e->getMessage()) . "
    </div>";
}
?>