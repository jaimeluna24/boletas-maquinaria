# ---------------------------------------------------
# 1. Fase de Compilación de Assets (Frontend)
# ---------------------------------------------------
FROM node:24.14.0-alpine AS frontend
WORKDIR /app

# Copiar manifiestos e instalar dependencias
COPY package.json package-lock.json ./
RUN npm ci

# Copiar código fuente y compilar assets
COPY . .
RUN npm run build

# ---------------------------------------------------
# 2. Fase de Aplicación (Backend PHP 8.4)
# ---------------------------------------------------
FROM php:8.4-cli-alpine AS app

# Instalar dependencias del sistema y extensiones necesarias para Laravel
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

# Copiar instalador de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instalar dependencias de PHP sin paquetes de desarrollo
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el proyecto y los assets compilados en la etapa de Node
COPY . .
COPY --from=frontend /app/public/build ./public/build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]