FROM php:8.1-apache

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads directory
RUN mkdir -p uploads/products \
    && chown -R www-data:www-data uploads \
    && chmod -R 755 uploads

# Configure PHP
RUN echo "display_errors = On\nerror_reporting = E_ALL\nlog_errors = On" > /usr/local/etc/php/conf.d/error.ini

# Update Apache configuration to use PORT environment variable
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE ${PORT}

# Start Apache
CMD ["apache2-foreground"]