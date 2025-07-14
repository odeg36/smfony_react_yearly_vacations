FROM php:8.2-cli

# Install system dependencies needed for composer and symfony (git, unzip, curl)
RUN apt-get update && apt-get install -y git unzip curl && rm -rf /var/lib/apt/lists/*

# Install Symfony CLI (optional, useful for development)
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Install Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/

# Copy project files into the container
COPY . .

# Install PHP dependencies (without dev, optimized autoloader)
RUN composer install --no-dev --optimize-autoloader

# Set default command to run Symfony console
ENTRYPOINT ["php", "bin/console"]
