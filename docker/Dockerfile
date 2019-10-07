################################################################################
#
#  This file is part of SplashSync Project.
# 
#  Copyright (C) Splash Sync <www.splashsync.com>
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
# 
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

FROM php:7.3-alpine

RUN apk add tini wget git curl mysql mysql-client libxml2-dev
RUN apk add php7-pdo_mysql php7-soap php7-zip php7-curl

RUN docker-php-ext-install pdo_mysql soap

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 


RUN git clone https://github.com/SplashSync/Toolkit.git --depth=1 /app

COPY .env /app/.env
COPY config/splash.yml /app/config/packages/splash.yml

RUN php -m

RUN cd /app && php -d memory_limit=-1 /usr/bin/composer update

RUN php /app/bin/console assets:install --no-debug

RUN php /app/bin/console doctrine:schema:update --force
RUN php /app/bin/console debug:config splash
RUN php /app/bin/console cache:clear
RUN php /app/bin/console fos:user:create admin contact@exemple.com admin --super-admin

EXPOSE 80

WORKDIR /app

CMD ["php", "/app/bin/console", "server:run", "172.169.0.10:80"]
