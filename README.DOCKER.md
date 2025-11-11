# 使用 Docker 运行 ACG 发卡系统

本指南将帮助您使用 Docker 快速部署 ACG 发卡系统。

## 环境要求

- Docker 18.06.0+
- Docker Compose 1.22.0+

## 快速开始

1. 克隆或下载项目代码：
   ```bash
   git clone https://github.com/lizhipay/acg-faka.git
   cd acg-faka
   ```

2. 构建并启动服务：
   ```bash
   docker-compose up -d
   ```

3. 等待服务启动完成，然后访问：
   - 前台: http://localhost:8088
   - 后台: http://localhost:8088/admin

4. 首次访问会自动跳转到安装页面，按照提示完成安装。

## 配置说明

### 环境变量

项目根目录下的 `.env` 文件包含以下配置：

- `APP_PORT`: Web 服务端口，默认 8080
- `DB_PORT`: MySQL 服务端口，默认 3306
- `DB_DATABASE`: 数据库名称，默认 acg_faka
- `DB_USERNAME`: 数据库用户名，默认 acg_user
- `DB_PASSWORD`: 数据库密码，默认 acg_password
- `DB_ROOT_PASSWORD`: MySQL root 用户密码，默认 root_password

### 目录结构

```
acg-faka/
├── docker/                 # Docker 配置文件
│   ├── mysql/              # MySQL 配置
│   │   └── init.sql        # 数据库初始化脚本
│   ├── nginx/              # Nginx 配置
│   │   ├── Dockerfile      # Nginx 镜像构建文件
│   │   └── nginx.conf      # Nginx 配置文件
│   └── php/                # PHP 配置
│       ├── Dockerfile      # PHP 镜像构建文件
│       └── php.ini         # PHP 配置文件
├── docker-compose.yml      # Docker Compose 配置文件
└── .env                    # 环境变量配置文件
```

## 管理命令

### 启动服务
```bash
docker-compose up -d
```

### 停止服务
```bash
docker-compose down
```

### 查看服务状态
```bash
docker-compose ps
```

### 查看日志
```bash
# 查看所有服务日志
docker-compose logs

# 查看特定服务日志
docker-compose logs nginx
docker-compose logs php
docker-compose logs mysql
```

### 进入容器
```bash
# 进入 PHP 容器
docker-compose exec php bash

# 进入 MySQL 容器
docker-compose exec mysql bash
```

## 数据持久化

MySQL 数据存储在 Docker 卷中，即使容器被删除，数据也会保留。要完全清除数据，需要删除卷：

```bash
docker-compose down -v
```

## 常见问题

### 1. 端口冲突

如果默认端口被占用，可以修改 `.env` 文件中的 `APP_PORT` 和 `DB_PORT` 变量。

### 2. 权限问题

如果遇到权限问题，请确保项目文件的所有者是正确的：
```bash
# 在 Linux/macOS 上
sudo chown -R $USER:$USER .
```

### 3. 安装页面无法访问

如果安装页面无法访问，请检查：
1. 所有服务是否正常启动：`docker-compose ps`
2. 查看日志是否有错误：`docker-compose logs`

## 技术栈

- PHP 8.1
- Nginx
- MySQL 8.0
- Docker & Docker Compose