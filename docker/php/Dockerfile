FROM php:8.1-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip pdo_mysql mysqli \
    && docker-php-ext-enable opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html

# 复制入口脚本
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint.sh

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod +x /usr/local/bin/app-entrypoint.sh
    
# Create required runtime directories
RUN mkdir -p /var/www/html/runtime/waf/PACKET /var/www/html/runtime/log && \
    chown -R www-data:www-data /var/www/html/runtime && \
    chmod -R 755 /var/www/html/runtime

# 安装 PHP 依赖
RUN composer install --optimize-autoloader --no-dev

# 暴露端口
EXPOSE 9000

# 启动 PHP-FPM
ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
CMD ["php-fpm"]
