FROM php:8.3-apache
USER root

# Enable .htaccess (mod_rewrite)
RUN apt-get update && a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y \
        apt-utils \
        vim \
        git \
        unzip \
        zip \
        zlib1g-dev \
        libbz2-dev \
        mariadb-client \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install zip bz2 \
    && docker-php-ext-install opcache \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install exif \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath

# PHP ACPU
RUN pecl install apcu && \
    docker-php-ext-enable apcu

# PHP INTL
RUN apt-get update \
    && apt-get install -y zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl

# PHP XSL
RUN apt-get update && apt-get install -y libxslt-dev
RUN docker-php-ext-install xsl

# PHP IMAP
RUN apt-get update && apt-get install -y libc-client-dev libkrb5-dev
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# PHP XDEBUG
#RUN pecl install xdebug
#RUN docker-php-ext-enable xdebug

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# PHP INI
COPY php-ini-overrides.ini /usr/local/etc/php/conf.d/99-overrides.ini

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html