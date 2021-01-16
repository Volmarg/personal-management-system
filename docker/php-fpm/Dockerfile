FROM php:7.4-fpm

RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

RUN apt-get update -y && apt-get install -y libpng-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev 

RUN docker-php-ext-install gd

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/php.ini

CMD ["php-fpm"]

EXPOSE 9000
