FROM php:8.3-fpm

# Argumentos para UID y GID
ARG UID=1000
ARG GID=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libpq-dev libzip-dev libmcrypt-dev libssl-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

RUN apt-get update && apt-get install -y default-mysql-client

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario con el mismo UID/GID que el host
RUN groupadd -g ${GID} appuser && \
    useradd -u ${UID} -g appuser -m -s /bin/bash appuser

# Crear directorio de trabajo
WORKDIR /var/www

# Copiar archivos
COPY --chown=appuser:appuser . /var/www

# Permisos
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]