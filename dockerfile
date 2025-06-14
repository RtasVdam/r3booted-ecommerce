FROM php:8.1-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite

# Copy application code
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/uploads/products
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 777 /var/www/html/uploads

# Create a simple index test
RUN echo "<?php phpinfo(); ?>" > /var/www/html/phpinfo.php

EXPOSE 80

CMD ["apache2-foreground"]