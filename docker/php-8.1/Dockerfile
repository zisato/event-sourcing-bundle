FROM php:8.1.0-cli AS php-cli

# Install
RUN apt update && apt install -y \
    curl \
    default-mysql-client \
    git \
    libcurl4-openssl-dev \
    libssl-dev \
    pkg-config \
    unzip \
    vim \
    wget

# Install composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# ----------


FROM php-cli AS php-cli-xdebug

# Install xdebug by pecl
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
# ----------


FROM php-cli AS php-cli-pcov
# Install pcov by pecl
RUN pecl install pcov \
    && docker-php-ext-enable pcov