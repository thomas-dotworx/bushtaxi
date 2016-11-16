FROM php:7.0-apache
RUN apt-get update && apt-get install -y zlib1g-dev libzmq-dev \
    && pecl install zmq-beta \
    && docker-php-ext-enable zmq \
    && docker-php-ext-install zip \
    && a2enmod rewrite
ADD scripts/install_composer.sh /install_composer.sh
RUN /install_composer.sh
