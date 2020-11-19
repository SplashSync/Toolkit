################################################################################
#
# * This file is part of SplashSync Project.
# *
# * Copyright (C) Splash Sync <www.splashsync.com>
# *
# * This program is distributed in the hope that it will be useful,
# * but WITHOUT ANY WARRANTY; without even the implied warranty of
# * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# *
# * For the full copyright and license information, please view the LICENSE
# * file that was distributed with this source code.
# *
# * @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

FROM php:7.3-apache-stretch

################################################################################
# Setup Apache
RUN a2enmod rewrite
#RUN service apache2 restart
################################################################################
# Configure Apache Root Path
ENV APACHE_DOCUMENT_ROOT /app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
################################################################################
# Install Required Libs
RUN apt-get update --fix-missing
RUN apt-get install -y git nano curl libxml2-dev libzip-dev zlib1g-dev unzip
################################################################################
# Install Required PHP Extensions
RUN yes '' | pecl install -f apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-install pdo_mysql soap zip
#################################################################
# Configure Php
ADD conf.d /etc/php/7.3/mods-available
ADD conf.d /usr/local/etc/php/conf.d/
################################################################################
# Install Composer
ENV COMPOSER_MEMORY_LIMIT: -1
ENV COMPOSER_HOME: '/home/docker/.composer'
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 
################################################################################
# Clone Splash Toolkit
RUN git clone https://github.com/SplashSync/Toolkit.git --depth=1 /app
################################################################################
# Configure Splash Toolkit
COPY .env /app/.env
COPY config/splash.yml /app/splash.yml
################################################################################
# Run Composer
RUN rm -Rf /app/var/cache/*
#RUN cd /app && php -d memory_limit=-1 /usr/bin/composer install --prefer-dist --no-interaction
RUN cd /app && php -d memory_limit=-1 /usr/bin/composer update --prefer-dist --no-interaction
################################################################################
# Install Splash Toolkit
RUN rm -Rf /app/var/cache/*
RUN php /app/bin/console doctrine:schema:update --force
RUN php /app/bin/console debug:config splash
RUN php /app/bin/console cache:clear
RUN php /app/bin/console fos:user:create admin contact@exemple.com admin --super-admin
RUN chmod 777 /app/var/data.db
################################################################################
# Setup Entrypoint
COPY entrypoint.sh /
RUN set -ex; \
    chmod 755 /entrypoint.sh
#################################################################################
## Show Composer Status
#RUN cd /app && php /usr/bin/composer info

EXPOSE 80
WORKDIR /app

ENTRYPOINT ["sh", "/entrypoint.sh"]
CMD ["apache2-foreground"]