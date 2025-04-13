FROM php:8.3-fpm

RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install pdo pdo_mysql opcache

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

RUN apt-get update -y && apt-get install -y libpng-dev

RUN apt-get update && \
    apt-get install zlib1g-dev tree mariadb-client -y

RUN docker-php-ext-install gd

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer

COPY fpm-pool.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY crontab/pms /etc/cron.d/pms

RUN chown root:root /etc/cron.d/pms && chmod 644 /etc/cron.d/pms

RUN pecl install apcu

RUN pear config-set php_ini /application/docker/php-fpm/php.ini

RUN curl -sLO https://github.com/gordalina/cachetool/releases/latest/download/cachetool.phar --create-dirs -O --output-dir /usr/bin && chmod +x /usr/bin/cachetool.phar

CMD ["php-fpm"]

EXPOSE 9000

RUN apt-get install -y nano less cron;
