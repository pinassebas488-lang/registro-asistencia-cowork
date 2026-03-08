# Sistema de Asistencia QR - Dockerfile optimizado para Render
FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    zip \
    postgresql-client \
    gcc \
    make \
    autoconf \
    && docker-php-ext-install pdo_mysql mysqli zip pdo_pgsql \
    && docker-php-ext-enable pdo_mysql mysqli zip pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar Apache para permitir acceso
RUN a2enmod rewrite \
    && a2enmod headers \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -i 's/Require all denied/Require all granted/g' /etc/apache2/sites-available/000-default.conf \
    && echo "<Directory /var/www/html/>" > /etc/apache2/conf-available/custom-directory.conf \
    echo "    Options Indexes FollowSymLinks" >> /etc/apache2/conf-available/custom-directory.conf \
    echo "    AllowOverride All" >> /etc/apache2/conf-available/custom-directory.conf \
    echo "    Require all granted" >> /etc/apache2/conf-available/custom-directory.conf \
    echo "</Directory>" >> /etc/apache2/conf-available/custom-directory.conf \
    && a2enconf custom-directory

# Configurar PHP para producción
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Verificar que index.html existe y establecer permisos
RUN ls -la /var/www/html/ \
    && chmod 644 /var/www/html/index.html \
    && chown www-data:www-data /var/www/html/index.html

# Configurar permisos correctamente
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Exponer puerto estándar
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
