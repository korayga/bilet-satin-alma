<?php
require_once '../config/config.php';
require_once '../config/auth.php';
include 'includes/header.php';

$firm_id = $_SESSION['firm_id'] ?? null;

if (!$firm_id) {
    echo "<div class='alert alert-danger'>Firma bilgisi bulunamadı.</div>";
    include 'includes/footer.php';
    exit;
}

// Tüm seferleri çek
$all_trips = get_firm_trips($firm_id);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-bus-front"></i> Seferlerim</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTripModal">
            <i class="bi bi-plus-circle"></i> Yeni Sefer Ekle
        </button>
    </div>
</div>

<?php if (!empty($all_trips)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Güzergah</th>
                            <th>Kalkış</th>
                            <th>Varış</th>
                            <th>Fiyat</th>
                            <th>Otobüs Tipi</th>
                            <th>Doluluk</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_trips as $trip): ?>
                            <?php
                            $is_past = strtotime($trip['departure_time']) < time();
                            $occupancy_rate = (($trip['total_seats'] - $trip['available_seats']) / $trip['total_seats']) * 100;
                            ?>
                            <tr class="<?= $is_past ? 'table-secondary' : '' ?>">
                                <td><strong>#<?= $trip['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($trip['departure_city']) ?></strong>
                                    <i class="bi bi-arrow-right"></i>
                                    <strong><?= htmlspecialchars($trip['arrival_city']) ?></strong>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($trip['arrival_time'])) ?></td>
                                <td><strong>₺<?= number_format($trip['price'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($trip['bus_type']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 100px; height: 20px;">
                                            <?php
                                            $color = $occupancy_rate > 80 ? 'danger' : ($occupancy_rate > 50 ? 'warning' : 'success');
                                            ?>
                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $occupancy_rate ?>%">
                                                <?= round($occupancy_rate) ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?= $trip['booked_seats'] ?>/<?= $trip['total_seats'] ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($is_past): ?>
                                        <span class="badge bg-secondary">Geçmiş</span>
                                    <?php elseif ($trip['available_seats'] == 0): ?>
                                        <span class="badge bg-danger">Dolu</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="editTrip(<?= htmlspecialchars(json_encode($trip)) ?>)"
                                                <?= $is_past ? 'disabled' : '' ?>>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteTrip(<?= $trip['id'] ?>)"
                                                <?= $is_past || $trip['booked_seats'] > 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Henüz sefer eklemediniz</h4>
            <p class="text-muted">İlk seferinizi oluşturmak için yukarıdaki butona tıklayın.</p>
            <button class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#addTripModal">
                <i class="bi bi-plus-circle"></i> İlk Seferi Ekle
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Yeni Sefer Modal -->
<div class="modal fade" id="addTripModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Sefer Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/trip_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kalkış Şehri</label>
                            <input type="text" name="departure_city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Varış Şehri</label>
                            <input type="text" name="arrival_city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kalkış Tarihi/Saati</label>
                            <input type="datetime-local" name="departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Varış Tarihi/Saati</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fiyat (₺)</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Toplam Koltuk</label>
                            <input type="number" name="total_seats" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Otobüs Tipi</label>
                            <select name="bus_type" class="form-select" required>
                                <option value="2+1">2+1</option>
                                <option value="2+2">2+2</option>
                            
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Sefer Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Düzenle Modal -->
<div class="modal fade" id="editTripModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sefer Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/trip_process.php" id="editTripForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="trip_id" id="edit_trip_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kalkış Şehri</label>
                            <input type="text" name="departure_city" id="edit_departure_city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Varış Şehri</label>
                            <input type="text" name="arrival_city" id="edit_arrival_city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kalkış Tarihi/Saati</label>
                            <input type="datetime-local" name="departure_time" id="edit_departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Varış Tarihi/Saati</label>
                            <input type="datetime-local" name="arrival_time" id="edit_arrival_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fiyat (₺)</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Otobüs Tipi</label>
                            <select name="bus_type" id="edit_bus_type" class="form-select" required>
                                <option value="2+1">2+1</option>
                                <option value="2+2">2+2</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTrip(trip) {
    document.getElementById('edit_trip_id').value = trip.id;
    document.getElementById('edit_departure_city').value = trip.departure_city;
    document.getElementById('edit_arrival_city').value = trip.arrival_city;
    document.getElementById('edit_departure_time').value = trip.departure_time.replace(' ', 'T').substring(0, 16);
    document.getElementById('edit_arrival_time').value = trip.arrival_time.replace(' ', 'T').substring(0, 16);
    document.getElementById('edit_price').value = trip.price;
    document.getElementById('edit_bus_type').value = trip.bus_type;
    
    new bootstrap.Modal(document.getElementById('editTripModal')).show();
}

function deleteTrip(tripId) {
    if (confirm('Bu seferi silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process/trip_process.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'trip_id';
        idInput.value = tripId;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
