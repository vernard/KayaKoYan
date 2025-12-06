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
USER root
COPY ./ /var/www/html/
RUN composer install
RUN npm install && npm run build
RUN chown -R www-data:www-data /var/www/html/!(node_modules)
USER www-data


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
