<?php
require_once 'includes/header2.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';

// Tüm kullanıcıları çek
$users = [];
try {
    $query = $db->query("
        SELECT u.*, f.name as firm_name 
        FROM users u 
        LEFT JOIN firms f ON u.firm_id = f.id 
        ORDER BY u.created_at DESC
    ");
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Kullanıcı listesi hatası: " . $e->getMessage());
}

// Firmalar listesi (firma admin oluşturma için)
$firms = get_all_firms();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-users"></i> Kullanıcı Yönetimi</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-turuncu" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
        </button>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row g-3 mb-4">
    <?php
    $total_users = count($users);
    $admins = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
    $firm_admins = count(array_filter($users, fn($u) => $u['role'] === 'firmadmin'));
    $regular_users = count(array_filter($users, fn($u) => $u['role'] === 'user'));
    ?>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;">
                <i class="fas fa-users" style="color: #2196f3;"></i>
            </div>
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">Toplam Kullanıcı</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce4ec;">
                <i class="fas fa-user-shield" style="color: #e91e63;"></i>
            </div>
            <div class="stat-value"><?= $admins ?></div>
            <div class="stat-label">Admin</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0;">
                <i class="fas fa-user-tie" style="color: #ff9800;"></i>
            </div>
            <div class="stat-value"><?= $firm_admins ?></div>
            <div class="stat-label">Firma Admin</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;">
                <i class="fas fa-user" style="color: #27ae60;"></i>
            </div>
            <div class="stat-value"><?= $regular_users ?></div>
            <div class="stat-label">Müşteri</div>
        </div>
    </div>
</div>

<!-- Kullanıcı Listesi -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Ad Soyad</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Firma</th>
                    <th>Bakiye</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <div>Henüz kullanıcı bulunmuyor</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong>#<?= $user['id'] ?></strong></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                            <td>
                                <?php
                                $role_badges = [
                                    'admin' => '<span class="badge bg-danger">Admin</span>',
                                    'firmadmin' => '<span class="badge bg-warning">Firma Admin</span>',
                                    'user' => '<span class="badge bg-primary">Kullanıcı</span>'
                                ];
                                echo $role_badges[$user['role']] ?? '<span class="badge bg-secondary">Bilinmiyor</span>';
                                ?>
                            </td>
                            <td>
                                <?php if ($user['firm_name']): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($user['firm_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><strong>₺<?= number_format($user['balance'] ?? 0, 0, ',', '.') ?></strong></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" 
                                            onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Yeni Kullanıcı Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/user_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="role" id="role_select" class="form-select" required>
                            <option value="firmadmin">Firma Admin</option>
                            <option value="admin">Sistem Admin</option>
                        </select>
                        <small class="text-muted">Normal kullanıcılar kayıt sayfasından oluşturulur</small>
                    </div>
                    
                    <div class="mb-3" id="firm_select_container" style="display: none;">
                        <label class="form-label">Firma</label>
                        <select name="firm_id" class="form-select">
                            <option value="">Firma Seçin</option>
                            <?php foreach ($firms as $firm): ?>
                                <option value="<?= $firm['id'] ?>"><?= htmlspecialchars($firm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                   
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-turuncu">Kullanıcı Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Düzenle Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Kullanıcı Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process/user_process.php" id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    
                  
                    
                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre (Boş bırakılırsa değişmez)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-turuncu">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Rol değiştiğinde firma seçimini göster/gizle
document.getElementById('role_select').addEventListener('change', function() {
    const firmContainer = document.getElementById('firm_select_container');
    firmContainer.style.display = this.value === 'firmadmin' ? 'block' : 'none';
});

function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_full_name').value = user.full_name || '';
    document.getElementById('edit_email').value = user.email || '';
    
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId, username) {
    if (confirm(`"${username}" kullanıcısını silmek istediğinize emin misiniz?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process/user_process.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'user_id';
        idInput.value = userId;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>