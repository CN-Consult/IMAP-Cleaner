# build environment
FROM phpearth/php:7.3-cli as build

RUN apk add --no-cache php7.3-imap composer
COPY . /app/
WORKDIR /app/
RUN composer install --optimize-autoloader && rm composer.json composer.lock


# production environment
FROM phpearth/php:7.3-cli
RUN apk add --no-cache php7.3-imap bash
COPY --from=build /app/ /app/
ENTRYPOINT ["/app/imap-cleaner.php"]
CMD []

