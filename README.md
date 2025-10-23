
# 🚌 TRENDWAY Bilet Satın Alma Platformu

> **Modern, güvenli ve kullanıcı dostu otobüs bileti rezervasyon sistemi**

Bu platform, otobüs firmaları için **profesyonel bilet satış ve rezervasyon sistemi** sağlar. Modern web teknolojileri kullanılarak geliştirilmiş, **güvenli, hızlı ve mobil uyumlu** bir çözümdür.

## 📋 İçindekiler

  - [🎯 Neden Bu Proje?](#-neden-bu-proje)
  - [✨ Özellikler](#-özellikler)
      - [👤 Ziyaretçi (Giriş Yapmamış)](#-ziyaretçi-giriş-yapmamış)
      - [🎫 Müşteri (User) Özellikleri](#-müşteri-user-özellikleri)
      - [🏢 Firma Admin Özellikleri](#-firma-admin-özellikleri)
      - [⚙️ Sistem Admin Özellikleri](#️-sistem-admin-özellikleri)
  - [🛠 Teknolojiler](#-teknolojiler)
  - [🚀 Hızlı Başlangıç (Docker)](#-hızlı-başlangıç-docker)
  - [💻 Manuel Kurulum](#-manuel-kurulum)
  - [👥 Varsayılan Kullanıcılar](#-varsayılan-kullanıcılar)
  - [📱 Kullanım Senaryoları](#-kullanım-senaryoları)
  - [📂 Proje Yapısı](#-proje-yapısı)
  - [🗄️ Veritabanı Şeması](#️-veritabanı-şeması)
  - [🔒 Güvenlik](#-güvenlik)
  - [🚀 Gelecek Planları (Roadmap)](#-gelecek-planları-roadmap)
  - [🤝 Katkıda Bulunma](#-katkıda-bulunma)
  - [📞 İletişim & Destek](#-iletişim--destek)
  - [📄 Lisans](#-lisans)

-----

## 🎯 Neden Bu Proje?

✅ **Kolay Kurulum** - Docker ile 2 dakikada hazır.  
✅ **Güvenli** - Şifre hashleme, SQL injection koruması, RBAC ve CSRF koruması.  
✅ **Kullanıcı Dostu** - Sezgisel arayüz, mobil uyumlu (responsive) tasarım.  
✅ **Gerçek Zamanlı** - Koltuk durumu anlık kontrol, rezervasyon çakışması önleme.  
✅ **Ölçeklenebilir** - Modüler yapı, SQLite veya diğer veritabanlarına kolay geçiş.

-----

## ✨ Özellikler

### 👤 Ziyaretçi (Giriş Yapmamış)

  - ✅ Ana sayfada sefer arama ve listeleme
  - ✅ Sefer detaylarını görüntüleme
  - ❌ Bilet satın alma (giriş gerektirir)

### 🎫 Müşteri (User) Özellikleri

| Özellik | Açıklama |
| :--- | :--- |
| 🔍 **Sefer Arama** | Kalkış-varış-tarih filtresiyle hızlı arama |
| 💺 **Koltuk Seçimi** | 2+1 ve 2+2 düzeninde interaktif koltuk haritası |
| 💰 **Kupon Sistemi** | Global ve firma özel indirim kuponları uygulama |
| 🎟️ **Bilet İptal** | Sefer saatinden 1 saat öncesine kadar iptal + otomatik iade |
| 📄 **PDF İndirme** | Biletleri PDF formatında indirme |
| 👤 **Profil Yönetimi** | Kişisel bilgiler ve şifre değiştirme |

### 🏢 Firma Admin Özellikleri

| Özellik | Açıklama |
| :--- | :--- |
| 🚌 **Sefer Yönetimi** | Sefer ekleme, düzenleme, silme (CRUD) |
| 📊 **Dashboard** | Satış istatistikleri, gelir grafikleri |
| 🎁 **Kupon Yönetimi** | Firma özel indirim kuponları oluşturma |
| 📈 **Raporlar** | Popüler rotalar, doluluk oranları, müşteri listesi |
| 💺 **Koltuk Takibi** | Seferlerin anlık koltuk durumunu izleme |

### ⚙️ Sistem Admin Özellikleri

| Özellik | Açıklama |
| :--- | :--- |
| 🏢 **Firma Yönetimi** | Yeni firma ekleme, düzenleme, silme |
| 👥 **Kullanıcı Yönetimi** | User ve firma admin oluşturma, firmaya atama |
| 🎁 **Global Kuponlar** | Tüm firmalar için geçerli kupon sistemi yönetimi |
| 📊 **Sistem İstatistikleri** | Toplam sefer, bilet, gelir raporları |

-----

## 🛠 Teknolojiler

| Kategori | Teknoloji | Açıklama |
| :--- | :--- | :--- |
| **Backend** | **PHP 8.2+** | Modern, güvenli ve hızlı |
| **Database** | **SQLite 3** | Hafif, dosya tabanlı veritabanı |
| **DB Bağlantı** | **PDO** | Prepared statements ile SQL injection koruması |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) | |
| **CSS Framework** | **Bootstrap 5.3** | Responsive ve modern UI |
| **Charts** | **Chart.js** | Dashboard gelir grafikleri |
| **Icons** | Font Awesome 6 | İkonlar |
| **Authentication** | Session-based | Güvenli oturum yönetimi |
| **PDF Generation** | FPDF / Browser Print | Sunucu veya istemci taraflı PDF oluşturma |
| **Güvenlik** | **bcrypt** | Şifre hashleme (PASSWORD\_DEFAULT) |
| **DevOps** | **Docker** & Docker Compose | Container teknolojisi |
| **Web Server** | Apache 2.4 | Web server |

-----

## 🚀 Hızlı Başlangıç (Docker)

Sistemi 2 dakika içinde ayağa kaldırmak için önerilen yöntem Docker kullanmaktır.

1.  **Projeyi klonlayın:**

    ```bash
    git clone https://github.com/YAVUZLAR/bilet-satin-alma.git
    cd bilet-satin-alma
    ```

2.  **Container'ları başlatın:**

    ```bash
    docker-compose up -d
    ```

3.  **Veritabanını oluşturun:**

    ```bash
    docker exec -it bilet-web php config/db.php
    ```

4.  **(Opsiyonel) Demo verileri yükleyin:**

    ```bash
    docker exec -it bilet-web php config/seed_data.php
    ```

5.  **Uygulamaya erişin:**
    Tarayıcınızdan [http://localhost:8080](http://localhost:8080) (veya `docker-compose.yml` dosyanızda belirtilen portu) açın.

🎉 **Tebrikler! Sistem hazır.**

-----

## 💻 Manuel Kurulum

#### Gereksinimler

  - PHP 8.2+
  - SQLite3 extension
  - PDO SQLite extension
  - Apache/Nginx web server
  - `mod_rewrite` (Apache için)

#### Adımlar

1.  **Projeyi klonlayın** veya dosyaları web server dizininize kopyalayın (örn: `/var/www/html/bilet-satin-alma`).

2.  **Gerekli izinleri verin:**

    ```bash
    cd /var/www/html/bilet-satin-alma

    # Proje dosyalarına genel izin
    chmod -R 755 .

    # Veritabanı klasörüne yazma izni
    chmod -R 777 database/
    ```

3.  **Veritabanını oluşturun:**

    ```bash
    php config/db.php
    ```

4.  **(Opsiyonel) Demo verileri yükleyin:**

    ```bash
    php config/seed_data.php
    ```

5.  **Web Server Yapılandırması:**
    DocumentRoot (Ana Dizin) olarak projenin içindeki `/public` klasörünü göstermelisiniz.

    **Apache VirtualHost Örneği:**

    ```apache
    <VirtualHost *:80>
        ServerName bilet.local
        DocumentRoot /var/www/html/bilet-satin-alma/public

        <Directory "/var/www/html/bilet-satin-alma/public">
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

-----

## 👥 Varsayılan Kullanıcılar

Sistem ilk kurulumda veya demo verileri yüklendiğinde aşağıdaki test kullanıcıları oluşturulur:

| Rol | Kullanıcı Adı | Şifre | Bakiye | Açıklama |
| :--- | :--- | :--- | :--- | :--- |
| **Sistem Admin** | `admin` | `123456` | 5000 ₺ | Tüm sisteme erişim, firma ve kullanıcı yönetimi |
| **Firma Admin** | `metro_admin` | `123456` | 0 ₺ | Sadece "Metro Turizm" firmasının seferlerini yönetir |
| **Firma Admin**| `pamukkale_admin`| `123456` | 0 ₺ | Sadece "Pamukkale" firmasının seferlerini yönetir |
| **Müşteri** | `test` | `123456` | 1000 ₺ | Bilet arama, satın alma, iptal işlemleri |

-----

## 📱 Kullanım Senaryoları

### Senaryo 1: Müşteri Bilet Satın Alma

1.  Müşteri ana sayfadan Kalkış-Varış-Tarih seçerek sefer arar.
2.  Listelenen seferlerden uygun olanı seçer.
3.  İnteraktif koltuk haritasından koltuk seçer (max 4 koltuk).
4.  Varsa kupon kodunu girer.
5.  "Ödemeyi Tamamla" butonuna tıklar.
6.  Bilet oluşturulur ve "Biletlerim" sayfasına yönlendirilir.

### Senaryo 2: Firma Admin - Yeni Sefer Ekleme

1.  Firma admin (`metro_admin`) paneline giriş yapar.
2.  "Seferler" -\> "Yeni Sefer Ekle" menüsüne gider.
3.  Formu doldurur (Kalkış, Varış, Tarih, Fiyat, Otobüs Tipi, Koltuk Sayısı).
4.  Kaydeder. Sefer anında müşteriler için listelenmeye başlar.

### Senaryo 3: Sistem Admin - Yeni Firma Ekleme

1.  Sistem admin (`admin`) paneline giriş yapar.
2.  "Firmalar" -\> "Yeni Firma Ekle" menüsüne gider.
3.  Firma bilgilerini (Metro, Pamukkale vb.) girer.
4.  "Kullanıcılar" menüsünden yeni bir "Firma Admin" kullanıcısı oluşturur ve bu kullanıcıyı oluşturduğu firmaya atar.

-----

## 📂 Proje Yapısı

```
bilet-satin-alma/
│
├── 📄 Dockerfile            # Docker image tanımı
├── 📄 docker-compose.yml   # Container orkestrasyon
├── 📄 apache-config.conf   # Apache VirtualHost config
├── 📄 .gitignore           # Git ignore kuralları
├── 📄 README.md            # Bu dosya
│
├── 📁 config/              # Yapılandırma dosyaları
│   ├── config.php          # Ana yapılandırma
│   ├── db.php              # Database initialization
│   ├── auth.php            # Authentication işlemleri
│   └── seed_data.php       # Demo veri yükleme
│
├── 📁 database/            # Veritabanı dizini
│   └── database.sqlite     # SQLite dosyası (runtime'da oluşur)
│
├── 📁 public/              # 🎫 Müşteri Arayüzü (DocumentRoot)
│   ├── index.php           # Ana sayfa + sefer arama
│   ├── login.php           # Giriş sayfası
│   ├── register.php        # Kayıt sayfası
│   ├── logout.php          # Çıkış
│   ├── route_detail.php    # Sefer detay + arama sonuçları
│   ├── buy_ticket.php      # Bilet satın alma + koltuk seçimi
│   ├── my_tickets.php      # Kullanıcı biletleri + iptal
│   ├── download_ticket.php # PDF bilet oluşturma
│   └── profile.php         # Kullanıcı profili
│
├── 📁 admin/               # ⚙️ Sistem Admin Paneli
│   ├── index.php           # Dashboard (istatistikler)
│   ├── firms.php           # Firma CRUD
│   ├── users.php           # Kullanıcı yönetimi
│   ├── trips.php           # Tüm seferler listesi
│   ├── coupons.php         # Global kupon yönetimi
│   └── process/            # CRUD işlem dosyaları
│
├── 📁 firm_admin/          # 🏢 Firma Admin Paneli
│   ├── index.php           # Firma dashboard
│   ├── trips.php           # Firma seferleri CRUD
│   ├── coupons.php         # Firma kuponları CRUD
│   ├── reports.php         # Satış raporları
│   └── process/            # CRUD işlem dosyaları
│
├── 📁 includes/            # Ortak komponenler (Header, Footer)
│   ├── header.php          # Public header (navbar)
│   ├── footer.php          # Public footer
│   └── functions.php       # Yardımcı fonksiyonlar
│
└── 📁 assets/              # Static dosyalar
    ├── css/style.css       # Ana CSS
    ├── js/scripts.js       # JavaScript
    └── img/                # Resimler
```

-----

## 🗄️ Veritabanı Şeması

Sistem 7 ana tablo üzerine kuruludur:

1.  **users**: Kullanıcı bilgileri (user, firmadmin, admin rolleri dahil).
2.  **firms**: Otobüs firma bilgileri.
3.  **trips**: Sefer bilgileri (rota, tarih, fiyat, koltuk sayısı).
4.  **tickets**: Satın alınan biletlerin ana kaydı.
5.  **booked\_seats**: Hangi biletin hangi koltuk numarasını aldığını tutar.
6.  **coupons**: Global veya firma özel kupon tanımları.
7.  **user\_coupons**: Hangi kullanıcının hangi kuponu kullandığının kaydı.

-----

## 🔒 Güvenlik

| Önlem | Açıklama | Teknoloji |
| :--- | :--- | :--- |
| 🔐 **Şifre Hashleme** | Şifreler bcrypt ile hashlenip saklanır | `password_hash()` |
| 🛡️ **SQL Injection** | Prepared statements kullanımı | PDO |
| 🚫 **XSS Koruması** | Tüm kullanıcı girdisi ve DB çıktısı escape edilir | `htmlspecialchars()` |
| 🎭 **CSRF Koruması** | Formlara gizli token eklenerek kontrol sağlanır | Session Tokens |
| 👮 **RBAC** | Rol bazlı yetkilendirme (Admin, Firma Admin, User) | Session Kontrolü |
| ✅ **Input Validation** | Tüm form girdileri sunucu tarafında doğrulanır | PHP Validation |
| 🔒 **Session Güvenlik** | HTTP-only ve secure flag'ler (production'da) | PHP Sessions |

-----


## 🤝 Katkıda Bulunma

Katkılarınızı bekliyoruz\! Lütfen şu adımları takip edin:

1.  Projeyi **Fork** edin.
2.  Yeni özelliğiniz için bir branch oluşturun (`git checkout -b feature/amazing-feature`).
3.  Değişikliklerinizi commit edin (`git commit -m 'Add amazing feature'`).
4.  Branch'inizi push edin (`git push origin feature/amazing-feature`).
5.  Bir **Pull Request** açın.

-----

## 📞 İletişim & Destek

**Geliştirici: Koray Garip**

[](https://www.linkedin.com/in/koray-garip/)
[](https://github.com/korayga)

Sorunlar için GitHub Issues kullanabilir veya yukarıdaki profillerden ulaşabilirsiniz.

-----



