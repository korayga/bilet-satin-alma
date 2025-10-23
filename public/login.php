<?php
require_once __DIR__ . "/../config/auth.php";

// Eğer kullanıcı zaten giriş yapmışsa rol bazlı yönlendir
if(isset($_SESSION["id"])){
    if (is_admin()) {
        header('Location: ../admin/index.php');
    } elseif (is_firm_admin()) {
        header('Location: ../firm_admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$hata_mesaji = "";
$basari_mesaji = "";

// Çıkış mesajı
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $basari_mesaji = "Başarıyla çıkış yaptınız!";
}

// Kayıt başarı mesajı
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $basari_mesaji = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
}

if($_POST){
    $username = trim($_POST["username"]); 
    $password = $_POST["password"];
    
    if (empty($username) || empty($password)) {
        $hata_mesaji = "Kullanıcı adı ve şifre giriniz!";
    } else {
        if(user_access($username, $password)){
            // Kullanıcının son giriş zamanını güncelle
            $db = getDB(); // config.php'deki fonksiyonu kullan
            $user_id = $_SESSION['id'];
            $date = date('Y-m-d H:i:s');
            $sorgu = $db->prepare("UPDATE users SET updated_at = ? WHERE id = ?");
            $sorgu->execute([$date, $user_id]);
            
            // Rol bazlı yönlendirme
            if (is_admin()) {
                header("Location: ../admin/index.php");
            } elseif (is_firm_admin()) {
                header("Location: ../firm_admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $hata_mesaji = "Kullanıcı adı veya şifre hatalı!";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Ana Giriş Bölümü -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      
      <!-- Giriş Formu Kartı -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-5">
          
          <!-- Başlık -->
          <div class="text-center mb-4">
            <h3 class="text-brand fw-bold">Giriş Yap</h3>
            <p class="muted">Hesabınıza giriş yapın</p>
          </div>

          <!-- Başarı Mesajı -->
          <?php if (!empty($basari_mesaji)): ?>
            <div class="alert alert-success border-0" role="alert">
              <i class="bi bi-check-circle-fill"></i>
              <?= htmlspecialchars($basari_mesaji) ?>
            </div>
          <?php endif; ?>

          <!-- Hata Mesajı -->
          <?php if (!empty($hata_mesaji)): ?>
            <div class="alert alert-danger border-0" role="alert">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <?= htmlspecialchars($hata_mesaji) ?>
            </div>
          <?php endif; ?>

          <!-- Giriş Formu -->
          <form method="POST" class="needs-validation" novalidate>
            
            <div class="mb-3">
              <label class="form-label fw-semibold">Kullanıcı Adı</label>
              <input type="text" name="username" class="form-control form-control-lg" 
                     placeholder="Kullanıcı adınız" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
              <div class="invalid-feedback">Lütfen kullanıcı adınızı girin.</div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Şifre</label>
              <input type="password" name="password" class="form-control form-control-lg" 
                     placeholder="Şifreniz" required>
              <div class="invalid-feedback">Lütfen şifrenizi girin.</div>
            </div>

            <!-- Giriş Butonu -->
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-siyah btn-lg fw-semibold">
                <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
              </button>
            </div>

            <!-- Kayıt Linki -->
            <div class="text-center">
              <p class="muted small mb-0">
                Hesabınız yok mu? 
                <a href="register.php" class="text-brand text-decoration-none fw-semibold">Kayıt olun</a>
              </p>
            </div>

          </form>
        </div>
      </div>
      
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

