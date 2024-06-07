ARG ALPINE_VERSION=3.19
FROM alpine:${ALPINE_VERSION}
LABEL Maintainer="Tim de Pater <code@trafex.nl>"
LABEL Description="Lightweight container with Nginx 1.24 & PHP 8.3 based on Alpine Linux."
# Setup document root
WORKDIR /var/www/html

# Install packages and remove default server definition
RUN apk add --no-cache \
  bash \
  tzdata \
  curl \
  nginx \
  php83 \
  php83-ctype \
  php83-curl \
  php83-dom \
  php83-fileinfo \
  php83-fpm \
  php83-gd \
  php83-intl \
  php83-mbstring \
  php83-bcmath \
  php83-gmp \
  php83-mysqli \
  php83-opcache \
  php83-openssl \
  php83-phar \
  php83-zip \
  php83-session \
  php83-tokenizer \
  php83-pdo \ 
  php83-pdo_mysql \
  supervisor

# Configure nginx - http
COPY config/nginx.conf /etc/nginx/nginx.conf
COPY config/conf.d /etc/nginx/conf.d/

# Configure PHP-FPM
ENV PHP_INI_DIR="/etc/php83" \
    TZ="UTC" \
    INSTALL="FALSE" \
    ADMIN_USER="" \
    ADMIN_PASS="" \
    ADMIN_EMAIL="" \
    URL_PATH="" \
    DB_HOST="" \
    DB_USER="" \
    DB_PASS="" \
    DB="" \
    DB_PREFIX=""

COPY config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY --chown=nobody config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Configure supervisord
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html /run /var/lib/nginx /var/log/nginx

# Create symlink for php
RUN ln -s /usr/bin/php83 /usr/bin/php

# Switch to use a non-root user from here on
USER nobody

# Add application
COPY --chown=nobody config/amxbans/web/ /var/www/html/
COPY --chown=nobody config/amxbans_startup.sh /usr/bin/amxbans_startup.sh

# Backup installation files
#COPY --chown=nobody config/amxbans/web/setup.php /tmp/amxbans_install/setup.php
#COPY --chown=nobody config/amxbans/web/install/ /tmp/amxbans_install/install/

# Expose the port nginx is reachable on
EXPOSE 80

# Let supervisord start nginx & php-fpm
CMD [ "/bin/bash", "/usr/bin/amxbans_startup.sh" ]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:80/fpm-ping || exit 1
