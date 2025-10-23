<?php 
require_once '../config/config.php';
require_once '../config/auth.php';

// 3 rastgele sefer çek
$random_trips = get_random_trips(3);


// Türkiye'nin 81 ili
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

include '../includes/header.php'; 
?>

<section class="hero-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1>TrendWay Bilet</h1>
        <p>Yolculuğun Akıllı Hali — Premium Konfor, Modern Deneyim</p>
      </div>
      
      <?php if (is_logged_in() && is_user()): ?>
        <div class="user-balance">
          <span class="badge fs-6 px-3 py-2" style="background-color: var(--brand); color: white;">
            <i class="bi bi-wallet2 me-1"></i>
            ₺<?= number_format(get_balance() ?? 0, 0, ',', '.') ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="container my-5">
  <div class="search-box mx-auto" style="max-width: 800px;">
    <form method="GET" action="route_detail.php" class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">
          <i class="bi bi-geo-alt me-1"></i>Kalkış Noktası
        </label>
        <select name="from" class="form-select form-select-lg" required>
          <option value="">Kalkış şehri seçin</option>
          <?php foreach($iller as $il): ?>
            <option value="<?= $il ?>" <?= ($_GET['from'] ?? '') == $il ? 'selected' : '' ?>>
              <?= $il ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">
          <i class="bi bi-geo-fill me-1"></i>Varış Noktası
        </label>
        <select name="to" class="form-select form-select-lg" required>
          <option value="">Varış şehri seçin</option>
          <?php foreach($iller as $il): ?>
            <option value="<?= $il ?>" <?= ($_GET['to'] ?? '') == $il ? 'selected' : '' ?>>
              <?= $il ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button type="submit" class="btn btn-turuncu btn-lg w-100 fw-semibold">
          <i class="bi bi-search"></i> Ara
        </button>
      </div>
    </form>
  </div>
</div>

<div class="container my-5">
  <div class="row g-4">
    <?php if (!empty($random_trips)): ?>
      <?php foreach ($random_trips as $trip): ?>
        <!-- Dinamik Sefer Kartı -->
        <div class="col-md-4">
          <div class="card p-3">
            <h5><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['arrival_city']) ?></h5>
            <p class="text-muted">
              <i class="bi bi-calendar me-1"></i>
              <?= date('d M Y - H:i', strtotime($trip['departure_time'])) ?>
            </p>
            <p class="text-muted small">
              <i class="bi bi-building me-1"></i>
              <?= htmlspecialchars($trip['firm_name']) ?>
            </p>
            <p class="price">₺<?= number_format($trip['price'], 0, ',', '.') ?></p>
            <p class="text-success small">
              <i class="bi bi-check-circle me-1"></i>
              <?= $trip['total_seats'] ?> koltuk mevcut
            </p>
            <p class="text-muted small">   
              <?= $trip['bus_type'] ?> otobüs
            </p>
            
            <?php if (is_logged_in()): ?>
              <!-- Giriş yapmış kullanıcı için buton -->
              <button class="btn btn-payment w-100" onclick="buyTicket(<?= $trip['id'] ?>)">
                <i class="bi bi-ticket me-1"></i>Bilet Al
              </button>
            <?php else: ?>
              <!-- Misafir kullanıcı için uyarı butonu -->
              <button class="btn btn-outline-secondary w-100" onclick="loginRequired()">
                <i class="bi bi-lock me-1"></i>Bilet Al
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Sefer bulunamadı mesajı -->
      <div class="col-12 text-center">
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          Şu anda gösterilecek sefer bulunmuyor.
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>


<!-- SIKÇA SORULAN SORULAR BÖLÜMÜ -->
<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">
      <i class="bi bi-question-circle text-primary"></i> Sıkça Sorulan Sorular
    </h2>
    <p class="text-muted">Merak ettikleriniz için yanıtlar</p>
  </div>
  
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="accordion" id="faqAccordion">
        <!-- Soru 1 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
              <i class="bi bi-ticket-perforated text-turuncu me-2"></i>
              Biletimi nasıl alabilirim?
            </button>
          </h2>
          <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p class="mb-2">Bilet almak için şu adımları izleyin:</p>
              <ol class="mb-0">
                <li>Ana sayfada <strong>kalkış ve varış noktanızı</strong> seçin</li>
                <li>Size uygun seferi bulun ve <strong>"Bilet Al"</strong> butonuna tıklayın</li>
                <li>Koltuk seçimi yapın (istediğiniz kadar, maksimum 4 koltuk)</li>
                <li>İndirim kuponunuz varsa uygulayın</li>
                <li>Bakiyenizden ödeme yapın ve biletiniz otomatik oluşturulur</li>
              </ol>
              <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Not:</strong> Bilet almak için giriş yapmanız gerekmektedir.
              </div>
            </div>
          </div>
        </div>

        <!-- Soru 2 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
              <i class="bi bi-credit-card text-turuncu me-2"></i>
              Ödeme nasıl yapılır?
            </button>
          </h2>
          <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p>TrendWay,şu anlık sanal<strong>bakiye sistemi</strong> ile çalışmaktadır:</p>
              <p>TrendWay,en kısa sürede gerçek zamanlı güvenli ödeme sistemine geçicektir:</p>
              
              <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Önemli:</strong> Yetersiz bakiye durumunda bilet alamazsınız. Lütfen bakiyenizi kontrol edin.
              </div>
            </div>
          </div>
        </div>

        <!-- Soru 3 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
              <i class="bi bi-x-circle text-turuncu me-2"></i>
              Biletimi iptal edebilir miyim?
            </button>
          </h2>
          <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p>Evet, belirli koşullar altında iptal edebilirsiniz:</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="border border-success rounded p-3 bg-light">
                    <h6 class="text-success mb-2">
                      <i class="bi bi-check-circle me-1"></i> İptal Edilebilir
                    </h6>
                    <ul class="small mb-0">
                      <li>Sefer saatine <strong>1 saatten fazla</strong> varsa</li>
                      <li>Bilet durumu <strong>"Aktif"</strong> ise</li>
                      <li>Tam ücret iadesi yapılır</li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="border border-danger rounded p-3 bg-light">
                    <h6 class="text-danger mb-2">
                      <i class="bi bi-x-circle me-1"></i> İptal Edilemez
                    </h6>
                    <ul class="small mb-0">
                      <li>Sefer saatine <strong>1 saatten az</strong> kaldıysa</li>
                      <li>Bilet zaten iptal edilmişse</li>
                      <li>Sefer geçmiş tarihli ise</li>
                    </ul>
                  </div>
                </div>
              </div>
              <p class="mt-3 mb-0">
                <i class="bi bi-arrow-right text-turuncu me-2"></i>
                <strong>İptal İşlemi:</strong> "Biletlerim" sayfasından ilgili bilete tıklayın ve "Bileti İptal Et" butonunu kullanın.
              </p>
            </div>
          </div>
        </div>

        <!-- Soru 4 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
              <i class="bi bi-gift text-turuncu me-2"></i>
              İndirim kuponu nasıl kullanılır?
            </button>
          </h2>
          <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p>İndirim kuponları bilet alırken kullanılabilir:</p>
              <ol class="mb-3">
                <li>Bilet alma sayfasında <strong>"İndirim Kuponu"</strong> alanını bulun</li>
                <li>Kupon kodunuzu girin (örn: SONBAHAR25)</li>
                <li><strong>"Kuponu Uygula"</strong> butonuna tıklayın</li>
                <li>İndirim tutarı otomatik hesaplanır ve toplam fiyattan düşülür</li>
              </ol>
              <div class="alert alert-success mb-0">
                <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Kupon Kuralları</h6>
                <ul class="small mb-0">
                  <li>Her kupon <strong>sadece 1 kez</strong> kullanılabilir</li>
                  <li>Kuponun <strong>süresi dolmamış</strong> olmalı</li>
                  <li>Kuponun <strong>kullanım limiti</strong> dolmamış olmalı</li>
                  <li>Aynı kullanıcı aynı kuponu tekrar kullanamaz</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Soru 5 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
              <i class="bi bi-person-badge text-turuncu me-2"></i>
              Hesap nasıl oluşturulur?
            </button>
          </h2>
          <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p>Ücretsiz hesap oluşturmak çok kolay:</p>
              <ol class="mb-3">
                <li>Sağ üstteki <strong>"Kayıt Ol"</strong> butonuna tıklayın</li>
                <li>Formu doldurun:
                  <ul>
                    <li>Kullanıcı adı (benzersiz olmalı)</li>
                    <li>Ad Soyad</li>
                    <li>Email adresi</li>
                    <li>Güvenli şifre</li>
                  </ul>
                </li>
                <li><strong>"Kayıt Ol"</strong> butonuna tıklayın</li>
             
              </ol>
              <div class="alert alert-info mb-0">
                <i class="bi bi-check-circle me-2"></i>
                Kayıt sonrası hemen bilet almaya başlayabilirsiniz!
              </div>
            </div>
          </div>
        </div>

        <!-- Soru 6 -->
        <div class="accordion-item border-0 shadow-sm mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
              <i class="bi bi-printer text-turuncu me-2"></i>
              Biletimi nasıl indirebilirim?
            </button>
          </h2>
          <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              <p>Biletlerinizi kolayca indirebilir ve yazdırabilirsiniz:</p>
              <ol class="mb-3">
                <li><strong>"Biletlerim"</strong> sayfasına gidin</li>
                <li>İndirmek istediğiniz bilete tıklayın</li>
                <li><strong>"Bileti İndir/Yazdır"</strong> butonuna tıklayın</li>
                <li>PDF formatında biletiniz açılır</li>
                <li>İsterseniz kaydedin, isterseniz yazdırın</li>
              </ol>
              <div class="row">
                <div class="col-md-6">
                  <div class="bg-light border rounded p-2 small">
                    <i class="bi bi-hash text-turuncu me-1"></i>
                    <strong>Bilet Kodu:</strong> Benzersiz bilet numarası
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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

// Aynı şehir seçimini engelle
document.addEventListener('DOMContentLoaded', function() {
    const fromSelect = document.querySelector('select[name="from"]');
    const toSelect = document.querySelector('select[name="to"]');
    
    function updateSelectOptions() {
        const fromValue = fromSelect.value;
        const toValue = toSelect.value;
        
        // Kalkış seçildiğinde varıştan aynı şehri gizle
        Array.from(toSelect.options).forEach(option => {
            if (option.value === fromValue && fromValue !== '') {
                option.style.display = 'none';
            } else {
                option.style.display = 'block';
            }
        });
        
        // Varış seçildiğinde kalkıştan aynı şehri gizle
        Array.from(fromSelect.options).forEach(option => {
            if (option.value === toValue && toValue !== '') {
                option.style.display = 'none';
            } else {
                option.style.display = 'block';
            }
        });
    }
    
    fromSelect.addEventListener('change', updateSelectOptions);
    toSelect.addEventListener('change', updateSelectOptions);
    
    // Sayfa yüklendiğinde kontrol et
    updateSelectOptions();
});
</script>

<?php include '../includes/footer.php'; ?>
