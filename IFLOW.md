# 异次元店铺系统 (ACG FAKA) 项目说明

<p align="center">
  <a href="https://faka.wiki/">
    <img src="https://raw.githubusercontent.com/lizhipay/acg-faka/refs/heads/main/favicon.ico" width="120" height="120" style="border-radius: 20px;" alt="异次元店铺系统">
  </a>
</p>

<br>
<p align="center">
<span>
<img src="https://faka.wiki/svg/php.svg" alt="php8.0,8.1">
</span>
<span>
<img src="https://faka.wiki/svg/mysql-version.svg" alt="mysql5.6+">
</span>
<span><img src="https://faka.wiki/svg/license.svg" alt="license"></span>
</p>

## 法律声明
> 本商城程序基于 MIT 协议开源，并且完全免费。该程序的初衷是为开发者提供学习和研究的机会。未取得合法资质，严禁将本程序用于任何商业用途，尤其是禁止利用本程序搭建平台进行商品销售。
>
> 用户在使用或学习本程序时，必须严格遵守法律法规。我们提倡依法行事，尊重法律，坚守法律，避免对社会产生不良影响。
>
> 使用本程序即表示您已充分理解并同意本法律声明的所有内容。

## 快速体验
- 后台演示：[http://162.14.111.118:91/admin](http://162.14.111.118:91/admin)  账号：demo@demo.com 密码：123456
- 前台演示：[http://162.14.111.118:91](http://162.14.111.118:91) 账号：为了明天美好而战斗 密码：123456
- 文档地址：[https://faka.wiki](https://faka.wiki)

## 项目概述

**异次元店铺系统** 是一款基于 PHP 8.0+ 开发的原生虚拟发卡系统，采用现代化的 MVC 架构设计。该系统具有强大的插件扩展能力，支持多种支付渠道，提供完整的电商功能。

### 核心特性
- 💳 **支付系统** - 支持全网任意平台、任意支付渠道
- 🔄 **云更新** - 无缝在线升级系统
- 🛍️ **商品销售** - 完整的电商功能（配图、会员价、API对接等）
- 🏢 **分站系统** - 支持分站独立运行或卖主站商品
- 👥 **会员系统** - 会员/商户一体化，支持等级自定义
- 📈 **推广系统** - 三级分销返佣功能
- 🏪 **共享店铺** - 无感知进货系统
- 🏪 **应用商店** - 插件和模板商店
- 📱 **响应式界面** - 完美支持PC和手机

### 功能简介

- 支付系统，拥有强悍的插件扩展能力，现目今已经支持全网任意平台，任意支付渠道。
- 云更新，如果系统升级新版本，你无需进行繁琐操作，只需要在你的店铺后台就可以无缝完成升级。
- 商品销售，支持商品配图、会员价、游客价、邮件通知、卡密预选（用户可以预选自己想购买的那个账号或者卡号）、API对接、强制登录购买、强悍的自定义控件功能、限时秒杀、批发优惠、优惠卷、等众多功能。
- 分站系统，前台用户可以开通分站，分站可以独立运行，也可以卖主站商品，有点类似商业店铺了。
- 会员系统，会员/商户融为一体，支持会员等级，以及商户等级完全自定义，以及商品可自定义会员等级对应价格。
- 推广/代理系统，拥有三级分销返佣功能，注册账号即实现自动发展下级。
- 共享店铺系统，可以在后台直接对接别人的店铺，通过扣除余额来进行无感知进货。
- 应用商店，拥有众多插件以及模板，让你的店铺变得格外强大。
- 界面美观，完美支持PC和手机，真正的内外二次元文化。
- 强悍的扩展能力，你可以通过本程序在几分钟之内快速的实现你任意想实现的在线购物功能，例子如下： 
  - 游戏方面，物品购买即时到玩家背包
  - 商业软件余额充值
  - 商业软件自动授权
  - 论坛/社区VIP自动开通
  - 只要你想得到，没有做不到。
- 还有更多强大的功能，需要安装自己发掘。至此，介绍完毕。

## 技术架构

### 核心技术栈
- **PHP 8.0+** - 主要开发语言，利用PHP 8的新特性和注解
- **MySQL 5.6+** - 数据存储（推荐 MySQL 5.7+ 或 8.0）
- **Eloquent ORM** - 数据库操作层
- **Smarty** - 模板引擎
- **Composer** - 依赖管理

### 架构特点
- **MVC 架构** - 清晰的模型-视图-控制器分离
- **依赖注入** - 使用DI容器管理依赖关系
- **注解系统** - 使用PHP 8注解进行配置和依赖注入
- **插件系统** - 强大的插件扩展机制
- **WAF防火墙** - 内置Web应用防火墙

## 项目结构

```
acg-faka/
├── app/                    # 应用核心代码
│   ├── Controller/         # 控制器层
│   │   ├── Admin/         # 后台管理控制器
│   │   ├── User/          # 用户前端控制器
│   │   ├── Shared/        # 共享控制器
│   │   └── Base/          # 基础控制器
│   ├── Model/             # 数据模型层
│   ├── Service/           # 业务逻辑服务层
│   ├── Entity/            # 实体类
│   ├── Pay/               # 支付相关类
│   ├── Util/              # 工具类
│   ├── View/              # 视图文件
│   ├── Interceptor/       # 拦截器
│   └── Consts/            # 常量定义
├── kernel/                # 核心框架
│   ├── Annotation/        # 注解系统
│   ├── Cache/             # 缓存组件
│   ├── Component/         # 核心组件
│   ├── Container/         # 依赖注入容器
│   ├── Database/          # 数据库组件
│   ├── Exception/         # 异常处理
│   ├── Plugin/            # 插件系统
│   ├── Waf/               # 防火墙
│   └── Install/           # 安装程序
├── config/                # 配置文件
├── assets/                # 静态资源
│   ├── admin/             # 后台静态资源
│   ├── user/              # 前台静态资源
│   ├── common/            # 公共静态资源
│   └── static/            # 第三方静态资源
├── vendor/                # Composer依赖
├── index.php              # 应用入口文件
└── composer.json          # 项目配置
```

## 安装和运行

### 系统要求
- **PHP**: >= 8.0.0
- **MySQL**: >= 5.6 (推荐 5.7+ 或 8.0)
- **扩展**: gd, curl, pdo, pdo_mysql, json, session, zip

### 安装方式

#### 1. 传统安装
1. **下载源码**
   ```bash
   composer create-project lizhipay/acg-faka:dev-main
   ```

2. **配置伪静态**
   
   **Apache**: 无需配置（.htaccess已配置）
   
   **Nginx**:
   ```nginx
   location / {
       if (!-e $request_filename){
           rewrite ^(.*)$ /index.php?s=$1 last; break;
       }
   }
   ```

   **Windows IIS**:
   ```xml
   <rules>
       <rule name="acg_rewrite" stopProcessing="true">
           <match url="^(.*)$"/>
           <conditions logicalGrouping="MatchAll">
               <add input="{HTTP_HOST}" pattern="^(.*)$"/>
               <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true"/>
               <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true"/>
           </conditions>
           <action type="Rewrite" url="index.php?s={R:1}"/>
       </rule>
   </rules>
   ```

3. **访问安装**
   - 访问首页开始安装向导
   - 配置数据库信息
   - 设置管理员账户

4. **后台访问**
   - 安装完成后访问：`https://你的域名/admin`

#### 2. Docker 部署（推荐）

使用提供的启动脚本快速启动系统：

```bash
# 1. 给启动脚本执行权限
chmod +x start.sh

# 2. 初始化项目
./start.sh init

# 3. 启动所有服务
./start.sh start

# 4. 启动安装向导
./start.sh install
```

**服务访问地址：**
- **网站首页**: http://localhost:8080
- **后台管理**: http://localhost:8080/admin
- **PHPMyAdmin**: http://localhost:8081
- **数据库端口**: 3306

**Docker 启动脚本命令：**
| 命令 | 说明 |
|------|------|
| `./start.sh init` | 初始化项目（创建目录、安装依赖） |
| `./start.sh start` | 启动所有服务 |
| `./start.sh stop` | 停止所有服务 |
| `./start.sh restart` | 重启所有服务 |
| `./start.sh status` | 查看服务状态和资源使用情况 |
| `./start.sh logs` | 实时查看日志 |
| `./start.sh build` | 重新构建镜像 |
| `./start.sh install` | 启动安装向导 |
| `./start.sh clean` | 清理所有 Docker 资源 |
| `./start.sh help` | 显示帮助信息 |

#### 3. VS Code DevContainer 开发环境

完整的容器化开发环境：

1. **安装 Remote - Containers 扩展**
2. **在 VS Code 中打开项目**
3. **按 `Ctrl+Shift+P` → 选择 "Dev Containers: Open Folder in Container"**
4. **等待容器构建完成**

**开发环境服务：**
- **主站**: http://localhost:8000
- **后台**: http://localhost:8000/admin
- **数据库**: localhost:3306
- **PHPMyAdmin**: http://localhost:8081

**DevContainer 快捷命令：**
```bash
# 启动服务
./start-dev.sh web        # Web 服务器 (端口 8000)
./start-dev.sh database   # 数据库
./start-dev.sh all        # 所有服务
./start-dev.sh stop       # 停止服务

# 数据库管理
./start-dev.sh shell      # 进入数据库容器
./start-dev.sh phpmyadmin # 启动 PHPMyAdmin
./start-dev.sh logs       # 查看日志
```

### 快速启动（Python 服务器）

对于简单的测试环境：

```bash
# 启动Python服务器
python3 run.py &

# 访问地址
# 主站: http://127.0.0.1:8088
# 后台: http://127.0.0.1:8088/admin
```

### 构建和运行命令

```bash
# 安装依赖
composer install

# 清理缓存
rm -rf cache/*

# 权限设置
chmod -R 755 ./
chmod -R 777 cache/
chmod -R 777 upload/
```

## 开发规范

### 编码风格
- 使用 **PHP 8.0+** 的严格类型声明
- 遵循 PSR-4 自动加载规范
- 使用 **注解系统** 进行依赖注入和配置
- 控制器类继承对应的基础类

### 目录约定
- **Controller**: 业务控制器，遵循 RESTful 风格
- **Model**: Eloquent 数据模型
- **Service**: 业务逻辑服务类
- **Util**: 工具类和帮助函数
- **Consts**: 系统常量定义

### 核心文件说明

#### 入口文件
- `index.php` - 应用入口，设置基本配置和路由解析
- `kernel/Kernel.php` - 核心框架初始化

#### 配置文件
- `config/app.php` - 应用基本配置
- `config/database.php` - 数据库配置
- `config/dependencies.php` - 依赖注入配置

#### 关键服务
- `App\Service\App` - 应用核心服务
- `App\Service\Pay` - 支付服务
- `App\Service\User` - 用户服务
- `App\Service\Order` - 订单服务

## 开发环境配置

### VS Code DevContainer 特性
- **PHP 8.1** - 完整支持项目要求
- **Composer 2** - 依赖管理
- **MySQL 8.0** - 开发数据库
- **Redis 7** - 缓存服务
- **Xdebug** - 调试支持
- **预配置扩展** - PHP、Git、数据库等工具

### Xdebug 调试配置
DevContainer 已预配置 Xdebug:

```ini
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
```

VS Code 调试配置 `.vscode/launch.json`:
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/workspace": "${workspaceFolder}"
            }
        }
    ]
}
```

## Docker 配置详情

### 环境变量
```yaml
# 数据库配置
MYSQL_ROOT_PASSWORD: acg_faka_root_password
MYSQL_DATABASE: acg_faka
MYSQL_USER: acg_faka
MYSQL_PASSWORD: acg_faka_password

# PHP 应用配置
DB_HOST: database
DB_NAME: acg_faka
DB_USER: acg_faka
DB_PASSWORD: acg_faka_password
```

### 包含的服务
| 服务 | 端口 | 说明 |
|------|------|------|
| web | 8080 | Nginx Web 服务器 |
| app | 9000 | PHP-FPM 应用服务 |
| database | 3306 | MySQL 8.0 数据库 |
| redis | 6379 | Redis 缓存 (可选) |
| phpmyadmin | 8081 | 数据库管理工具 |

## 插件开发

### 插件结构
```
plugins/
├── [PluginName]/
│   ├── Service.php      # 插件服务类
│   ├── Controller.php   # 插件控制器
│   ├── config.json      # 插件配置
│   └── assets/          # 插件静态资源
```

### 插件配置示例
```json
{
    "name": "插件名称",
    "version": "1.0.0",
    "description": "插件描述",
    "author": "作者",
    "hooks": ["hook_name"]
}
```

## 常见操作

### 数据库操作
```php
// 使用 Eloquent ORM
$users = User::where('status', 1)->get();

// 手动查询
$results = \DB::select("SELECT * FROM users WHERE status = ?", [1]);
```

### 配置文件
```php
// 读取配置
$value = config('database.host');

// 设置配置
setConfig(['key' => 'value'], 'config/file.php');
```

### 钩子系统
```php
// 注册钩子
hook('hook_name', function($data) {
    // 处理逻辑
});

// 触发钩子
hook('hook_name', $data);
```

### 数据备份与恢复
```bash
# 备份数据库 (Docker 环境)
docker exec acg-faka-database mysqldump -u acg_faka -pacg_faka_password acg_faka > backup_$(date +%Y%m%d_%H%M%S).sql

# 恢复数据库
docker exec -i acg-faka-database mysql -u acg_faka -pacg_faka_password acg_faka < backup_file.sql
```

## 安全特性

### WAF 防火墙
- 包含完整的 Web 应用防火墙
- 支持 URL、参数、Cookie、User-Agent 检测
- 配置文件位于 `config/waf/` 目录

### 数据安全
- 使用 JWT 进行身份验证
- 密码使用 Salt 加密
- XSS 攻击防护
- SQL 注入防护

## 部署建议

### 生产环境配置
1. **关闭 DEBUG**: 设置 `DEBUG = false`
2. **启用 OPcache**: 提升PHP执行性能
3. **配置缓存**: 启用文件缓存或Redis
4. **HTTPS**: 启用SSL证书
5. **定期备份**: 定期备份数据库和文件

### 性能优化
- 启用 MySQL 查询缓存
- 使用 CDN 加速静态资源
- 配置适当的 PHP 内存限制
- 定期清理日志和缓存文件

### Docker 生产环境示例
```yaml
# docker-compose.prod.yml
version: '3.8'
services:
  web:
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./ssl:/etc/nginx/ssl
  database:
    volumes:
      - ./mysql_data:/var/lib/mysql
  app:
    environment:
      - APP_ENV=production
      - DEBUG=false
```

**生产环境注意事项：**
- 更改所有默认密码
- 使用环境变量管理敏感信息
- 配置 SSL 证书
- 限制网络访问权限

## 常见操作

### 数据库操作
```php
// 使用 Eloquent ORM
$users = User::where('status', 1)->get();

// 手动查询
$results = \DB::select("SELECT * FROM users WHERE status = ?", [1]);
```

### 配置文件
```php
// 读取配置
$value = config('database.host');

// 设置配置
setConfig(['key' => 'value'], 'config/file.php');
```

### 钩子系统
```php
// 注册钩子
hook('hook_name', function($data) {
    // 处理逻辑
});

// 触发钩子
hook('hook_name', $data);
```

## 部署建议

### 生产环境配置
1. **关闭 DEBUG**: 设置 `DEBUG = false`
2. **启用 OPcache**: 提升PHP执行性能
3. **配置缓存**: 启用文件缓存或Redis
4. **HTTPS**: 启用SSL证书
5. **定期备份**: 定期备份数据库和文件

### 性能优化
- 启用 MySQL 查询缓存
- 使用 CDN 加速静态资源
- 配置适当的 PHP 内存限制
- 定期清理日志和缓存文件

## 故障排除

### 常见问题
1. **404错误**: 检查伪静态配置
2. **数据库连接失败**: 检查 `config/database.php` 配置
3. **权限错误**: 确保 `cache/` 和 `upload/` 目录可写
4. **PHP版本问题**: 确认PHP版本 >= 8.0

### Docker 环境问题

#### 端口冲突
```bash
# 检查端口占用
lsof -i :8080

# 修改 docker-compose.yml 中的端口映射
ports:
  - "8088:80"  # 改为 8088 端口
```

#### 权限问题
```bash
# 修复权限
sudo chown -R $USER:$USER .
chmod +x start.sh
```

#### 内存不足
```bash
# 检查 Docker 内存限制
docker system df

# 清理未使用的资源
docker system prune -af
```

#### 数据库连接失败
```bash
# 检查数据库状态
docker-compose logs database

# 等待数据库启动完成
sleep 30
```

#### 容器构建失败
```bash
# 清理 Docker 缓存
docker system prune -a

# 重新构建
docker-compose -f .devcontainer/docker-compose.yml build --no-cache
```

#### Xdebug 不工作
```bash
# 检查 Xdebug 状态
php -m | grep xdebug

# 验证配置
php -i | grep xdebug
```

### 重置系统
```bash
# Docker 环境完全重置
./start.sh clean
./start.sh init
./start.sh start

# DevContainer 重置
Dev Containers: Rebuild Container Without Cache
```

### 查看日志
```bash
# 查看所有服务日志
./start.sh logs

# 查看特定服务日志
docker-compose logs web
docker-compose logs app
docker-compose logs database

# DevContainer 查看日志
./start-dev.sh logs
```

### 日志位置
- **错误日志**: PHP 错误日志
- **应用日志**: `cache/logs/` 目录
- **WAF日志**: `cache/waf/` 目录
- **Docker日志**: `docker-compose logs [service]`

## 日常维护

### 服务管理
```bash
# 重启服务
./start.sh restart

# 停止服务
./start.sh stop

# 查看服务状态
./start.sh status

# 进入容器调试
docker exec -it acg-faka-app bash
```

### 性能监控
- 定期检查系统资源使用情况
- 监控数据库连接数
- 检查缓存命中率
- 优化慢查询

## 更新说明

当更新应用代码时：

```bash
# Docker 环境
./start.sh build
./start.sh restart

# 传统环境
composer install
rm -rf cache/*
chmod -R 777 cache/
```

## 支持和文档

### 官方资源
- **官方文档**: https://faka.wiki
- **演示地址**: 
  - 后台：http://162.14.111.118:91/admin
  - 前台：http://162.14.111.118:91
- **技术交流**: QQ群 970103572
- **Telegram**: http://t.me/mcyofficial

### 相关文档
- **Docker 部署指南**: 详细 Docker 部署说明
- **DevContainer 使用指南**: VS Code 开发环境配置
- **快速启动指南**: 各种安装方式的快速入门

---

*本项目基于 MIT 协议开源，仅供学习和研究使用。*