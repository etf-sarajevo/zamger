FROM phpstorm/php-71-apache-xdebug

RUN apt-get update          \
    && apt-get install -y   \
        libxml2-dev         \
        libgd-dev           \
        libpng-dev          \
        libfreetype6-dev    \
        libjpeg62-turbo-dev \
        libldap-dev         \
        zip unzip git       \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/  \
    && docker-php-ext-install gd ldap                                                                 \
    && apt-get clean all                                                                            \
    && rm -rvf /var/lib/apt/lists/*                                                                 \
    && a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/bin

ENV PATH /root/.composer/vendor/bin:$PATH

RUN mkdir /home/zamger && chown www-data /home/zamger

WORKDIR /var/www/html