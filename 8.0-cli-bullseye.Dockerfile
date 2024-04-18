FROM php:8.0-cli-bullseye

ENV TZ=Europe/Moscow

# обновление и установка пакетов
RUN apt-get update && \
	apt-get install -y apt-utils locales && \
	locale-gen en_US.UTF-8  && \
	locale-gen ru_RU.UTF-8  && \
	echo $TZ > /etc/timezone && \
	apt-get install -y tzdata && \
	rm /etc/localtime && \
	ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
	dpkg-reconfigure -f noninteractive tzdata && \
	apt-get install -y \
	curl wget mc bc socket cron supervisor \
	&& curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && php -v

#ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
#RUN install-php-extensions mongodb
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o - | sh -s mongodb

RUN rm -rf /var/lib/apt/lists/* && apt-get autoclean && apt-get autoremove

# добавить код из репозитория
#ADD ./.release /var/www/html
#RUN composer install --working-dir=/var/www/html

# web ui для управления запущенными процессами
#EXPOSE 9000
#
#CMD ["php-fpm"]