FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

ENV APACHE_DOCUMENT_ROOT=/var/www/html
WORKDIR /var/www/html

COPY . /var/www/html/

# Create writable upload/image directories even when Git does not include
# empty folders or the deployment repository omits these assets.
RUN mkdir -p /var/www/html/images/users /var/www/html/uploads/instructors \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R u+rwX,g+rwX /var/www/html/images /var/www/html/uploads

EXPOSE 80

