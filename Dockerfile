FROM phpearth/php:7.3-cli

RUN apk add --no-cache php7.3-imap bash
COPY . /app/

ENTRYPOINT ["/app/imap-cleaner.php"]
CMD []