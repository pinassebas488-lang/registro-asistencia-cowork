# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos extensiones necesarias para MySQL si las usas
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiamos todo tu código a la carpeta del servidor
COPY . /var/www/html/

# Exponemos el puerto 80
EXPOSE 80
