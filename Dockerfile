FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite and allow .htaccess overrides everywhere
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
      /etc/apache2/apache2.conf \
      /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
RUN find /etc/apache2 -name "*.conf" -exec \
      sed -i 's/AllowOverride None/AllowOverride All/g' {} \;

# Update Apache configuration to point to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html
