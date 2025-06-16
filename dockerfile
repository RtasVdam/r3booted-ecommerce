FROM php:8.1-apache

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create uploads directory structure and set permissions
RUN mkdir -p uploads/products \
    && chown -R www-data:www-data uploads \
    && chmod -R 775 uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure PHP for uploads
RUN echo "display_errors = On\nerror_reporting = E_ALL\nlog_errors = On\nupload_max_filesize = 10M\npost_max_size = 10M\nmax_file_uploads = 20" > /usr/local/etc/php/conf.d/uploads.ini

# Configure Apache to listen on port 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8080>/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]