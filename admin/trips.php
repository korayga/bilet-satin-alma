<?php 
require_once 'includes/header2.php';

$action = $_GET['action'] ?? 'list';
$trip_id = $_GET['id'] ?? null;

// Mesaj gösterimi
if (isset($_GET['message'])) {
    $msg_type = $_GET['type'] ?? 'success';
    echo '<div class="alert alert-' . $msg_type . ' alert-dismissible fade show">
            ' . htmlspecialchars($_GET['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// Tüm firmaları çek (dropdown için)
$all_firms = get_all_firms();
?>

<?php if ($action == 'list'): ?>
    <!-- SEFER LİSTESİ -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-bus text-turuncu"></i> Tüm Seferler</h5>
            <a href="?action=add" class="btn btn-turuncu">
                <i class="fas fa-plus"></i> Yeni Sefer Ekle
            </a>
        </div>
        
        <!-- Filtreler -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="🔍 Şehir ara...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="firmFilter">
                    <option value="">Tüm Firmalar</option>
                    <?php foreach ($all_firms as $firm): ?>
                        <option value="<?= htmlspecialchars($firm['name']) ?>"><?= htmlspecialchars($firm['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tripsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Firma</th>
                        <th>Güzergah</th>
                        <th>Kalkış</th>
                        <th>Fiyat</th>
                        <th>Kapasite</th>
                        <th>Müsait</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $trips = get_all_trips_admin(['limit' => 100]);
                    
                    if (empty($trips)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-bus fa-2x mb-2"></i>
                                <div>Henüz sefer kaydı bulunmuyor</div>
                            </td>
                        </tr>
                    <?php else:
                        foreach ($trips as $trip): 
                    ?>
                    <tr>
                        <td><strong>#<?= $trip['id'] ?></strong></td>
                        <td>
                            <span class="badge bg-info"><?= htmlspecialchars($trip['firm_name']) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($trip['departure_city']) ?></strong> 
                            <i class="fas fa-arrow-right text-turuncu"></i> 
                            <strong><?= htmlspecialchars($trip['arrival_city']) ?></strong>
                        </td>
                        <td>
                            <small><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></small>
                        </td>
                        <td><strong>₺<?= number_format($trip['price'], 0, ',', '.') ?></strong></td>
                        <td><span class="badge bg-secondary"><?= $trip['total_seats'] ?></span></td>
                        <td>
                            <?php 
                            $fill_rate = (($trip['total_seats'] - $trip['available_seats']) / $trip['total_seats']) * 100;
                            $badge_class = $fill_rate >= 80 ? 'bg-danger' : ($fill_rate >= 50 ? 'bg-warning' : 'bg-success');
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= $trip['available_seats'] ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                               
                                <a href="?action=edit&id=<?= $trip['id'] ?>" class="btn btn-outline-primary" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="process/trip_process.php?action=delete&id=<?= $trip['id'] ?>" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirm('Bu seferi silmek istediğinize emin misiniz?')" 
                                   title="Sil">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action == 'add'): ?>
    <!-- YENİ SEFER EKLEME FORMU -->
    <div class="table-card">
        <h5 class="mb-4">
            <i class="fas fa-plus text-turuncu"></i> Yeni Sefer Ekle
        </h5>
        
        <form method="POST" action="process/trip_process.php" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="create">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Firma *</label>
                    <select name="firm_id" class="form-select" required>
                        <option value="">Firma Seçin</option>
                        <?php foreach ($all_firms as $firm): ?>
                            <option value="<?= $firm['id'] ?>"><?= htmlspecialchars($firm['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Firma seçimi gerekli</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Otobüs Tipi *</label>
                    <select name="bus_type" class="form-select" required>
                        <option value="2+2">2+2 (Standart)</option>
                        <option value="2+1">2+1 (VIP)</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kalkış Şehri *</label>
                    <input type="text" name="departure_city" class="form-control" required placeholder="Örn: İstanbul">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Varış Şehri *</label>
                    <input type="text" name="arrival_city" class="form-control" required placeholder="Örn: Ankara">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kalkış Zamanı *</label>
                    <input type="datetime-local" name="departure_time" class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Varış Zamanı *</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fiyat (₺) *</label>
                    <input type="number" name="price" class="form-control" required min="0" step="0.01" placeholder="450.00">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Toplam Koltuk *</label>
                    <input type="number" name="total_seats" class="form-control" required min="1" value="52">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Müsait Koltuk *</label>
                    <input type="number" name="available_seats" class="form-control" required min="0" value="52">
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-turuncu">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="?action=list" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>

<?php elseif ($action == 'view' && $trip_id): ?>
    <!-- SEFER DETAY -->
    <?php 
    $trip = get_trip_by_id($trip_id);
    if (!$trip): ?>
        <div class="alert alert-danger">Sefer bulunamadı!</div>
        <a href="?action=list" class="btn btn-outline-secondary">Geri Dön</a>
    <?php else: ?>
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-eye text-turuncu"></i> Sefer Detayları</h5>
            <a href="?action=list" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Geri
            </a>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-box">
                    <label class="fw-bold text-muted">Firma</label>
                    <div class="fs-5"><?= htmlspecialchars($trip['firm_name']) ?></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-box">
                    <label class="fw-bold text-muted">Otobüs Tipi</label>
                    <div class="fs-5"><?= htmlspecialchars($trip['bus_type']) ?></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-box">
                    <label class="fw-bold text-muted">Kalkış</label>
                    <div class="fs-5">
                        <i class="fas fa-map-marker-alt text-success"></i> 
                        <?= htmlspecialchars($trip['departure_city']) ?>
                    </div>
                    <small class="text-muted"><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></small>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-box">
                    <label class="fw-bold text-muted">Varış</label>
                    <div class="fs-5">
                        <i class="fas fa-map-marker-alt text-danger"></i> 
                        <?= htmlspecialchars($trip['arrival_city']) ?>
                    </div>
                    <small class="text-muted"><?= date('d.m.Y H:i', strtotime($trip['arrival_time'])) ?></small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box bg-light">
                    <label class="fw-bold text-muted">Fiyat</label>
                    <div class="fs-4 text-success">₺<?= number_format($trip['price'], 2, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box bg-light">
                    <label class="fw-bold text-muted">Toplam Koltuk</label>
                    <div class="fs-4"><?= $trip['total_seats'] ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box bg-light">
                    <label class="fw-bold text-muted">Müsait Koltuk</label>
                    <div class="fs-4 text-primary"><?= $trip['available_seats'] ?></div>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="d-flex gap-2">
            <a href="?action=edit&id=<?= $trip['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Düzenle
            </a>
            <a href="?action=list" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Listeye Dön
            </a>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($action == 'edit' && $trip_id): ?>
    <!-- SEFER DÜZENLEME FORMU -->
    <?php 
    $trip = get_trip_by_id($trip_id);
    if (!$trip): ?>
        <div class="alert alert-danger">Sefer bulunamadı!</div>
        <a href="?action=list" class="btn btn-outline-secondary">Geri Dön</a>
    <?php else: ?>
    <div class="table-card">
        <h5 class="mb-4">
            <i class="fas fa-edit text-turuncu"></i> Sefer Düzenle
        </h5>
        
        <form method="POST" action="process/trip_process.php" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Firma *</label>
                    <select name="firm_id" class="form-select" required>
                        <option value="">Firma Seçin</option>
                        <?php foreach ($all_firms as $firm): ?>
                            <option value="<?= $firm['id'] ?>" <?= $firm['id'] == $trip['firm_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($firm['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Firma seçimi gerekli</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Otobüs Tipi *</label>
                    <select name="bus_type" class="form-select" required>
                        <option value="2+2" <?= $trip['bus_type'] == '2+2' ? 'selected' : '' ?>>2+2 (Standart)</option>
                        <option value="2+1" <?= $trip['bus_type'] == '2+1' ? 'selected' : '' ?>>2+1 (VIP)</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kalkış Şehri *</label>
                    <input type="text" name="departure_city" class="form-control" required 
                           value="<?= htmlspecialchars($trip['departure_city']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Varış Şehri *</label>
                    <input type="text" name="arrival_city" class="form-control" required 
                           value="<?= htmlspecialchars($trip['arrival_city']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kalkış Zamanı *</label>
                    <input type="datetime-local" name="departure_time" class="form-control" required 
                           value="<?= date('Y-m-d\TH:i', strtotime($trip['departure_time'])) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Varış Zamanı *</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" required 
                           value="<?= date('Y-m-d\TH:i', strtotime($trip['arrival_time'])) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fiyat (₺) *</label>
                    <input type="number" name="price" class="form-control" required min="0" step="0.01" 
                           value="<?= $trip['price'] ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Toplam Koltuk *</label>
                    <input type="number" name="total_seats" class="form-control" required min="1" 
                           value="<?= $trip['total_seats'] ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Müsait Koltuk *</label>
                    <input type="number" name="available_seats" class="form-control" required min="0" 
                           value="<?= $trip['available_seats'] ?>">
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-turuncu">
                    <i class="fas fa-save"></i> Güncelle
                </button>
                <a href="?action=list" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Geçersiz işlem
    </div>
<?php endif; ?>

<script>
// Form validasyonu
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Tablo arama
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    filterTable();
});

document.getElementById('firmFilter')?.addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const firmValue = document.getElementById('firmFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#tripsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const showSearch = text.includes(searchValue);
        const showFirm = !firmValue || text.includes(firmValue);
        row.style.display = (showSearch && showFirm) ? '' : 'none';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>