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

// Firma kuponlarını çek
$coupons = get_firm_coupons($firm_id);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-ticket-perforated"></i> Kuponlarım</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
            <i class="bi bi-plus-circle"></i> Yeni Kupon Ekle
        </button>
    </div>
</div>

<?php if (!empty($coupons)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Kupon Kodu</th>
                            <th>İndirim</th>
                            <th>Kullanım</th>
                            <th>Kalan</th>
                            <th>Son Kullanım</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <?php
                            $is_expired = strtotime($coupon['expire_date']) < time();
                            $remaining = $coupon['usage_limit'] - $coupon['used_count'];
                            ?>
                            <tr>
                                <td><strong class="text-primary"><?= htmlspecialchars($coupon['code']) ?></strong></td>
                                <td><span class="badge bg-success">%<?= $coupon['discount_percentage'] ?></span></td>
                                <td><?= $coupon['used_count'] ?> / <?= $coupon['usage_limit'] ?></td>
                                <td>
                                    <?php if ($remaining > 0): ?>
                                        <span class="text-success"><strong><?= $remaining ?></strong></span>
                                    <?php else: ?>
                                        <span class="text-danger"><strong>0</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y', strtotime($coupon['expire_date'])) ?></td>
                                <td>
                                    <?php if ($is_expired): ?>
                                        <span class="badge bg-secondary">Süresi Dolmuş</span>
                                    <?php elseif ($remaining <= 0): ?>
                                        <span class="badge bg-warning">Tükenmiş</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editCoupon(<?= htmlspecialchars(json_encode($coupon)) ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteCoupon(<?= $coupon['id'] ?>)"
                                            <?= $coupon['used_count'] > 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
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
            <i class="bi bi-ticket text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Henüz kupon oluşturmadınız</h4>
            <p class="text-muted">İlk kuponunuzu oluşturmak için yukarıdaki butona tıklayın.</p>
            <button class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                <i class="bi bi-plus-circle"></i> İlk Kuponu Ekle
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Yeni Kupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Kupon Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/coupon_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Kupon Kodu</label>
                        <input type="text" name="code" class="form-control" 
                               placeholder="Örn: YILBASI25" required 
                               pattern="[A-Z0-9]+" 
                               title="Sadece büyük harf ve rakam kullanın">
                        <small class="text-muted">Sadece büyük harf ve rakam</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">İndirim Oranı (%)</label>
                        <input type="number" name="discount_percentage" class="form-control" 
                               min="1" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kullanım Limiti</label>
                        <input type="number" name="usage_limit" class="form-control" 
                               min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Son Kullanım Tarihi</label>
                        <input type="date" name="expire_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kupon Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Kupon Düzenle Modal -->
<div class="modal fade" id="editCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kupon Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/coupon_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="coupon_id" id="edit_coupon_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Kupon Kodu</label>
                        <input type="text" name="code" id="edit_code" class="form-control" 
                               pattern="[A-Z0-9]+" 
                               title="Sadece büyük harf ve rakam kullanın" required>
                        <small class="text-muted">Sadece büyük harf ve rakam</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">İndirim Oranı (%)</label>
                        <input type="number" name="discount_percentage" id="edit_discount_percentage" 
                               class="form-control" min="1" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kullanım Limiti</label>
                        <input type="number" name="usage_limit" id="edit_usage_limit" 
                               class="form-control" min="1" required>
                        <small class="text-muted" id="edit_used_info"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Son Kullanım Tarihi</label>
                        <input type="date" name="expire_date" id="edit_expire_date" 
                               class="form-control" required>
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
function editCoupon(coupon) {
    document.getElementById('edit_coupon_id').value = coupon.id;
    document.getElementById('edit_code').value = coupon.code;
    document.getElementById('edit_discount_percentage').value = coupon.discount_percentage;
    document.getElementById('edit_usage_limit').value = coupon.usage_limit;
    document.getElementById('edit_usage_limit').min = coupon.used_count;
    document.getElementById('edit_used_info').textContent = 
        coupon.used_count > 0 ? `Bu kupon ${coupon.used_count} kez kullanıldı. Limit bundan az olamaz.` : '';
    document.getElementById('edit_expire_date').value = coupon.expire_date;
    
    new bootstrap.Modal(document.getElementById('editCouponModal')).show();
}

function deleteCoupon(couponId) {
    if (confirm('Bu kuponu silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process/coupon_process.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'coupon_id';
        idInput.value = couponId;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>