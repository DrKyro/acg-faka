# 使用官方 PHP 8.1 开发镜像
FROM mcr.microsoft.com/devcontainers/php:1-8.1-bullseye

# 设置环境变量
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Shanghai
ENV COMPOSER_ALLOW_XDEBUG=1

# 更新包列表并安装系统依赖
RUN apt-get update && apt-get install -y \
    # 基础工具
    git \
    curl \
    wget \
    vim \
    nano \
    unzip \
    zip \
    build-essential \
    \
    # PHP 扩展和库
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libmcrypt-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libmagickwand-dev \
    \
    # 数据库相关
    mysql-client \
    \
    # 图像处理
    imagemagick \
    \
    # 其他开发工具
    nodejs \
    npm \
    yarn \
    \
    # 清理缓存
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 配置 PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# 安装 PHP 扩展 (分步安装避免错误)
RUN docker-php-ext-install \
    pdo_mysql \
    && docker-php-ext-install \
    gd \
    && docker-php-ext-install \
    zip \
    && docker-php-ext-install \
    intl \
    && docker-php-ext-install \
    bcmath \
    && docker-php-ext-install \
    opcache \
    && docker-php-ext-install \
    mbstring \
    && docker-php-ext-install \
    mysqli \
    && docker-php-ext-install \
    exif

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 升级 Composer 到最新版本
RUN composer self-update

# 创建工作目录
WORKDIR /workspace

# 设置 PHP 配置
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "xdebug.mode=debug,develop" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/custom.ini

# 创建并设置用户权限
RUN groupadd -r vscode && useradd -r -g vscode vscode \
    && chown -R vscode:vscode /workspace \
    && usermod -aG sudo vscode \
    && echo "vscode ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# 切换到 vscode 用户
USER vscode

# 设置 Composer 全局配置
RUN composer config --global process-timeout 2000 \
    && composer config --global repos.packagist composer https://mirrors.aliyun.com/composer/

# 创建开发配置目录
RUN mkdir -p ~/.vscode-server/extensions \
    && mkdir -p ~/.config/composer

# 设置默认的编辑器
ENV EDITOR=vim
ENV VISUAL=vim

# 暴露端口
EXPOSE 80 443 9000 8000

# 设置工作目录
WORKDIR /workspace

# 默认启动命令
CMD ["/bin/bash"]