# Dockerfile

# Use the latest composer base image
FROM composer:latest

# Install the pdo_pgsql extension
RUN apk --no-cache add postgresql-dev \
    && docker-php-ext-install pdo_pgsql

# Set the working directory to /app
WORKDIR /app

# Copy the contents of the current directory to the container at /app
COPY . /aaxis_test

# Run the Symfony installation script
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install Symfony ORM pack and Maker bundle
RUN composer require symfony/orm-pack \
    && composer require --dev symfony/maker-bundle

# Expose port 8000 for Symfony
EXPOSE 8000

# Command to start the Symfony server
CMD ["symfony", "server:start", "--no-tls"]
