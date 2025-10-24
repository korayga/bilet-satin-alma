FROM php:8.2-apache

# Gerekli sistem paketlerini yükle (SQLite3 dahil)
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP uzantılarını yükle
RUN docker-php-ext-install pdo pdo_sqlite zip

# Apache mod_rewrite etkinleştir
RUN a2enmod rewrite

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Proje dosyalarını kopyala
COPY . .

# Dosya izinlerini ayarla
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod -R 777 /var/www/html/database

# Apache yapılandırması
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Port açılışı
EXPOSE 80

CMD ["apache2-foreground"]
