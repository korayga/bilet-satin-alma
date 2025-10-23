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

// Raporlama verileri
$stats = get_firm_dashboard_stats($firm_id);
$revenue_30days = get_revenue_chart($firm_id, 30);
$popular_routes = get_popular_routes($firm_id, 10);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-graph-up"></i> Raporlar ve İstatistikler</h2>
    </div>
</div>

<!-- Özet Kartlar -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <h6 class="mb-1 opacity-75">Toplam Gelir</h6>
                <h2 class="mb-0">₺<?= number_format($stats['total_revenue'], 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <h6 class="mb-1 opacity-75">Satılan Bilet</h6>
                <h2 class="mb-0"><?= number_format($stats['tickets_sold']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <h6 class="mb-1 opacity-75">Toplam Sefer</h6>
                <h2 class="mb-0"><?= number_format($stats['my_trips']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body">
                <h6 class="mb-1 opacity-75">Ortalama Bilet Fiyatı</h6>
                <h2 class="mb-0">
                    <?php 
                    $avg = $stats['tickets_sold'] > 0 ? $stats['total_revenue'] / $stats['tickets_sold'] : 0;
                    echo '₺' . number_format($avg, 0, ',', '.');
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Son 30 Gün Gelir Grafiği -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Son 30 Günlük Gelir Grafiği</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenue_30days['dates'])): ?>
                    <canvas id="revenue30Chart" height="80"></canvas>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('revenue30Chart');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode($revenue_30days['dates']) ?>,
                                datasets: [{
                                    label: 'Günlük Gelir (₺)',
                                    data: <?= json_encode($revenue_30days['revenues']) ?>,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
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
                    <p class="text-muted text-center py-5">Son 30 günde gelir verisi bulunmuyor.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
