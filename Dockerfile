FROM php:8.2-apache

# Update package list
RUN apt-get update && apt-get upgrade -y

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*