# Base stage with common components
FROM serversideup/php:8.4-fpm-apache AS base

USER root

RUN apt-get update && apt-get install -y \
    wget \
    vim \
    nano \
    && apt-get clean

# Install GD extension always
RUN install-php-extensions gd intl

# Production stage (default target)
FROM base AS production

# Install Node.js and NPM
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Switch back to the unprivileged user (www-data) for security
USER www-data

# Show node and npm version
RUN node --version
RUN npm --version

# Copy your application code
COPY --chown=www-data:www-data . /var/www/html/

RUN composer install --no-interaction --optimize-autoloader
# Install NPM dependencies and build assets
RUN npm install
RUN npm run build
# Clean up image
RUN rm /var/www/html/node_modules -rf

# Development stage with XDebug
FROM base AS development

USER root
# Install XDebug
RUN install-php-extensions xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.trigger_value=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Expose port 9003 for Xdebug
EXPOSE 9003

USER www-data

# Default target stage
FROM production
