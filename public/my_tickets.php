<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/auth.php";

// Giriş kontrolü
if (!is_logged_in() || !is_user()) {
    header('Location: login.php?message=Lütfen giriş yapın');
    exit();
}

// AJAX İptal İsteği İşle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_ticket') {
    header('Content-Type: application/json');
    
    $ticket_code = $_POST['ticket_code'] ?? '';
    
    if (empty($ticket_code)) {
        echo json_encode(['success' => false, 'message' => 'Bilet kodu gerekli']);
        exit;
    }
    
    $result = cancel_ticket($ticket_code, $_SESSION['id']);
    echo json_encode($result);
    exit;
}

require_once "../includes/header.php";

// auth.php'deki fonksiyonu kullan
$user_tickets = get_user_tickets();
?>

<div class="container-fluid mt-4 px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-turuncu text-white">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt"></i> Biletlerim</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (empty($user_tickets)): ?>
                        <!-- Bilet yok mesajı -->
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5>Henüz biletiniz bulunmuyor</h5>
                            <p>Hemen bir sefer arayın ve biletinizi satın alın!</p>
                            <a href="index.php" class="btn btn-turuncu mt-2">
                                <i class="fas fa-search"></i> Sefer Ara
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Bilet listesi -->
                        <div class="row">
                            <?php foreach ($user_tickets as $ticket): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="card border-start border-4 <?php echo $ticket['status'] === 'active' ? 'border-success' : 'border-secondary'; ?> h-100">
                                        <div class="card-body p-3">
                                            <!-- Durum badge -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-bus text-turuncu"></i> 
                                                    <?php echo htmlspecialchars($ticket['firm_name']); ?>
                                                </h6>
                                                <span class="badge bg-<?php echo $ticket['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $ticket['status'] === 'active' ? 'Aktif' : 'İptal'; ?>
                                                </span>
                                            </div>

                                            <!-- Güzergah -->
                                            <div class="mb-2">
                                                <strong><?php echo htmlspecialchars($ticket['departure_city']); ?></strong>
                                                <i class="fas fa-arrow-right text-turuncu mx-1"></i>
                                                <strong><?php echo htmlspecialchars($ticket['arrival_city']); ?></strong>
                                            </div>

                                            <!-- Tarih ve Saat -->
                                            <div class="d-flex gap-3 mb-2 small text-muted">
                                                <span><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($ticket['departure_time'])); ?></span>
                                                <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($ticket['departure_time'])); ?> - <?php echo date('H:i', strtotime($ticket['arrival_time'])); ?></span>
                                            </div>

                                            <!-- Koltuk + Tip -->
                                            <div class="d-flex gap-2 mb-2 flex-wrap">
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($ticket['seat_numbers'] ?? '-'); ?></span>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($ticket['bus_type']); ?></span>
                                            </div>

                                            <!-- Fiyat -->
                                            <div class="mb-2">
                                                <strong class="text-success fs-5"><?php echo number_format($ticket['total_price'], 2); ?> TL</strong>
                                                <?php if ($ticket['discount'] > 0): ?>
                                                    <small class="text-muted ms-1">(<?php echo number_format($ticket['discount'], 2); ?> TL indirim)</small>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Bilet Kodu -->
                                            <div class="p-2 bg-light rounded mb-2">
                                                <small class="text-muted font-monospace"><?php echo htmlspecialchars($ticket['ticket_code']); ?></small>
                                            </div>

                                            <!-- Butonlar -->
                                            <div class="d-flex gap-1 mb-2">
                                                <a href="download_ticket.php?code=<?php echo urlencode($ticket['ticket_code']); ?>" 
                                                   class="btn btn-success btn-sm flex-fill" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="buy_ticket.php?trip_id=<?php echo $ticket['trips_id']; ?>"
                                                   class="btn btn-info btn-sm flex-fill text-white">
                                                    <i class="fas fa-redo"></i>
                                                </a>
                                                <?php if ($ticket['status'] === 'active'): ?>
                                                <button onclick="cancelTicket('<?php echo $ticket['ticket_code']; ?>')"
                                                   class="btn btn-danger btn-sm flex-fill">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Alış tarihi -->
                                            <small class="text-muted" style="font-size: 10px;">
                                                <i class="fas fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($ticket['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Özet bilgi -->
                        <div class="alert alert-light mt-4">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h3 class="text-turuncu mb-0"><?php echo count($user_tickets); ?></h3>
                                    <small class="text-muted">Toplam Bilet</small>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-success mb-0">
                                        <?php echo count(array_filter($user_tickets, fn($t) => $t['status'] === 'active')); ?>
                                    </h3>
                                    <small class="text-muted">Aktif Bilet</small>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-primary mb-0">
                                      <?php echo array_sum(array_column($user_tickets, 'ticket_count')); ?>
                                    </h3>
                                    <small class="text-muted">Toplam Koltuk</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ana sayfaya dön -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-turuncu">
                            <i class="fas fa-home"></i> Ana Sayfa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelTicket(ticketCode) {
    if (!confirm('Bu bileti iptal etmek istediğinizden emin misiniz?\n\n⚠️ Sefer saatine 1 saatten fazla kaldıysa iptal edebilirsiniz.\n✅ İptal edilen biletlerin tutarı bakiyenize iade edilecektir.')) {
        return;
    }
    
    // İptal butonunu devre dışı bırak
    const cancelBtn = event.target.closest('.btn-danger');
    const originalText = cancelBtn.innerHTML;
    cancelBtn.disabled = true;
    cancelBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İptal Ediliyor...';
    
    // FormData ile POST
    const formData = new FormData();
    formData.append('action', 'cancel_ticket');
    formData.append('ticket_code', ticketCode);
    
    // AJAX isteği
    fetch('my_tickets.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message + '\n\n💰 İade Tutarı: ' + data.refund_amount + ' ₺\n🪑 İptal Edilen Koltuk: ' + data.cancelled_seats + ' adet\n💳 Yeni Bakiye: ' + data.new_balance + ' ₺');
            
            // Sayfayı yenile
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('❌ Hata: ' + data.message);
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('❌ Bir hata oluştu: ' + error.message);
        cancelBtn.disabled = false;
        cancelBtn.innerHTML = originalText;
    });
}
</script>

<?php require_once "../includes/footer.php"; ?>