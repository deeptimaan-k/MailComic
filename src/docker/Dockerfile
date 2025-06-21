FROM php:8.3-apache

# Install cron and sendmail
RUN apt-get update && apt-get install -y cron sendmail && apt-get clean

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy everything from src/ into container
COPY ../ /var/www/html/

# Create required directories with correct permissions
RUN mkdir -p /var/www/html/emails /var/www/html/codes \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Ensure script is executable
RUN chmod +x /var/www/html/setup_cron.sh

# Start CRON and Apache
CMD bash -c "/var/www/html/setup_cron.sh && service cron start && exec apache2-foreground"
