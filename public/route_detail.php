<?php 
require_once '../config/config.php';
require_once '../config/auth.php';

// Arama parametrelerini al
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$date=($_GET['date'] ?? '');

$trips = [];
$search_performed = false;

if (!empty($from) && !empty($to)) {
    $search_performed = true;
    $trips = search_trips($from, $to, $date);
}

include '../includes/header.php'; 
?>

<!-- Arama Sonuçları Sayfası -->
<div class="container my-4">
    
    <!-- Sayfa Başlığı -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-brand">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Seferler</li>
                    </ol>
                </nav>
                
                <?php if (is_logged_in() && is_user()): ?>
                    <div class="user-balance">
                        <div class="alert alert-info mb-0 py-2 px-3" style="border-left: 4px solid var(--brand);">
                            <i class="bi bi-wallet2 me-2"></i>
                            <strong>Bakiyeniz: ₺<?= number_format(get_balance() ?? 0, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($search_performed): ?>
                <h2 class="text-brand">
                    <i class="bi bi-search me-2"></i>
                    "<?= htmlspecialchars($from) ?>" → "<?= htmlspecialchars($to) ?>" Seferleri
                </h2>
                <p class="text-muted">
                    <?php if ($date): ?>
                        <?= date('d M Y', strtotime($date)) ?> tarihinde 
                    <?php endif ?>
                    <?= count($trips) ?> sefer bulundu
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Arama Formu (Tekrar) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Kalkış Noktası
                            </label>
                            <select name="from" class="form-select" required>
                                <option value="">Kalkış şehri seçin</option>
                                <?php 
                                $iller = [
                                    'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Amasya', 'Ankara', 'Antalya', 'Artvin',
                                    'Aydın', 'Balıkesir', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa',
                                    'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Edirne', 'Elazığ', 'Erzincan',
                                    'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkâri', 'Hatay', 'Isparta',
                                    'Mersin', 'İstanbul', 'İzmir', 'Kars', 'Kastamonu', 'Kayseri', 'Kırklareli', 'Kırşehir',
                                    'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Kahramanmaraş', 'Mardin', 'Muğla',
                                    'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Rize', 'Sakarya', 'Samsun', 'Siirt',
                                    'Sinop', 'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Şanlıurfa', 'Uşak',
                                    'Van', 'Yozgat', 'Zonguldak', 'Aksaray', 'Bayburt', 'Karaman', 'Kırıkkale', 'Batman',
                                    'Şırnak', 'Bartın', 'Ardahan', 'Iğdır', 'Yalova', 'Karabük', 'Kilis', 'Osmaniye', 'Düzce'
                                ];
                                foreach($iller as $il): ?>
                                    <option value="<?= $il ?>" <?= $from == $il ? 'selected' : '' ?>>
                                        <?= $il ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-fill me-1"></i>Varış Noktası
                            </label>
                            <select name="to" class="form-select" required>
                                <option value="">Varış şehri seçin</option>
                                <?php foreach($iller as $il): ?>
                                    <option value="<?= $il ?>" <?= $to == $il ? 'selected' : '' ?>>
                                        <?= $il ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1"></i>Tarih
                            </label>
                            <input type="date" name="date" class="form-control" 
                                   value="<?= $date ?: date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-turuncu w-100 fw-semibold">
                                <i class="bi bi-search"></i> Ara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sonuçlar -->
    <div class="row">
        <?php if ($search_performed): ?>
            <?php if (!empty($trips)): ?>
                <!-- Sefer Listesi -->
                <?php foreach ($trips as $trip): ?>
                    <div class="col-12 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Sefer Bilgileri -->
                                    <div class="col-md-6">
                                        <h5 class="mb-1">
                                            <i class="bi bi-geo-alt-fill text-brand me-1"></i>
                                            <?= htmlspecialchars($trip['departure_city']) ?> 
                                            <i class="bi bi-arrow-right mx-2"></i>
                                            <?= htmlspecialchars($trip['arrival_city']) ?>
                                        </h5>
                                        <p class="text-muted mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($trip['firm_name']) ?>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-calendar me-1"></i>
                                            Kalkış: <?= date('d M Y - H:i', strtotime($trip['departure_time'])) ?>
                                            <span class="ms-3">
                                                <i class="bi bi-clock me-1"></i>
                                                Varış: <?= date('H:i', strtotime($trip['arrival_time'])) ?>
                                            </span>
                                        </p>
                                    </div>
                                    
                                    <!-- Fiyat ve Koltuk -->
                                    <div class="col-md-3 text-center">
                                        <h4 class="text-brand mb-1">₺<?= number_format($trip['price'], 0, ',', '.') ?></h4>
                                        <p class="text-success small mb-0">
                                            <i class="bi bi-check-circle me-1"></i>
                                            <?= $trip['available_seats'] ?> koltuk mevcut
                                        </p>
                                    </div>
                                    
                                    <!-- Bilet Al Butonu -->
                                    <div class="col-md-3 text-end">
                                        <?php if (is_logged_in()): ?>
                                            <button class="btn btn-payment btn-lg px-4" onclick="buyTicket(<?= $trip['id'] ?>)">
                                                <i class="bi bi-ticket me-1"></i>Bilet Al
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary btn-lg px-4" onclick="loginRequired()">
                                                <i class="bi bi-lock me-1"></i>Bilet Al
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sefer Bulunamadı -->
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-search display-1 text-muted"></i>
                        </div>
                        <h4 class="text-muted">Aradığınız kriterlerde sefer bulunamadı</h4>
                        <p class="text-muted">
                            Farklı tarih veya şehir seçerek tekrar deneyebilirsiniz.
                        </p>
                        <a href="index.php" class="btn btn-turuncu mt-3">
                            <i class="bi bi-house me-1"></i>Ana Sayfaya Dön
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- İlk Yükleme Mesajı -->
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-search display-1 text-brand"></i>
                    </div>
                    <h4>Sefer Arama</h4>
                    <p class="text-muted">
                        Yukarıdaki formu kullanarak sefer aramaya başlayın.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript -->
<script>
function loginRequired() {
    if (confirm('Bilet almak için giriş yapmanız gerekiyor. Giriş sayfasına yönlendirilmek istiyor musunuz?')) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
    }
}

function buyTicket(tripId) {
    // Bilet alma sayfasına yönlendir
    window.location.href = 'buy_ticket.php?trip_id=' + tripId;
}
</script>

<?php include '../includes/footer.php'; ?>