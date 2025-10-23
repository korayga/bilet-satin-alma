<?php
require_once '../config/config.php';
require_once '../config/auth.php';
include 'includes/header.php';

// Firma ID'yi al
$firm_id = $_SESSION['firm_id'] ?? null;

if (!$firm_id) {
    echo "<div class='alert alert-danger'>Firma bilgisi bulunamadı.</div>";
    include 'includes/footer.php';
    exit;
}

// Dashboard verilerini çek
$stats = get_firm_dashboard_stats($firm_id);
$recent_trips = get_firm_trips($firm_id, ['upcoming' => true, 'limit' => 5]);

$revenue_data = get_revenue_chart($firm_id, 7);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h2>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Toplam Sefer</h6>
                        <h3 class="mb-0"><?= number_format($stats['my_trips']) ?></h3>
                    </div>
                    <div class="text-primary fs-1">
                        <i class="bi bi-bus-front"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Satılan Bilet</h6>
                        <h3 class="mb-0"><?= number_format($stats['tickets_sold']) ?></h3>
                    </div>
                    <div class="text-success fs-1">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Toplam Gelir</h6>
                        <h3 class="mb-0">₺<?= number_format($stats['total_revenue'], 0, ',', '.') ?></h3>
                    </div>
                    <div class="text-warning fs-1">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Aktif Kupon</h6>
                        <h3 class="mb-0"><?= number_format($stats['active_coupons']) ?></h3>
                    </div>
                    <div class="text-info fs-1">
                        <i class="bi bi-tag"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Gelir Grafiği -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Son 7 Günlük Gelir</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenue_data['dates'])): ?>
                    <canvas id="revenueChart" height="80"></canvas>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('revenueChart');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?= json_encode($revenue_data['dates']) ?>,
                                datasets: [{
                                    label: 'Gelir (₺)',
                                    data: <?= json_encode($revenue_data['revenues']) ?>,
                                    borderColor: 'rgb(75, 192, 192)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    });
                    </script>
                <?php else: ?>
                    <p class="text-muted text-center py-5">Son 7 günde gelir verisi bulunmuyor.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

<!-- Yaklaşan Seferler -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Yaklaşan Seferler</h5>
                <a href="trips.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_trips)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Güzergah</th>
                                    <th>Tarih</th>
                                    <th>Saat</th>
                                    <th>Fiyat</th>
                                    <th>Doluluk</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_trips as $trip): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($trip['departure_city']) ?></strong>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <strong><?= htmlspecialchars($trip['arrival_city']) ?></strong>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($trip['departure_time'])) ?></td>
                                        <td><?= date('H:i', strtotime($trip['departure_time'])) ?></td>
                                        <td>₺<?= number_format($trip['price'], 0, ',', '.') ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <?php 
                                                $occupancy = (($trip['total_seats'] - $trip['available_seats']) / $trip['total_seats']) * 100;
                                                $color = $occupancy > 80 ? 'danger' : ($occupancy > 50 ? 'warning' : 'success');
                                                ?>
                                                <div class="progress-bar bg-<?= $color ?>" style="width: <?= $occupancy ?>%">
                                                    <?= round($occupancy) ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($trip['available_seats'] > 0): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Dolu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Yaklaşan sefer bulunmuyor.</p>
                        <a href="trips.php" class="btn btn-primary">Yeni Sefer Ekle</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
