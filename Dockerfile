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

################################################################################
# Image Arguments
# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.0
ARG APP_ENV=dev

################################################################################
# "php" stage
FROM php:${PHP_VERSION}-cli-alpine AS splash-toolkit-alpine

################################################################################
# Install Dependencies
ARG APCU_VERSION=5.1.21
RUN apk add --no-cache acl file gettext git bash nano sqlite
RUN set -eux; \
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev sqlite-dev zlib-dev libxml2-dev; \
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) intl pdo_sqlite zip soap; \
	pecl install apcu-${APCU_VERSION}; \
	pecl clear-cache; \
	docker-php-ext-enable apcu opcache; \
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	apk del .build-deps

#################################################################
# Configure Php
ADD docker/alpine/conf.d /usr/local/etc/php/conf.d/

################################################################################
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1
WORKDIR /app

################################################################################
# Prevent the reinstallation of vendors at every changes in the source code
COPY .env.dist ./.env
COPY composer.json composer.lock phpunit.xml.dist ./
RUN set -eux; \
	composer update --prefer-dist --no-dev --no-scripts --no-progress --no-plugins; \
	composer clear-cache;

################################################################################
# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
# Remove PHP CGI
RUN rm /usr/local/bin/php-cgi

################################################################################
# Copy only specifically what we need
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY translations translations/
COPY tests tests/

################################################################################
# Configure Project before Run
RUN set -eux; \
	mkdir -p connectors var/cache var/log; \
	chmod +x bin/console; sync; \
	composer run-script --no-dev post-install-cmd; \
    bin/console doctrine:database:create --no-interaction; \
    bin/console doctrine:schema:update --force; \
    bin/console fos:user:create toolkit@splashsync.com toolkit@splashsync.com toolkit --super-admin;
VOLUME /app/var

################################################################################
# Setup Dockerr Entrypoint
COPY docker/alpine/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]

################################################################################
# Default Enveironment Variables
ENV SHOPIFY_API_SECRET=ThisTokenIsNotUsed

################################################################################
# Start Symfony WebServer
EXPOSE 80
CMD ["/usr/local/bin/symfony", "serve", "--no-tls", "--port=80"]