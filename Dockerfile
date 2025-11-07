# 使用官方 PHP 8.1 镜像 (fpm 版本)
FROM php:8.1-fpm

# 设置时区
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展 (分步安装避免错误)
RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install gd

RUN docker-php-ext-install zip

RUN docker-php-ext-install intl

RUN docker-php-ext-install bcmath

RUN docker-php-ext-install opcache

RUN docker-php-ext-install mbstring

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制 composer 文件
COPY composer.json composer.lock ./

# 安装 PHP 依赖
RUN composer install --no-dev --optimize-autoloader

# 复制项目文件
COPY . .

# 创建必要目录并设置权限
RUN mkdir -p /var/www/html/cache /var/www/html/upload /var/www/html/logs && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/cache /var/www/html/upload /var/www/html/logs

# 暴露端口
EXPOSE 9000

# 启动命令
CMD ["php-fpm"]