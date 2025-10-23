<?php

function formatDateTurkish($date, $format = 'full') {
    $months = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    
    $days = [
        'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'
    ];
    
    $datetime = new DateTime($date);
    
    if ($format === 'full') {
        $day_name = $days[$datetime->format('l')];
        $day = $datetime->format('d');
        $month = $months[(int)$datetime->format('m')];
        $year = $datetime->format('Y');
        $time = $datetime->format('H:i');
        
        return "$day_name, $day $month $year $time";
    } elseif ($format === 'date') {
        $day = $datetime->format('d');
        $month = $months[(int)$datetime->format('m')];
        $year = $datetime->format('Y');
        
        return "$day $month $year";
    } elseif ($format === 'time') {
        return $datetime->format('H:i');
    }
    
    return $datetime->format('d.m.Y H:i');
}

/**
 * Fiyat formatı (Türk Lirası)
 */
function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

/**
 * Güvenli HTML çıktısı
 */
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Koltuk numarasını formatla (A1, B2, etc.)
 */
function formatSeatNumber($seat_number) {
    $row = chr(65 + floor(($seat_number - 1) / 4)); // A, B, C...
    $seat = (($seat_number - 1) % 4) + 1; // 1, 2, 3, 4
    return $row . $seat;
}
?>