FROM phpearth/php:7.3-cli

RUN apk add --no-cache composer php7.3-imap bash

ENTRYPOINT sleep 6000
