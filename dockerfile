FROM php:8.1-cli

WORKDIR /app
COPY . .

RUN echo "display_errors = On\nerror_reporting = E_ALL" > /usr/local/etc/php/conf.d/error.ini

EXPOSE $PORT

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-3000} -t . test.php"]
