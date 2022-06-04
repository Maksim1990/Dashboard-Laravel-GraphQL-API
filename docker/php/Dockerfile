FROM php:8.0.9-fpm-buster

ARG USER_ID
ARG GROUP_ID

RUN apt-get update && apt-get install -y wget \
  curl \
  git \
  grep \
  libmcrypt-dev \
  libxml2-dev \
  libtool \
  make \
  autoconf \
  g++ \
  supervisor \
  zip \
  nano \
  vim \
  redis \
  libzip-dev \
  libpng-dev libpng-dev \
  libcurl4-openssl-dev pkg-config libssl-dev

RUN pecl channel-update pecl.php.net

RUN pecl install redis mongodb
RUN docker-php-ext-enable redis mongodb
RUN docker-php-ext-configure zip
RUN apt-get install -y libjpeg-dev libfreetype6-dev
RUN docker-php-ext-install -j$(nproc) bcmath pcntl exif sockets gd

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

### Install Swoole (v4.7.2) extension ###
RUN cd /tmp && git clone https://github.com/openswoole/swoole-src.git && \
    cd swoole-src && \
    git checkout v4.7.2 && \
    phpize  && \
    ./configure --enable-openssl --enable-swoole-curl --enable-http2 --enable-mysqlnd && \
    make && make install
RUN touch /usr/local/etc/php/conf.d/openswoole.ini && \
    echo 'extension=openswoole.so' > /usr/local/etc/php/conf.d/zzz_openswoole.ini
RUN wget -O /usr/local/bin/dumb-init https://github.com/Yelp/dumb-init/releases/download/v1.2.2/dumb-init_1.2.2_amd64
RUN chmod +x /usr/local/bin/dumb-init
RUN apt-get autoremove -y && rm -rf /var/lib/apt/lists/*
##################

### In case error cURL error 77: error setting certificate verify locations
RUN mkdir -p /etc/ssl/curl
RUN wget --no-check-certificate https://curl.haxx.se/ca/cacert.pem -O /etc/ssl/curl/cacert.pem
#######################

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#### Install NodeJS and NPM ###########
RUN curl --silent --location https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install --yes nodejs
#######################################

#RUN usermod -u ${USER_ID} www-data && groupmod -g ${GROUP_ID} www-data

RUN addgroup -gid ${GROUP_ID} app \
  && useradd -u ${USER_ID} -g app -s /bin/bash -d /var/www app \
  && chown -R app:app /var/www/ \
  && echo "app ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

COPY --chown=app:app . /var/www

# Set working directory PHP
WORKDIR /var/www

RUN npm install

# Enable Xdebug
RUN mkdir -p /var/www/logs/xdebug && touch /var/www/logs/xdebug/xdebug.log
ADD ./docker/php/php.ini /usr/local/etc/php/php.ini
RUN pecl install xdebug-3.0.1 && docker-php-ext-enable xdebug

RUN chmod 755 /var/www

#USER app

########################
## Configure Supervisor
########################
## Create Supervisor group
#RUN addgroup supervisor
## Add user to Supervisor group
#RUN apk add shadow openrc
#RUN usermod -a -G supervisor root
#COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
#COPY ./docker/supervisor/* /etc/supervisor/conf.d/
## Set Supervisor environment
#RUN mkdir -p /var/log/supervisor/
##RUN service supervisor restart
#RUN touch /var/run/supervisor.sock && chmod 777 /var/run/supervisor.sock
#RUN touch /run/supervisord.sock && chmod 777 /run/supervisord.sock
#### /etc/init.d/supervisord restart

### In order to Start Supervisor run:
#RUN ./services/start_supervisor.sh
#######################


