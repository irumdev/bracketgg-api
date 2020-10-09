ARG phpVersion=7.4
FROM php:${phpVersion}-fpm
LABEL maintainer="dhtmdgkr123 <osh12201@gmail.com>"
ARG uploadMaxSize=5
USER root
RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
            curl \
            libmemcached-dev \
            libz-dev \
            libpq-dev \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libmcrypt-dev \
            libonig-dev; \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install pdo_mysql; \
    # Install the PHP pdo_mysql extention
    # Install the PHP gd library
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-freetype; \
    docker-php-ext-install gd; \
    set -xe; \
    pecl channel-update pecl.php.net && \
    apt-get update; \
    apt-get install -yqq \
      apt-utils libzip-dev zip unzip && \
    #####################
    # start install zip
    #####################
    #####################
    docker-php-ext-configure zip && \
    docker-php-ext-install zip && \
    php -m | grep -q 'zip'; \
    #####################
    # end install zip
    #####################
    #####################
    # start install redis
    #####################
    pecl install -o -f redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis && \
    #####################
    # end install redis
    #####################
    #####################
    # start install bcmath opcache
    docker-php-ext-install bcmath opcache && \
    #####################
    # end install bcmath opcache
    #####################
    #####################
    # start install intl
    #####################
    apt-get install -y zlib1g-dev libicu-dev g++ && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    #####################
    # end install intl
    #####################
    #####################
    # start install image optimizer, image magick dependency
    #####################
    apt-get install -y jpegoptim optipng pngquant gifsicle && \
    #####################
    # end install image optimizer
    #####################
    # start install image magick
    #####################
    #####################
    #####################
    apt-get install -y libmagickwand-dev imagemagick && \
    pecl install imagick && \
    docker-php-ext-enable imagick && \
    #####################
    # install xedebug
    #####################
#     pecl install xdebug && docker-php-ext-enable xdebug && \
    #####################
    #####################
    #####################
    # end install imagemagick
    #####################
    apt-get clean && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    rm /var/log/lastlog /var/log/faillog; \
    #####################
    # clear dev package
    #####################
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#     apt-get remove libhashkit-dev \
#                    libjpeg62-turbo-dev \
#                    libsasl2-dev \
#                    libfreetype6-dev \
#                    libhashkit-dev \
#                    libjpeg-dev \
#                    libjpeg62-turbo-dev \
#                    libmcrypt-dev \
#                    libmemcached-dev \
#                    libonig-dev \
#                    libpng-dev \
#                    libpq-dev \
#                    libsasl2-dev \
#                    libssl-dev \
#                    zlib1g-dev \
#                    autotools-dev \
#                    libblkid-dev \
#                    libbz2-dev \
#                    libcairo2-dev \
#                    libdjvulibre-dev \
#                    libexif-dev \
#                    libexpat1-dev \
#                    libffi-dev \
#                    libfontconfig1-dev \
#                    libgdk-pixbuf2.0-dev \
#                    libglib2.0-dev \
#                    libglib2.0-dev-bin \
#                    libice-dev \
#                    libilmbase-dev \
#                    libjbig-dev \
#                    liblcms2-dev \
#                    liblqr-1-0-dev \
#                    libltdl-dev \
#                    liblzma-dev \
#                    libmagickcore-6.q16-dev \
#                    libmagickwand-6.q16-dev \
#                    libmount-dev \
#                    libopenexr-dev \
#                    libopenjp2-7-dev \
#                    libpcre3-dev \
#                    libpixman-1-dev \
#                    libpthread-stubs0-dev \
#                    librsvg2-dev \
#                    libselinux1-dev \
#                    libsepol1-dev \
#                    libsm-dev \
#                    libtiff-dev \
#                    libwmf-dev \
#                    libx11-dev \
#                    libxau-dev \
#                    libxcb-render0-dev \
#                    libxcb-shm0-dev \
#                    libxcb1-dev \
#                    libxdmcp-dev \
#                    libxext-dev \
#                    libxml2-dev \
#                    libxrender-dev \
#                    libxt-dev \
#                    uuid-dev \
#                    x11proto-core-dev \
#                    x11proto-dev \
#                    x11proto-xext-dev \
#                    xtrans-dev \
#                    libfftw3-dev \
#                    autotools-dev \
#                    libblkid-dev \
#                    libbz2-dev \
#                    libcairo2-dev \
#                    libdjvulibre-dev \
#                    libexif-dev \
#                    libexpat1-dev \
#                    libffi-dev \
#                    libfontconfig1-dev \
#                    libgdk-pixbuf2.0-dev \
#                    libglib2.0-dev \
#                    libglib2.0-dev-bin \
#                    libice-dev \
#                    libilmbase-dev \
#                    libjbig-dev \
#                    liblcms2-dev \
#                    liblqr-1-0-dev \
#                    libltdl-dev \
#                    liblzma-dev \
#                    libmagickcore-6.q16-dev \
#                    libmagickwand-6.q16-dev \
#                    libmagickwand-dev \
#                    libmount-dev \
#                    libopenexr-dev \
#                    libopenjp2-7-dev \
#                    libpcre3-dev \
#                    libpixman-1-dev \
#                    libpthread-stubs0-dev \
#                    librsvg2-dev \
#                    libselinux1-dev \
#                    libsepol1-dev \
#                    libsm-dev \
#                    libtiff-dev \
#                    libwmf-dev \
#                    libx11-dev \
#                    libxau-dev \
#                    libxcb-render0-dev \
#                    libxcb-shm0-dev \
#                    libxcb1-dev \
#                    libxdmcp-dev \
#                    libxext-dev \
#                    libxml2-dev \
#                    libxrender-dev \
#                    libxt-dev \
#                    uuid-dev \
#                    x11proto-core-dev \
#                    x11proto-dev \
#                    x11proto-xext-dev \
#                    xtrans-dev \
#                    autotools-dev \
#                    libblkid-dev \
#                    libbz2-dev \
#                    libcairo2-dev \
#                    libdjvulibre-dev \
#                    libexif-dev \
#                    libexpat1-dev \
#                    libffi-dev \
#                    libfontconfig1-dev \
#                    libgdk-pixbuf2.0-dev \
#                    libglib2.0-dev \
#                    libglib2.0-dev-bin \
#                    libice-dev \
#                    libilmbase-dev \
#                    libjbig-dev \
#                    liblcms2-dev \
#                    liblqr-1-0-dev \
#                    libltdl-dev \
#                    liblzma-dev \
#                    libmagickcore-6.q16-dev \
#                    libmagickwand-6.q16-dev \
#                    libmount-dev \
#                    libopenexr-dev \
#                    libopenjp2-7-dev \
#                    libpcre3-dev \
#                    libpixman-1-dev \
#                    libpthread-stubs0-dev \
#                    librsvg2-dev \
#                    libselinux1-dev \
#                    libsepol1-dev \
#                    libsm-dev \
#                    libtiff-dev \
#                    libwmf-dev \
#                    libx11-dev \
#                    libxau-dev \
#                    libxcb-render0-dev \
#                    libxcb-shm0-dev \
#                    libxcb1-dev \
#                    libxdmcp-dev \
#                    libxext-dev \
#                    libxml2-dev \
#                    libxrender-dev \
#                    libxt-dev \
#                    uuid-dev \
#                    x11proto-core-dev \
#                    x11proto-dev \
#                    x11proto-xext-dev \
#                    xtrans-dev \
#                    autotools-dev \
#                    libblkid-dev \
#                    libbz2-dev \
#                    libcairo2-dev \
#                    libdjvulibre-dev \
#                    libexif-dev \
#                    libexpat1-dev \
#                    libffi-dev \
#                    libfontconfig1-dev \
#                    libgdk-pixbuf2.0-dev \
#                    libglib2.0-dev \
#                    libglib2.0-dev-bin \
#                    libice-dev \
#                    libilmbase-dev \
#                    libjbig-dev \
#                    liblcms2-dev \
#                    liblqr-1-0-dev \
#                    libltdl-dev \
#                    liblzma-dev \
#                    libmagickcore-6.q16-dev \
#                    libmagickwand-6.q16-dev \
#                    libmagickwand-dev \
#                    libmount-dev \
#                    libopenexr-dev \
#                    libopenjp2-7-dev \
#                    libpcre3-dev \
#                    libpixman-1-dev \
#                    libpthread-stubs0-dev \
#                    librsvg2-dev \
#                    libselinux1-dev \
#                    libsepol1-dev \
#                    libsm-dev \
#                    libtiff-dev \
#                    libwmf-dev \
#                    libx11-dev \
#                    libxau-dev \
#                    libxcb-render0-dev \
#                    libxcb-shm0-dev \
#                    libxcb1-dev \
#                    libxdmcp-dev \
#                    libxext-dev \
#                    libxml2-dev \
#                    libxrender-dev \
#                    libxt-dev \
#                    uuid-dev \
#                    x11proto-core-dev \
#                    x11proto-dev \
#                    x11proto-xext-dev \
#                    xtrans-dev
WORKDIR /var/www
COPY . /var/www
RUN echo ${uploadMaxSize} && echo '' >> /usr/local/etc/php/conf.d/php-uploadFile.ini && \
    echo '[PHP]' >> /usr/local/etc/php/conf.d/php-uploadFile.ini && \
    echo "post_max_size = ${uploadMaxSize}M" >> /usr/local/etc/php/conf.d/php-uploadFile.ini && \
    echo "upload_max_filesize = ${uploadMaxSize}M" >> /usr/local/etc/php/conf.d/php-uploadFile.ini && \
    /usr/local/bin/composer install --optimize-autoloader --no-dev && \
    cd /var/www && \
    chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage

CMD ["cd /var/www && php artisan route:cache && php artisan config:cache && php-fpm"]
EXPOSE 9000
