# ---------------------------------------------------
# 1. Fase de Compilación de Assets (Frontend)
# ---------------------------------------------------
FROM node:24.14.0-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---------------------------------------------------
# 2. Fase de Aplicación (Backend PHP 8.4)
# ---------------------------------------------------
FROM php:8.4-cli-alpine AS app

# Instalar dependencias del sistema y extensiones necesarias
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instalar dependencias de PHP sin ejecutar scripts que dependan de artisan
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copiar el código fuente completo del proyecto
COPY . .

# Copiar los assets compilados en la etapa de Node
COPY --from=frontend /app/public/build ./public/build

# Ejecutar los scripts pendientes de Composer (como package:discover) ahora que artisan está disponible
RUN composer dump-autoload --optimize --no-dev

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]