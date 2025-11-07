#!/bin/bash

# 异次元店铺系统 - DevContainer 创建后脚本
# 容器创建完成后运行

set -e

# 颜色定义
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}🔧 容器创建完成，进行环境配置...${NC}"

# 等待数据库服务 (如果需要)
if command -v mysql &> /dev/null; then
    echo -e "${BLUE}🗄️  测试数据库连接...${NC}"
    for i in {1..30}; do
        if mysql -h database -u acg_faka -pdev_password -e "SELECT 1;" 2>/dev/null; then
            echo -e "${GREEN}✅ 数据库连接成功${NC}"
            break
        fi
        if [ $i -eq 30 ]; then
            echo -e "${YELLOW}⚠️  数据库连接超时，将在需要时重试${NC}"
        fi
        sleep 2
    done
fi

# 安装 PHP 依赖
echo -e "${BLUE}📦 安装 PHP 依赖...${NC}"
if [ -f "composer.json" ]; then
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader || {
            echo -e "${YELLOW}⚠️  Composer 安装失败，尝试开发模式...${NC}"
            composer install
        }
        echo -e "${GREEN}✅ PHP 依赖安装完成${NC}"
    else
        echo -e "${YELLOW}⚠️  Composer 未找到，跳过依赖安装${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  未找到 composer.json${NC}"
fi

# 复制开发配置文件
echo -e "${BLUE}📄 配置开发环境文件...${NC}"

# 确保配置文件存在
if [ ! -f "config/database.php" ]; then
    if [ -f "config/database.php" ]; then
        cp config/database.php config/database.php
        echo -e "${GREEN}✅ 数据库配置已创建${NC}"
    else
        echo -e "${YELLOW}⚠️  建议创建数据库配置文件${NC}"
    fi
fi

# 清理和优化
echo -e "${BLUE}🧹 清理和优化...${NC}"

# 清理Composer缓存
composer clear-cache 2>/dev/null || true

# 安装 fish shell
echo -e "${BLUE}🐚 安装 Fish Shell...${NC}"
if ! command -v fish &> /dev/null; then
    # 尝试安装 fish (不需要 sudo)
    if command -v apt-get &> /dev/null; then
        echo "尝试安装 fish..."
        # 检查是否有 sudo 权限
        if sudo -n true 2>/dev/null; then
            sudo apt-get update && sudo apt-get install -y fish
        else
            echo "没有 sudo 权限，跳过 fish 安装"
        fi
    fi
else
    echo -e "${GREEN}✅ Fish Shell 已安装${NC}"
fi

# 安装 iflow-cli (不需要 sudo)
echo -e "${BLUE}⚡ 安装 iFlow CLI...${NC}"
if ! npm list -g @iflow-ai/iflow-cli &> /dev/null; then
    npm install -g @iflow-ai/iflow-cli || {
        echo "npm install 失败，可能需要权限"
    }
else
    echo -e "${GREEN}✅ iFlow CLI 已安装${NC}"
fi

# 设置默认 shell 为 fish (如果存在)
if command -v fish &> /dev/null; then
    echo -e "${BLUE}🔧 设置默认 Shell...${NC}"
    chsh -s /usr/bin/fish vscode 2>/dev/null || echo "设置默认 shell 失败，但 fish 可用"
fi

# 优化自动加载器
if [ -f "vendor/autoload.php" ]; then
    composer dump-autoload --optimize 2>/dev/null || true
    echo -e "${GREEN}✅ 自动加载器已优化${NC}"
fi

# 创建快捷命令
echo -e "${BLUE}⚡ 创建快捷命令...${NC}"

# 创建 dev container 专用的启动脚本
cat > /workspace/start.sh << 'EOF'
#!/bin/bash
# 异次元店铺系统开发环境启动脚本

case "$1" in
    "web")
        echo "🌐 启动 Web 服务器..."
        php -S 0.0.0.0:8000 -t /workspace
        ;;
    "database")
        echo "🗄️ 启动数据库服务..."
        docker-compose -f .devcontainer/docker-compose.yml up -d database
        ;;
    "all")
        echo "🚀 启动所有服务..."
        docker-compose -f .devcontainer/docker-compose.yml up -d
        ;;
    "stop")
        echo "🛑 停止所有服务..."
        docker-compose -f .devcontainer/docker-compose.yml down
        ;;
    "logs")
        echo "📋 查看日志..."
        docker-compose -f .devcontainer/docker-compose.yml logs -f
        ;;
    "shell")
        echo "🐚 进入数据库容器..."
        docker-compose -f .devcontainer/docker-compose.yml exec database bash
        ;;
    "phpmyadmin")
        echo "🗄️ 启动 PHPMyAdmin..."
        docker-compose -f .devcontainer/docker-compose.yml --profile tools up -d phpmyadmin
        echo "访问: http://localhost:8081"
        ;;
    *)
        echo "用法: $0 {web|database|all|stop|logs|shell|phpmyadmin}"
        echo ""
        echo "命令说明:"
        echo "  web        - 启动 PHP 内置服务器 (端口 8000)"
        echo "  database   - 启动 MySQL 数据库"
        echo "  all        - 启动所有服务"
        echo "  stop       - 停止所有服务"
        echo "  logs       - 查看服务日志"
        echo "  shell      - 进入数据库容器"
        echo "  phpmyadmin - 启动 PHPMyAdmin (端口 8081)"
        ;;
esac
EOF

chmod +x /workspace/start.sh

# 创建项目信息文件
cat > /workspace/.devcontainer-info.md << 'EOF'
# 异次元店铺系统 - 开发环境信息

## 🚀 快速开始

### 1. 启动服务
```bash
# 启动 Web 服务器
./start.sh web

# 或者启动所有服务
./start.sh all
```

### 2. 访问地址
- **主站**: http://localhost:8000
- **后台**: http://localhost:8000/admin
- **数据库**: localhost:3306
- **PHPMyAdmin**: http://localhost:8081 (运行: `./start.sh phpmyadmin`)

### 3. 数据库配置
- **主机**: database
- **数据库**: acg_faka_dev
- **用户名**: acg_faka
- **密码**: dev_password

## 🔧 开发工具

### 扩展
已自动安装以下 VS Code 扩展:
- PHP 调试 (xdebug.php-debug)
- PHP 智能感知 (intelephense)
- Git 工具 (gitlens)
- 远程容器 (remote-containers)

### 命令
```bash
# 数据库管理
./start.sh shell      # 进入数据库容器
./start.sh logs       # 查看日志

# Composer 命令
composer install          # 安装依赖
composer update           # 更新依赖
composer dump-autoload    # 重新生成自动加载器

# Git 命令
git status               # 查看状态
git add .                # 暂存所有文件
git commit -m "message"  # 提交更改
```

## ⚙️ 配置

### 环境变量
在 `config/app.dev.php` 中设置开发环境配置:
```php
return [
    'debug' => true,
    'env' => 'development'
];
```

### 数据库配置
使用 `config/database.php` 配置数据库连接。

## 🔍 故障排除

### 常见问题
1. **端口占用**: 检查 8000, 3306 端口是否被占用
2. **权限问题**: 确保缓存和上传目录可写
3. **数据库连接**: 等待数据库服务完全启动

### 调试
```bash
# 查看容器状态
docker-compose -f .devcontainer/docker-compose.yml ps

# 查看特定服务日志
docker-compose -f .devcontainer/docker-compose.yml logs database

# 重启服务
docker-compose -f .devcontainer/docker-compose.yml restart
```

## 📚 相关文档
- [项目文档](../IFLOW.md)
- [Docker 部署指南](../DOCKER_README.md)
- [快速开始](../QUICK_START.md)
EOF

echo -e "${GREEN}🎉 容器环境配置完成！${NC}"
echo -e "${BLUE}💡 提示:${NC}"
echo "   - 运行 ./start.sh web 启动开发服务器"
echo "   - 访问 http://localhost:8000 查看应用"
echo "   - 查看 .devcontainer-info.md 获取更多信息"