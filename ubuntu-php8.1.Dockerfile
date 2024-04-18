FROM ubuntu:23.04

ENV TZ=Europe/Moscow

# обновление и установка пакетов
RUN apt-get update && \
	apt-get install -y locales && \
	locale-gen en_US.UTF-8  && \
	locale-gen ru_RU.UTF-8  && \
	echo $TZ > /etc/timezone && \
    apt-get install -y tzdata && \
    rm /etc/localtime && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata && \
	apt-get install -y \
	software-properties-common && \
	add-apt-repository ppa:ondrej/php && \
	apt-get update && \
 	apt-get install -y  \
    php8.1-cli php8.1-common php8.1-fpm php8.1-dev \
        php8.1-bcmath \
        php8.1-bz2 \
        php8.1-curl \
        php8.1-gd \
        php8.1-mbstring\
        php8.1-mcrypt \
        php8.1-soap \
        php8.1-xml \
     	php8.1-redis \
     	php8.1-xdebug \
        redis-tools \
	curl \
	wget \
	mc \
	bc \
	socket \
    && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && php -v
RUN     update-alternatives --set php /usr/bin/php8.1 \
	&& pecl channel-update pecl.php.net \
	&& php -v \
	&& pecl install mongodb \
	&& echo "extension=mongodb.so" > /etc/php/8.1/mods-available/mongodb.ini && phpenmod mongodb \
	&& apt-get clean && apt-get autoremove && apt-get autoclean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

