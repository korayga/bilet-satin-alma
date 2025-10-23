<?php
session_start();

// Tüm session verilerini temizle
session_unset();
session_destroy();

// Çıkış mesajıyla login sayfasına yönlendir
header('Location: login.php?logout=1');
exit;
?>
