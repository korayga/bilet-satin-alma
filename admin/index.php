<?php 
require_once 'includes/header2.php';

// Admin dashboard - Backend verilerini çek
$system_stats = get_system_stats();
$recent_firms = get_all_firms(['limit' => 5]);
?>

<!-- İstatistik Kartları -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;">
                <i class="fas fa-building" style="color: #27ae60;"></i>
            </div>
            <div class="stat-value"><?= number_format($system_stats['total_firms']) ?></div>
            <div class="stat-label">Toplam Firma</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;">
                <i class="fas fa-users" style="color: #2196f3;"></i>
            </div>
            <div class="stat-value"><?= number_format($system_stats['total_users']) ?></div>
            <div class="stat-label">Toplam Kullanıcı</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0;">
                <i class="fas fa-bus" style="color: #ff9800;"></i>
            </div>
            <div class="stat-value"><?= number_format($system_stats['total_trips']) ?></div>
            <div class="stat-label">Toplam Sefer</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce4ec;">
                <i class="fas fa-ticket-alt" style="color: #e91e63;"></i>
            </div>
            <div class="stat-value"><?= number_format($system_stats['total_tickets_sold']) ?></div>
            <div class="stat-label">Satılan Bilet</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sol Kolon -->
    <div class="col-lg-8">
        <!-- Son Firmalar -->
        <div class="table-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-building text-turuncu"></i> Kayıtlı Firmalar</h5>
                <a href="firms.php" class="btn btn-sm btn-turuncu">Tümünü Gör</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Firma Adı</th>
                            <th>Email</th>
                            <th>Telefon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_firms)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <div>Henüz firma kaydı bulunmuyor</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($recent_firms, 0, 5) as $firm): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($firm['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($firm['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($firm['phone'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sağ Kolon -->
    <div class="col-lg-4">
        <!-- Hızlı İşlemler -->
        <div class="table-card mb-4">
            <h5 class="mb-3"><i class="fas fa-bolt text-warning"></i> Hızlı İşlemler</h5>
            <div class="d-grid gap-2">
                <button class="btn btn-turuncu" onclick="window.location.href='firms.php'">
                    <i class="fas fa-plus"></i> Firma Yönetimi
                </button>
                <button class="btn btn-outline-primary" onclick="window.location.href='users.php'">
                    <i class="fas fa-user-plus"></i> Kullanıcı Yönetimi
                </button>
                <button class="btn btn-outline-success" onclick="window.location.href='coupons.php'">
                    <i class="fas fa-ticket-alt"></i> Kupon Yönetimi
                </button>
                <button class="btn btn-outline-info" onclick="window.location.href='trips.php'">
                    <i class="fas fa-bus"></i> Sefer Yönetimi
                </button>
            </div>
        </div>
        
        <!-- Son Bilet Satışları -->
        <div class="table-card">
            <h5 class="mb-3"><i class="fas fa-ticket-alt text-info"></i> Son Satışlar</h5>
            <?php
            // Son 5 bilet satışını çek
            $recent_tickets=recent_ticket();
            ?>
            <?php if (!empty($recent_tickets)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_tickets as $ticket): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <small class="text-muted"><?= htmlspecialchars($ticket['ticket_code']) ?></small>
                                        <small class="badge bg-secondary"><?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></small>
                                    </div>
                                    <div class="small fw-bold">
                                        <?= htmlspecialchars($ticket['departure_city']) ?> → 
                                        <?= htmlspecialchars($ticket['arrival_city']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($ticket['full_name']) ?>
                                        <?php if (isset($ticket['firm_name'])): ?>
                                            | <i class="fas fa-building"></i> <?= htmlspecialchars($ticket['firm_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <strong class="text-success">₺<?= number_format($ticket['total_price'], 0, ',', '.') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <div>Henüz satış bulunmuyor</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>