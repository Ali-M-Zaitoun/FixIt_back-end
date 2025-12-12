FROM php:8.3-apache

# Install required extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80