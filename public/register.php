<?php

require_once '../config/auth.php';

if ($_POST) {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $result = user_register($username, $password, $fullname, $email);

    if ($result) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $error = "Bu kullanıcı adı veya e-posta zaten kayıtlı.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Ana Kayıt Bölümü -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      
      <!-- Kayıt Formu Kartı -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-5">
          
          <!-- Başlık -->
          <div class="text-center mb-4">
            <h3 class="text-brand fw-bold">Kayıt Ol</h3>
            <p class="muted">TrendWay Bilet'e hoş geldiniz</p>
          </div>

          <!-- Hata Mesajı -->
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0" role="alert">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <!-- Kayıt Formu -->
          <form method="POST" class="needs-validation" novalidate>
            
            <div class="mb-3">
              <label class="form-label fw-semibold">Ad Soyad</label>
              <input type="text" name="fullname" class="form-control form-control-lg" 
                     placeholder="Adınız ve soyadınız" required>
              <div class="invalid-feedback">Lütfen ad ve soyadınızı girin.</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Kullanıcı Adı</label>
              <input type="text" name="username" class="form-control form-control-lg" 
                     placeholder="Kullanıcı adınız" required>
              <div class="invalid-feedback">Lütfen bir kullanıcı adı seçin.</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">E-posta</label>
              <input type="email" name="email" class="form-control form-control-lg" 
                     placeholder="ornek@email.com" required>
              <div class="invalid-feedback">Lütfen geçerli bir e-posta adresi girin.</div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Şifre</label>
              <input type="password" name="password" class="form-control form-control-lg" 
                     placeholder="Güçlü bir şifre seçin" required>
              <div class="invalid-feedback">Lütfen bir şifre girin.</div>
            </div>

            <!-- Kayıt Butonu -->
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-turuncu btn-lg fw-semibold">
                <i class="bi bi-person-plus"></i> Kayıt Ol
              </button>
            </div>

            <!-- Giriş Linki -->
            <div class="text-center">
              <p class="muted small mb-0">
                Zaten hesabınız var mı? 
                <a href="login.php" class="text-brand text-decoration-none fw-semibold">Giriş yapın</a>
              </p>
            </div>

          </form>
        </div>
      </div>
      
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
