FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache configuration to point to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Inject a Directory block for the document root with AllowOverride All + sitemap rewrite
# Doing this in the VirtualHost avoids any .htaccess AllowOverride race condition
RUN sed -i 's|</VirtualHost>|    <Directory /var/www/html/public>\n        Options -Indexes +FollowSymLinks\n        AllowOverride All\n        Require all granted\n        RewriteEngine On\n        RewriteRule ^sitemap\\.xml$ sitemap.php [L,QSA]\n    </Directory>\n</VirtualHost>|' \
    /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html
