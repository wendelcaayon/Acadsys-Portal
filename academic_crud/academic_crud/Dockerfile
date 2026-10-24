FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

ENV APACHE_DOCUMENT_ROOT=/var/www/html
WORKDIR /var/www/html

COPY . /var/www/html/

# Keep uploaded images writable in the container. Note: Render's container
# filesystem is ephemeral unless a persistent disk is configured.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R u+rwX,g+rwX /var/www/html/images /var/www/html/uploads

EXPOSE 80
