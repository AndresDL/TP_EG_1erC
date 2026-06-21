

# Use the official PHP image with Apache
FROM php:8.2-apache
 
# Install the mysqli extension (enable is handled automatically)
RUN apt-get update \
    && docker-php-ext-install mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
 
# Copy all project files into Apache's web root
COPY . /var/www/html/
 
# Point Apache document root to the INDEX subfolder (only target specific directives)
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/INDEX|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/INDEX>|g' /etc/apache2/apache2.conf
 
# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
 
# Expose port 80
EXPOSE 80



