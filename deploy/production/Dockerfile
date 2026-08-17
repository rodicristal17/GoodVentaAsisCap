ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libcurl4-openssl-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        ca-certificates \
        openssh-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd mbstring mysqli pdo_mysql zip \
    && (php -m | grep -qi '^Zend OPcache$' || docker-php-ext-install opcache) \
    && a2enmod rewrite headers expires deflate ssl \
    && sed -ri 's/^ServerTokens .*/ServerTokens Prod/; s/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf \
    && rm -rf /var/lib/apt/lists/*

COPY deploy/production/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/production/php-performance.ini /usr/local/etc/php/conf.d/99-telar-performance.ini

RUN if php -r 'exit(PHP_VERSION_ID >= 80000 ? 0 : 1);'; then \
        printf '%s\n' \
          'disable_functions=utf8_encode,utf8_decode' \
          'auto_prepend_file=/var/www/html/php_compat/encoding.php' \
          > /usr/local/etc/php/conf.d/98-telar-php8-compat.ini; \
    fi

RUN printf '%s\n' \
      'auto_prepend_file=/var/www/html/php_system/error_monitor_bootstrap.php' \
      'display_errors=Off' \
      'display_startup_errors=Off' \
      'log_errors=On' \
      'error_reporting=E_ALL' \
      > /usr/local/etc/php/conf.d/zz-telar-error-monitor.ini

WORKDIR /var/www/html
