# Usamos PHP 8.2 con Apache
FROM php:8.2-apache

# Instalamos librerías necesarias para MySQL y PostgreSQL
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    zip \
    postgresql-client \
    && docker-php-ext-install pdo_mysql mysqli zip pdo_pgsql

# Habilitamos el módulo de reescritura de Apache (útil para rutas amigables)
RUN a2enmod rewrite

# COPIAMOS EL CONTENIDO DE LA CARPETA REGISTRO AL DIRECTORIO WEB
COPY . /var/www/html/

# Ajustamos permisos para que Apache pueda trabajar
RUN chown -R www-data:www-data /var/www/html

# Render usa el puerto 80 por defecto para Apache
EXPOSE 80

CMD ["apache2-foreground"]
