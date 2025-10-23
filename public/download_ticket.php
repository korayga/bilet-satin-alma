<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/auth.php";

// Giriş kontrolü - Sadece kullanıcılar erişebilir
if (!is_logged_in() || !is_user()) {
    header('Location: login.php?message=Lütfen giriş yapın');
    exit();
}

// Bilet kodu kontrolü
if (!isset($_GET['code']) || empty($_GET['code'])) {
    header('Location: my_tickets.php?message=Geçersiz bilet kodu');
    exit();
}

$ticket_code = $_GET['code'];

// Bileti getir (sadece sahibi görebilir)
$ticket = get_ticket_by_code($ticket_code);

if (!$ticket) {
    header('Location: my_tickets.php?message=Bilet bulunamadı veya erişim yetkiniz yok');
    exit();
}

// HTML olarak yazdır (CTRL+P ile PDF olarak kaydedilebilir)
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet - <?php echo htmlspecialchars($ticket['ticket_code']); ?></title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .ticket {
            background: white;
            border: 3px solid #FF6B35;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .ticket-header {
            text-align: center;
            border-bottom: 3px dashed #FF6B35;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .ticket-header h1 {
            color: #FF6B35;
            margin: 0 0 10px 0;
        }
        .ticket-code {
            background: #FF6B35;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .ticket-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .ticket-field {
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #FF6B35;
            border-radius: 5px;
        }
        .ticket-field label {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .ticket-field .value {
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }
        .route {
            grid-column: 1 / -1;
            text-align: center;
            font-size: 24px;
            color: #FF6B35;
            padding: 20px;
            background: #fff8f5;
            border-radius: 10px;
        }
        .ticket-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px dashed #ccc;
            color: #666;
            font-size: 12px;
        }
        .cancelled-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.15);
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
        }
        .ticket {
            position: relative;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #FF6B35;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
            background: #e55a2b;
        }
        .seats {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .seat-badge {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Yazdır / PDF Kaydet
    </button>

    <div class="ticket">
        <?php if ($ticket['status'] === 'cancelled'): ?>
            <div class="cancelled-watermark">İPTAL EDİLDİ</div>
        <?php endif; ?>
        <div class="ticket-header">
            <h1>🚌 OTOBÜS BİLETİ</h1>
            <div class="ticket-code"><?php echo htmlspecialchars($ticket['ticket_code']); ?></div>
            <?php if ($ticket['status'] === 'cancelled'): ?>
                <div style="background: #ff4444; color: white; padding: 15px; margin-top: 15px; border-radius: 5px; font-weight: bold;">
                    ⚠️ BU BİLET İPTAL EDİLMİŞTİR - GEÇERSİZ ⚠️
                </div>
            <?php else: ?>
                <p style="margin-top: 10px; color: #4CAF50; font-weight: bold; font-size: 16px;">
                    ✅ BİLET GEÇERLİDİR
                </p>
            <?php endif; ?>
        </div>

        <div class="ticket-body">
            <div class="route">
                <strong><?php echo htmlspecialchars($ticket['departure_city']); ?></strong>
                ➜
                <strong><?php echo htmlspecialchars($ticket['arrival_city']); ?></strong>
            </div>

            <div class="ticket-field">
                <label>🏢 Firma</label>
                <div class="value"><?php echo htmlspecialchars($ticket['firm_name']); ?></div>
            </div>

            <div class="ticket-field">
                <label>👤 Yolcu</label>
                <div class="value"><?php echo htmlspecialchars($ticket['passenger_name']); ?></div>
            </div>

            <div class="ticket-field">
                <label>📅 Tarih</label>
                <div class="value"><?php echo date('d.m.Y', strtotime($ticket['departure_time'])); ?></div>
            </div>

            <div class="ticket-field">
                <label>🕐 Kalkış Saati</label>
                <div class="value"><?php echo date('H:i', strtotime($ticket['departure_time'])); ?></div>
            </div>

            <div class="ticket-field">
                <label>🕑 Varış Saati</label>
                <div class="value"><?php echo date('H:i', strtotime($ticket['arrival_time'])); ?></div>
            </div>

            <div class="ticket-field">
                <label>🚌 Otobüs Tipi</label>
                <div class="value"><?php echo htmlspecialchars($ticket['bus_type']); ?></div>
            </div>

            <div class="ticket-field" style="grid-column: 1 / -1;">
                <label>💺 Koltuk Numaraları</label>
                <div class="seats">
                    <?php 
                    $seats = explode(', ', $ticket['seat_numbers']);
                    foreach ($seats as $seat): 
                    ?>
                        <span class="seat-badge"><?php echo htmlspecialchars($seat); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ticket-field">
                <label>💰 Ödenen Tutar</label>
                <div class="value" style="color: #4CAF50; font-size: 20px;">
                    <?php echo number_format($ticket['total_price'], 2); ?> TL
                </div>
            </div>

            <div class="ticket-field">
                <label>🎫 Koltuk Sayısı</label>
                <div class="value"><?php echo $ticket['ticket_count']; ?> Koltuk</div>
            </div>

            <?php if ($ticket['discount'] > 0): ?>
            <div class="ticket-field" style="grid-column: 1 / -1; background: #fff3cd; border-left-color: #ffc107;">
                <label>🎉 İndirim</label>
                <div class="value" style="color: #ff9800;">
                    <?php echo number_format($ticket['discount'], 2); ?> TL indirim uygulandı
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="ticket-footer">
            <p><strong>Satın Alma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($ticket['created_at'])); ?></p>
            <p style="margin-top: 10px;">
                Bu bilet <?php echo htmlspecialchars($ticket['passenger_email']); ?> e-posta adresine kayıtlıdır.
            </p>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">
                TrendWay Online Bilet Sistemi - www.trendway.com<br>
                Bu bileti yolculuk esnasında yanınızda bulundurunuz.
            </p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <a href="my_tickets.php" style="text-decoration: none; color: #666;">
            ← Biletlerime Dön
        </a>
    </div>
</body>
</html>
