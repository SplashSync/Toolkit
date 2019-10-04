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

RUN apk add tini wget curl git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 


RUN git clone https://github.com/SplashSync/Toolkit.git --depth=1 /app

COPY .env /app/.env
COPY config/splash.yml /app/config/splash.yml

RUN cd /app
RUN ls -l

RUN cd /app && php -d memory_limit=-1 /usr/bin/composer update


RUN php /app/bin/console assets:install --no-debug

EXPOSE 8000

WORKDIR /app

#CMD ["/sbin/tini", "--"]

CMD ["php", "/app/bin/console", "server:run", "172.169.0.10:80"]
