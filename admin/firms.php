<?php 
require_once 'includes/header2.php';

$action = $_GET['action'] ?? 'list';
$firm_id = $_GET['id'] ?? null;

// Mesaj gösterimi
if (isset($_GET['message'])) {
    $msg_type = $_GET['type'] ?? 'success';
    echo '<div class="alert alert-' . $msg_type . ' alert-dismissible fade show">
            ' . htmlspecialchars($_GET['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>

<?php if ($action == 'list'): ?>
    <!-- FİRMA LİSTESİ -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-building text-turuncu"></i> Firma Listesi</h5>
            <a href="?action=add" class="btn btn-turuncu">
                <i class="fas fa-plus"></i> Yeni Firma Ekle
            </a>
        </div>
        
        <!-- Arama -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="searchInput" placeholder="🔍 Firma adı, email veya telefon ara...">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="firmsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Firma Adı</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Durum</th>
                        <th>Sefer Sayısı</th>
                        <th>Admin Sayısı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Backend fonksiyonundan veri çek
                    $firms = get_all_firms();
                    
                    if (empty($firms)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <div>Henüz firma kaydı bulunmuyor</div>
                                <a href="?action=add" class="btn btn-sm btn-turuncu mt-2">
                                    <i class="fas fa-plus"></i> İlk Firmayı Ekle
                                </a>
                            </td>
                        </tr>
                    <?php else:
                        // Her firma için admin ve sefer sayısını hesapla
                        $db = getDB();
                        
                        foreach ($firms as $firm): 
                            // Firma admin sayısı
                            $admin_query = $db->prepare("SELECT COUNT(*) FROM users WHERE firm_id = :firm_id AND role = 'firmadmin'");
                            $admin_query->execute([':firm_id' => $firm['id']]);
                            $admin_count = $admin_query->fetchColumn();
                            
                            // Sefer sayısı
                            $trip_query = $db->prepare("SELECT COUNT(*) FROM trips WHERE firm_id = :firm_id");
                            $trip_query->execute([':firm_id' => $firm['id']]);
                            $trip_count = $trip_query->fetchColumn();
                    ?>
                    <tr>
                        <td><strong>#<?= $firm['id'] ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-turuncu text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 35px; height: 35px; font-size: 14px;">
                                    <?= strtoupper(substr($firm['name'], 0, 2)) ?>
                                </div>
                                <strong><?= htmlspecialchars($firm['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($firm['email'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($firm['phone'] ?? '-') ?></td>
                        <td>
                            <?php if ($firm['status'] == 'active'): ?>
                                <span class="badge badge-aktif">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-pasif">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary"><?= $trip_count ?> sefer</span></td>
                        <td><span class="badge bg-info"><?= $admin_count ?> admin</span></td>
                    
                        <td>
                            <div class="btn-group btn-group-sm">
                                
                                <a href="?action=edit&id=<?= $firm['id'] ?>" class="btn btn-outline-primary" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="process/firm_process.php?action=delete&id=<?= $firm['id'] ?>" class="btn btn-outline-danger" 
                                   onclick="return confirm('Bu firmayı silmek istediğinize emin misiniz?')" title="Sil">
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

<?php elseif ($action == 'add' || $action == 'edit'): ?>
    <!-- FİRMA EKLEME/DÜZENLEME FORMU -->
    <div class="table-card">
        <h5 class="mb-4">
            <i class="fas <?= $action == 'add' ? 'fa-plus' : 'fa-edit' ?> text-turuncu"></i> 
            Firma <?= $action == 'add' ? 'Ekle' : 'Düzenle' ?>
        </h5>
        
        <form method="POST" action="process/firm_process.php" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="<?= $action ?>">
            <?php if ($action == 'edit' && $firm_id): 
                $firm = get_firm($firm_id);
            ?>
                <input type="hidden" name="firm_id" value="<?= $firm_id ?>">
            <?php endif; ?>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Firma Adı *</label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?= $action == 'edit' && $firm ? htmlspecialchars($firm['name']) : '' ?>" 
                           placeholder="Örn: Metro Turizm">
                    <div class="invalid-feedback">Firma adı gerekli</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email *</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= $action == 'edit' && $firm ? htmlspecialchars($firm['email']) : '' ?>"
                           placeholder="Örn: info@firma.com">
                    <div class="invalid-feedback">Geçerli email adresi gerekli</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Telefon</label>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?= $action == 'edit' && $firm ? htmlspecialchars($firm['phone']) : '' ?>"
                           placeholder="Örn: 0850 123 45 67">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Logo URL</label>
                    <input type="url" name="logo" class="form-control" 
                           value="<?= $action == 'edit' && $firm ? htmlspecialchars($firm['logo']) : '' ?>"
                           placeholder="https://firma.com/logo.png">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Durum *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?= ($action == 'edit' && $firm && $firm['status'] == 'active') || $action == 'add' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= $action == 'edit' && $firm && $firm['status'] == 'inactive' ? 'selected' : '' ?>>Pasif</option>
                    </select>
                    <div class="invalid-feedback">Durum seçimi gerekli</div>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-bold">Adres</label>
                    <textarea name="address" class="form-control" rows="2" 
                              placeholder="Firma adresi"><?= $action == 'edit' && $firm ? htmlspecialchars($firm['address']) : '' ?></textarea>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-bold">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Firma hakkında kısa açıklama"><?= $action == 'edit' && $firm ? htmlspecialchars($firm['description']) : '' ?></textarea>
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

<?php elseif ($action == 'view'): 
    // Firma bilgilerini ve istatistiklerini çek
    if (!$firm_id) {
        header('Location: ?action=list&message=Firma ID gerekli&type=danger');
        exit();
    }
    
    $firm = get_firm($firm_id);
    if (!$firm) {
        header('Location: ?action=list&message=Firma bulunamadı&type=danger');
        exit();
    }
    
    $db = getDB();
    
    // Firma admin sayısı
    $admin_query = $db->prepare("SELECT COUNT(*) FROM users WHERE firm_id = :firm_id AND role = 'firmadmin'");
    $admin_query->execute([':firm_id' => $firm_id]);
    $admin_count = $admin_query->fetchColumn();
    
    // Toplam sefer sayısı
    $trip_query = $db->prepare("SELECT COUNT(*) FROM trips WHERE firm_id = :firm_id");
    $trip_query->execute([':firm_id' => $firm_id]);
    $trip_count = $trip_query->fetchColumn();
    
    // Toplam gelir
    $revenue_query = $db->prepare("
        SELECT COALESCE(SUM(t.total_price), 0) 
        FROM tickets t 
        INNER JOIN trips tr ON t.trip_id = tr.id 
        WHERE tr.firm_id = :firm_id
    ");
    $revenue_query->execute([':firm_id' => $firm_id]);
    $total_revenue = $revenue_query->fetchColumn();
    
    // Firma adminleri
    $admins_query = $db->prepare("SELECT * FROM users WHERE firm_id = :firm_id AND role = 'firmadmin' ORDER BY full_name");
    $admins_query->execute([':firm_id' => $firm_id]);
    $admins = $admins_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Son 5 sefer
    $trips_query = $db->prepare("
        SELECT * FROM trips 
        WHERE firm_id = :firm_id 
        ORDER BY departure_time DESC 
        LIMIT 5
    ");
    $trips_query->execute([':firm_id' => $firm_id]);
    $recent_trips = $trips_query->fetchAll(PDO::FETCH_ASSOC);
?>
    <!-- FİRMA DETAY -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="table-card text-center">
                <div class="bg-turuncu text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 32px;">
                    <?= strtoupper(substr($firm['name'], 0, 2)) ?>
                </div>
                <h4><?= htmlspecialchars($firm['name']) ?></h4>
                <span class="badge badge-<?= $firm['status'] == 'active' ? 'aktif' : 'pasif' ?> mb-3">
                    <?= $firm['status'] == 'active' ? 'Aktif' : 'Pasif' ?>
                </span>
                <hr>
                <div class="text-start">
                    <p class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i> 
                        <?= htmlspecialchars($firm['email'] ?? 'Email yok') ?>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i> 
                        <?= htmlspecialchars($firm['phone'] ?? 'Telefon yok') ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i> 
                        <?= htmlspecialchars($firm['address'] ?? 'Adres yok') ?>
                    </p>
                </div>
                <?php if (!empty($firm['description'])): ?>
                <hr>
                <div class="text-start">
                    <small class="text-muted"><?= htmlspecialchars($firm['description']) ?></small>
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-grid gap-2">
                    <a href="?action=edit&id=<?= $firm_id ?>" class="btn btn-turuncu">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <a href="?action=list" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- İstatistikler -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= $trip_count ?></div>
                        <div class="stat-label">Toplam Sefer</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= $admin_count ?></div>
                        <div class="stat-label">Firma Adminleri</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($total_revenue, 0, ',', '.') ?> ₺</div>
                        <div class="stat-label">Toplam Gelir</div>
                    </div>
                </div>
            </div>
            
            <!-- Firma Adminleri -->
            <div class="table-card mb-4">
                <h5 class="mb-3"><i class="fas fa-users text-info"></i> Firma Adminleri</h5>
                <?php if (empty($admins)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p>Bu firmaya henüz admin atanmamış</p>
                        <a href="users.php?action=add" class="btn btn-sm btn-info">
                            <i class="fas fa-user-plus"></i> Admin Ekle
                        </a>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Kullanıcı Adı</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                <td><code><?= htmlspecialchars($admin['username']) ?></code></td>
                                <td><?= htmlspecialchars($admin['email'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Son Seferler -->
            <div class="table-card">
                <h5 class="mb-3"><i class="fas fa-bus text-turuncu"></i> Son Seferler</h5>
                <?php if (empty($recent_trips)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-bus-alt fa-2x mb-2"></i>
                        <p>Bu firmaya ait henüz sefer bulunmuyor</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Güzergah</th>
                                <th>Tarih</th>
                                <th>Fiyat</th>
                                <th>Satış</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_trips as $trip): 
                                // Satış sayısı
                                $sold_query = $db->prepare("SELECT COUNT(*) FROM tickets WHERE trip_id = :trip_id");
                                $sold_query->execute([':trip_id' => $trip['id']]);
                                $sold_count = $sold_query->fetchColumn();
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['arrival_city']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></td>
                                <td><?= number_format($trip['price'], 0, ',', '.') ?> ₺</td>
                                <td>
                                    <?php 
                                    $percentage = ($sold_count / $trip['capacity']) * 100;
                                    $badge_class = $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $sold_count ?>/<?= $trip['capacity'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Tablo arama
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#firmsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>