FROM php:7.0-cli
RUN apt-get update && apt-get install -y zlib1g-dev libzmq-dev wget git \
    && pecl install zmq-beta \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zmq
ADD scripts/install_composer.sh /install_composer.sh
RUN /install_composer.sh
WORKDIR /bushtaxi
RUN useradd bushtaxi && mkdir -p /home/bushtaxi && chown bushtaxi:bushtaxi /home/bushtaxi
USER bushtaxi
CMD composer update && composer exec bushtaxi
