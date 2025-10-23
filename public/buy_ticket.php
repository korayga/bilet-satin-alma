<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/auth.php";
require_once "../includes/header.php";

// Giriş kontrolü
if (!is_logged_in() || !is_user()) {
    header('Location: login.php?message=Lütfen giriş yapın');
    exit();
}

// Trip ID kontrolü
$trip_id = $_GET['trip_id'] ?? $_POST['trip_id'] ?? null;

if (!$trip_id || !is_numeric($trip_id)) {
    header('Location: index.php?message=Geçersiz sefer ID');
    exit();
}

?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Bilet Satın Al</h4>
                </div>
                <div class="card-body">
                    <?php
                    $trip=(get_trip($trip_id));
                    if (!$trip) {
                        echo "<div class='alert alert-danger'>";
                        echo "<i class='fas fa-exclamation-triangle'></i> Sefer bulunamadı!";
                        echo "</div>";
                        echo "<a href='index.php' class='btn btn-primary'>Ana Sayfa</a>";
                        exit;
                    }
                    
                    // GEÇMİŞ TARİHLİ SEFER KONTROLÜ
                    if (strtotime($trip['departure_time']) <= time()) {
                        echo "<div class='alert alert-danger'>";
                        echo "<i class='fas fa-exclamation-triangle'></i> Bu sefer geçmiş tarihli olduğu için satışa kapalıdır!";
                        echo "</div>";
                        echo "<a href='index.php' class='btn btn-primary'>Ana Sayfa</a>";
                        exit;
                    }
                    
                    // Ödeme işlemi başlatıldı mı kontrol et
                    $payment_confirm = $_POST['payment_confirm'] ?? false;
                    
                    if ($payment_confirm) {
                        // Kupon kodunu ve seçilen koltukları al
                        $coupon_code = $_POST['coupon_code'] ?? null;
                        $selected_seats_json = $_POST['selected_seats'] ?? '[1]';
                        $selected_seats = json_decode($selected_seats_json, true) ?: [1];
                        
                        // Ödeme işlemi yap (koltuk seçimi ile)
                        $result = set_balance($trip_id, $selected_seats, $coupon_code);

                        if ($result['success']) {
                            // Başarılı ödeme
                            echo "<div class='alert alert-success'>";
                            echo "<i class='fas fa-check-circle'></i> ";
                            echo $result['message'] . "<br>";
                            echo "Yeni bakiye: " . $result['new_balance'] . " TL";
                            
                            // İndirim bilgisi 
                            if ($result['discount'] > 0) {
                                echo "<br><small class='text-success'>";
                                echo "Orijinal fiyat: " . $result['original_price'] . " TL | ";
                                echo "İndirim: -" . $result['discount'] . " TL | ";
                                echo "Ödenen: " . $result['final_price'] . " TL";
                                echo "</small>";
                            }
                            echo "</div>";
                            
                            echo "<div class='alert alert-info'>";
                            echo "<i class='fas fa-ticket-alt'></i> ";
                            echo "Biletiniz oluşturuluyor...";
                            echo "</div>";
                            
                            //Bilet oluşturma 
                            $ticket_result = create_ticket($trip_id, $result, $selected_seats);
                            
                            if ($ticket_result['success']) {
                                echo "<div class='alert alert-success'>";
                                echo "<i class='fas fa-check-circle'></i> ";
                                echo $ticket_result['message'] . "<br>";
                                echo "<strong>Bilet Kodu: " . $ticket_result['ticket_code'] . "</strong><br>";
                                echo "<strong>Seçilen Koltuklar: " . implode(', ', $ticket_result['selected_seats']) . "</strong>";
                                echo "</div>";
                            
                                
                                echo "<div class='mt-3'>";
                                echo "<a href='my_tickets.php' class='btn btn-turuncu me-2'>Biletlerim</a>";
                                echo "<a href='download_ticket.php?code=" . $ticket_result['ticket_code'] . "' class='btn btn-success me-2'>PDF İndir</a>";
    
                                echo "<a href='index.php' class='btn btn-outline-secondary'>Ana Sayfa</a>";
                                echo "</div>";
                                
                                // Satın alınan koltukları JavaScript'e aktar
                                $purchased_seat_numbers = json_encode($selected_seats);
                                
                                // 3 saniye sonra otomatik yönlendirme + koltukları hemen güncelle
                                echo "<script>
                                    // Koltuk haritası yüklenene kadar bekle
                                    setTimeout(function() {
                                        console.log('Koltuklar güncelleniyor...', $purchased_seat_numbers);
                                        
                                        // Satın alınan koltukları hemen görsel olarak güncelle
                                        const purchasedSeats = $purchased_seat_numbers;
                                        
                                        if (!purchasedSeats || purchasedSeats.length === 0) {
                                            console.error('Koltuk listesi boş!');
                                            return;
                                        }
                                        
                                        purchasedSeats.forEach(seatNum => {
                                            console.log('Koltuk güncelleniyor:', seatNum);
                                            const seatElement = document.querySelector('.seat[data-seat=\"' + seatNum + '\"]');
                                            
                                            if (seatElement) {
                                                console.log('Koltuk bulundu:', seatElement);
                                                seatElement.classList.remove('available', 'selected');
                                                seatElement.classList.add('booked');
                                                seatElement.textContent = '✕';
                                                seatElement.style.cursor = 'not-allowed';
                                                seatElement.style.pointerEvents = 'none';
                                                seatElement.style.background = '#e0e0e0';
                                                seatElement.style.border = '2px solid #ccc';
                                                seatElement.style.color = '#999';
                                            } else {
                                                console.error('Koltuk bulunamadı! Selector:', '.seat[data-seat=\"' + seatNum + '\"]');
                                            }
                                        });
                                    }, 100); // 100ms bekle - DOM yüklensin
                                    
                                    // 3 saniye sonra sayfa yenilenir (veritabanından güncel veri gelir)
                                    setTimeout(function() {
                                        window.location.href = 'buy_ticket.php?trip_id=$trip_id';
                                    }, 3000);
                                </script>";
                            } else {
                                echo "<div class='alert alert-warning'>";
                                echo "<i class='fas fa-exclamation-triangle'></i> ";
                                echo "Ödeme başarılı ancak bilet oluşturulamadı: " . $ticket_result['message'];
                                echo "</div>";
                                
                                echo "<div class='mt-3'>";
                                echo "<a href='my_tickets.php' class='btn btn-turuncu me-2'>Biletlerim</a>";
                                echo "<a href='index.php' class='btn btn-outline-secondary'>Ana Sayfa</a>";
                                echo "</div>";
                            }
                            
                        } else {
                            // Hata durumu
                            echo "<div class='alert alert-danger'>";
                            echo "<i class='fas fa-exclamation-triangle'></i> ";
                            echo "Hata: " . $result['message'];
                            echo "</div>";
                            
                            echo "<div class='mt-3'>";
                            echo "<a href='javascript:history.back()' class='btn btn-secondary me-2'>Geri Dön</a>";
                            echo "<a href='index.php' class='btn btn-outline-primary'>Ana Sayfa</a>";
                            echo "</div>";
                        }
                    } else {
                        // Bilet detayı göster ve ödeme formu
                        ?>
                        
                        <!-- Sefer Detayları -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-bus text-orange"></i> Sefer Bilgileri</h5>
                                <table class="table table-borderless">
                                    <tr><td><strong>Firma:</strong></td><td><?php echo htmlspecialchars($trip['firm_name']); ?></td></tr>
                                    <tr><td><strong>Güzergah:</strong></td><td><?php echo htmlspecialchars($trip['departure_city']); ?> → <?php echo htmlspecialchars($trip['arrival_city']); ?></td></tr>
                                    <tr><td><strong>Tarih:</strong></td><td><?php echo date('d.m.Y', strtotime($trip['departure_time'])); ?></td></tr>
                                    <tr><td><strong>Saat:</strong></td><td><?php echo date('H:i', strtotime($trip['departure_time'])); ?> - <?php echo date('H:i', strtotime($trip['arrival_time'])); ?></td></tr>
                                    <tr><td><strong>Koltuk:</strong></td><td><?php 
                                        // Dinamik müsait koltuk sayısı hesapla
                                        $seat_availability = get_seat_availability($trip_id);
                                        $available_count = isset($seat_availability['available_count']) ? $seat_availability['available_count'] : $trip['total_seats'];
                                        echo $available_count . ' adet mevcut'; 
                                    ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-credit-card text-orange"></i> Ödeme Bilgileri</h5>
                                <div class="card bg-light" id="payment-info">
                                    <div class="card-body">
                                        <p class="mb-2">Mevcut Bakiye: <strong><?php echo get_balance(); ?> TL</strong></p>
                                        <p class="mb-2">Bilet Fiyatı: <strong class="text-danger" id="original-price"><?php echo $trip['price']; ?> TL</strong></p>
                                        <div id="discount-info" style="display: none;">
                                            <p class="mb-2 text-success">Kupon İndirimi: <strong id="discount-amount">-0 TL</strong></p>
                                            <p class="mb-2">Son Fiyat: <strong class="text-success" id="final-price"><?php echo $trip['price']; ?> TL</strong></p>
                                        </div>
                                        <hr>
                                        <p class="mb-0">Kalan Bakiye: <strong id="remaining-balance"><?php echo (get_balance() - $trip['price']); ?> TL</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Koltuk Seçimi -->
                        <style>
                        /* Koltuk Seçimi Inline Styles */
                        #seat-map .seats-container {
                            background: white;
                            border-radius: 12px;
                            padding: 20px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            width: 100%;
                        }
                        
                        #seat-map .bus-header {
                            text-align: center;
                            font-weight: bold;
                            font-size: 18px;
                            color: #E53935;
                            padding: 15px;
                            background: linear-gradient(to right, #fff5f5, #ffffff);
                            border-radius: 8px 8px 0 0;
                            margin: -20px -20px 20px -20px;
                        }
                        
                        #seat-map .seat-legend {
                            display: flex;
                            gap: 30px;
                            justify-content: center;
                            margin-bottom: 20px;
                            padding: 15px;
                            background: #f8f9fa;
                            border-radius: 8px;
                        }
                        
                        #seat-map .legend-item {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        
                        #seat-map .legend-seat {
                            width: 32px;
                            height: 32px;
                            pointer-events: none;
                        }
                        
                        #seat-map .driver-section {
                            display: flex;
                            justify-content: flex-end;
                            margin-bottom: 20px;
                            padding-right: 20px;
                        }
                        
                        #seat-map .driver-indicator {
                            background: #e0e0e0;
                            padding: 8px 16px;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            color: #555;
                        }
                        
                        #seat-map .seat-grid {
                            display: flex;
                            flex-direction: column;
                            gap: 12px;
                            width: 100%;
                        }
                        
                        #seat-map .bus-row {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 40px;
                            padding: 0 20px;
                            flex-wrap: nowrap;
                        }
                        
                        #seat-map .seat-group-left,
                        #seat-map .seat-group-right {
                            display: flex;
                            gap: 8px;
                            flex-wrap: nowrap;
                        }
                        
                        #seat-map .row-number {
                            font-size: 12px;
                            color: #999;
                            font-family: monospace;
                            min-width: 20px;
                            text-align: center;
                        }
                        
                        #seat-map .seat {
                            width: 48px;
                            height: 48px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 6px;
                            font-size: 13px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            user-select: none;
                        }
                        
                        #seat-map .seat.available {
                            background: white;
                            border: 2px solid #ddd;
                            color: #333;
                        }
                        
                        #seat-map .seat.available:hover {
                            border-color: #E53935;
                            transform: scale(1.05);
                            box-shadow: 0 2px 8px rgba(229, 57, 53, 0.3);
                        }
                        
                        #seat-map .seat.selected {
                            background: #00897B;
                            border: 2px solid #00796B;
                            color: white;
                            box-shadow: 0 2px 8px rgba(0, 137, 123, 0.4);
                        }
                        
                        #seat-map .seat.booked {
                            background: #e0e0e0;
                            border: 2px solid #ccc;
                            color: #999;
                            cursor: not-allowed;
                        }
                        
                        .selected-info {
                            border-left: 4px solid #E53935;
                        }
                        
                        .ticket-price {
                            color: #E53935;
                            font-size: 20px;
                        }
                        </style>
                        
                        <div style="width: 100%; margin-bottom: 30px;">
                            <h5>🪑 Koltuk Seçimi</h5>
                            <div class="seat-map" id="seat-map">
                                <?php
                                $seat_info = get_seat_availability($trip_id);
                                if (isset($seat_info['error'])) {
                                    echo "<div class='alert alert-warning'>" . $seat_info['error'] . "</div>";
                                } else {
                                    echo "<div class='seats-container'>";
                                    echo "<div class='bus-header'><i class='fas fa-bus'></i> OTOBÜS KOLTUK HARİTASI</div>";
                                    
                                    // Legend (Açıklama)
                                    echo "<div class='seat-legend'>";
                                    echo "<div class='legend-item'><div class='seat available legend-seat'></div><span>Müsait</span></div>";
                                    echo "<div class='legend-item'><div class='seat selected legend-seat'></div><span>Seçili</span></div>";
                                    echo "<div class='legend-item'><div class='seat booked legend-seat'></div><span>Dolu</span></div>";
                                    echo "</div>";
                                    
                                    // Şoför bölgesi
                                    echo "<div class='driver-section'>";
                                    echo "<div class='driver-indicator'>🚗 Şoför</div>";
                                    echo "</div>";
                                    
                                    $seats = $seat_info['seats'];
                                    $bus_type = $seat_info['bus_type'];
                                    
                                    // Koltukları satırlara göre grupla
                                    $grouped_seats = [];
                                    foreach ($seats as $seat) {
                                        $grouped_seats[$seat['row']][] = $seat;
                                    }
                                    
                                    echo "<div class='seat-grid'>";
                                    foreach ($grouped_seats as $row => $row_seats) {
                                        echo "<div class='bus-row'>";
                                        
                                        // Sol grup koltuklar (left-window, left-aisle)
                                        $left_seats = array_filter($row_seats, function($seat) {
                                            return strpos($seat['position'], 'left') !== false;
                                        });
                                        
                                        echo "<div class='seat-group-left'>";
                                        foreach ($left_seats as $seat) {
                                            $class = 'seat ' . $seat['status'];
                                            $display = $seat['status'] === 'booked' ? '✕' : $seat['display'];
                                            echo "<div class='$class' data-seat='{$seat['number']}' data-display='{$seat['display']}'>$display</div>";
                                        }
                                        echo "</div>";
                                        
                                        // Sıra numarası (ortada)
                                        echo "<div class='row-number'>$row</div>";
                                        
                                        // Sağ grup koltuklar (right-aisle, right-window, right-single)
                                        $right_seats = array_filter($row_seats, function($seat) {
                                            return strpos($seat['position'], 'right') !== false;
                                        });
                                        
                                        echo "<div class='seat-group-right'>";
                                        foreach ($right_seats as $seat) {
                                            $class = 'seat ' . $seat['status'];
                                            $display = $seat['status'] === 'booked' ? '✕' : $seat['display'];
                                            echo "<div class='$class' data-seat='{$seat['number']}' data-display='{$seat['display']}'>$display</div>";
                                        }
                                        echo "</div>";
                                        
                                        echo "</div>";
                                    }
                                    echo "</div>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                            
                            <!-- Seçilen Koltuklar Bilgisi -->
                            <div class="selected-info mt-3 p-3 bg-light rounded">
                                <h6><i class="fas fa-couch"></i> Seçilen Koltuklar</h6>
                                <p class="mb-1">Koltuk Numaraları: <span id="selected-seats-display" class="fw-bold text-primary">Koltuk seçin</span></p>
                                <p class="mb-0">Toplam Fiyat: <span id="total-price-display" class="ticket-price">0</span> TL</p>
                            </div>
                        </div>

                        <!-- Kupon Kodu -->
                        <form method="POST" id="payment-form">
                            <!-- Hidden inputs for selected seats -->
                            <input type="hidden" name="selected_seats" id="selected_seats_input" value="">
                            <input type="hidden" name="total_price" id="total_price_input" value="0">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="coupon_code" class="form-label">Kupon Kodu (Opsiyonel)</label>
                                    <input type="text" class="form-control" id="coupon_code" name="coupon_code" placeholder="Kupon kodunuz varsa buraya yazın">
                                    <div id="coupon-message" class="mt-2"></div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="apply-coupon-btn">Kupon Kontrol Et</button>
                                </div>
                            </div>

                            <!-- Onay Butonu -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="javascript:history.back()" class="btn btn-secondary me-md-2">Geri Dön</a>
                                <input type="hidden" name="payment_confirm" value="1">
                                <button type="submit" class="btn btn-turuncu btn-lg" id="payment-btn">
                                    <i class="fas fa-credit-card"></i> Ödemeyi Tamamla (<span id="payment-amount"><?php echo $trip['price']; ?></span> TL)
                                </button>
                            </div>
                        </form>

                        <script>
                        // Koltuk seçimi değişkenleri
                        let selectedSeats = [];
                        const seatPrice = <?php echo $trip['price']; ?>;
                        const maxSeats = 4;

                        // Sayfa yüklendiğinde
                        document.addEventListener('DOMContentLoaded', function() {
                            // Koltuk tıklama eventi
                            document.querySelectorAll('.seat.available').forEach(seat => {
                                seat.addEventListener('click', function() {
                                    const seatNumber = parseInt(this.dataset.seat);
                                    const seatDisplay = this.dataset.display;
                                    
                                    if (this.classList.contains('selected')) {
                                        // Seçimi kaldır
                                        selectedSeats = selectedSeats.filter(s => s.number !== seatNumber);
                                        this.classList.remove('selected');
                                    } else {
                                        // Koltuk seç
                                        if (selectedSeats.length >= maxSeats) {
                                            alert(`En fazla ${maxSeats} koltuk seçebilirsiniz!`);
                                            return;
                                        }
                                        selectedSeats.push({ number: seatNumber, display: seatDisplay });
                                        this.classList.add('selected');
                                    }
                                    
                                    updateSeatDisplay();
                                });
                            });

                            // Form submit kontrolü
                            const paymentForm = document.getElementById('payment-form');
                            if (paymentForm) {
                                paymentForm.addEventListener('submit', function(e) {
                                    if (selectedSeats.length === 0) {
                                        e.preventDefault();
                                        alert('Lütfen en az bir koltuk seçin!');
                                        return false;
                                    }
                                });
                            }

                            // Kupon kontrol sistemi
                            const applyCouponBtn = document.getElementById('apply-coupon-btn');
                            if (applyCouponBtn) {
                                applyCouponBtn.addEventListener('click', function() {
                                    const couponCode = document.getElementById('coupon_code').value.trim();
                                    
                                    if (!couponCode) {
                                        resetPricing();
                                        document.getElementById('coupon-message').innerHTML = '';
                                        return;
                                    }
                                    
                                    // Form'u submit et ve sayfayı yenile
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.innerHTML = `
                                        <input type="hidden" name="check_coupon" value="1">
                                        <input type="hidden" name="coupon_code" value="${couponCode}">
                                        <input type="hidden" name="trip_id" value="<?php echo $trip_id; ?>">
                                        <input type="hidden" name="selected_seats" value="${JSON.stringify(selectedSeats.map(s => s.number))}">
                                    `;
                                    document.body.appendChild(form);
                                    form.submit();
                                });
                            }
                            
                            // Kupon temizlendiğinde fiyatları sıfırla
                            const couponInput = document.getElementById('coupon_code');
                            if (couponInput) {
                                couponInput.addEventListener('input', function() {
                                    if (this.value.trim() === '') {
                                        resetPricing();
                                        document.getElementById('coupon-message').innerHTML = '';
                                    }
                                });
                            }
                        });

                        function updateSeatDisplay() {
                            // Koltukları numaraya göre sırala
                            selectedSeats.sort((a, b) => a.number - b.number);
                            
                            // Ekranda göster
                            const displayText = selectedSeats.length > 0 
                                ? selectedSeats.map(s => s.display).join(', ')
                                : 'Koltuk seçin';
                            document.getElementById('selected-seats-display').textContent = displayText;
                            
                            // Fiyat hesapla
                            const totalPrice = selectedSeats.length * seatPrice;
                            document.getElementById('total-price-display').textContent = totalPrice;
                            document.getElementById('payment-amount').textContent = totalPrice;
                            
                            // Bakiye hesapla
                            const currentBalance = <?php echo get_balance(); ?>;
                            document.getElementById('remaining-balance').textContent = (currentBalance - totalPrice) + ' TL';
                            
                            // Hidden input'u güncelle (sadece numaraları)
                            const seatNumbers = selectedSeats.map(s => s.number);
                            document.getElementById('selected_seats_input').value = JSON.stringify(seatNumbers);
                        }
                        
                        function resetPricing() {
                            const totalPrice = selectedSeats.length * seatPrice;
                            const currentBalance = <?php echo get_balance(); ?>;
                            
                            const discountInfo = document.getElementById('discount-info');
                            if (discountInfo) {
                                discountInfo.style.display = 'none';
                            }
                            document.getElementById('remaining-balance').textContent = (currentBalance - totalPrice) + ' TL';
                            document.getElementById('payment-amount').textContent = totalPrice;
                        }
                        </script>

                        <?php
                        // Kupon kontrol işlemi
                        if (isset($_POST['check_coupon'])) {
                            $coupon_code = $_POST['coupon_code'] ?? '';
                            if (!empty($coupon_code)) {
                               
                                // Kupon kontrolü - FİRM_ID ile birlikte
                                $coupon_result = check_coupon_validity($coupon_code, $trip['price'], $_SESSION['id'], $trip['firm_id']);
                                
                                if ($coupon_result['valid']) {
                                    echo "<script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.getElementById('coupon_code').value = '$coupon_code';
                                            document.getElementById('coupon-message').innerHTML = '<div class=\"alert alert-success py-2\"><i class=\"fas fa-check\"></i> {$coupon_result['message']}</div>';
                                            document.getElementById('discount-info').style.display = 'block';
                                            document.getElementById('discount-amount').textContent = '-{$coupon_result['discount']} TL';
                                            document.getElementById('final-price').textContent = '{$coupon_result['final_price']} TL';
                                            document.getElementById('remaining-balance').textContent = '" . (get_balance() - $coupon_result['final_price']) . " TL';
                                            document.getElementById('payment-amount').textContent = '{$coupon_result['final_price']}';
                                        });
                                    </script>";
                                } else {
                                    echo "<script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.getElementById('coupon_code').value = '$coupon_code';
                                            document.getElementById('coupon-message').innerHTML = '<div class=\"alert alert-danger py-2\"><i class=\"fas fa-times\"></i> {$coupon_result['message']}</div>';
                                        });
                                    </script>";
                                }
                            }
                        }
                        ?>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>