FROM php:8.1-cli

WORKDIR /app
COPY . .

EXPOSE 3000

CMD ["php", "-S", "0.0.0.0:3000", "-t", "."]
