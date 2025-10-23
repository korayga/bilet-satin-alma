<?php
// Config dosyası - config.php

define('DB_PATH', __DIR__ . '/../database/database.sqlite');
define('SITE_NAME', 'Bilet Satın Alma Sistemi');
define('SITE_URL', 'http://localhost:8080');
define('SESSION_LIFETIME', 3600);

// Veritabanı bağlantı fonksiyonu
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }
    
    return $db;
}

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Session
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}
?>