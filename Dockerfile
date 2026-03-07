# Usamos PHP con Apache
FROM php:8.2-apache

# Instalamos extensiones para MySQL (mysqli y pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitamos el módulo rewrite para rutas amigables
RUN a2enmod rewrite

# ESTA ES LA LÍNEA CLAVE:
# Copiamos el CONTENIDO de la carpeta REGISTRO a la raíz del servidor
COPY ./REGISTRO/ /var/www/html/

# Ajustamos permisos para que Apache pueda leer todo
RUN chown -R www-data:www-data /var/www/html

# Exponemos el puerto 80
EXPOSE 80

CMD ["apache2-foreground"]
