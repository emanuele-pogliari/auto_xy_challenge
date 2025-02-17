# Usa PHP con estensioni necessarie
FROM php:8.2-cli

# Imposta la working directory
WORKDIR /var/www/html

# Installa estensioni PHP necessarie
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Installa Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia il codice nel container
COPY . /var/www/html

# Installa le dipendenze di Symfony
RUN composer install --no-scripts --no-autoloader

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html

RUN mkdir -p /var/www/html/var && chmod -R 775 /var/www/html/var

# Esponi la porta per Symfony
EXPOSE 8000

# Comando per avviare il server Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]


