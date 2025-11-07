#!/bin/bash

# 异次元店铺系统 - DevContainer 初始化脚本
# 容器创建后首次运行

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 初始化异次元店铺系统开发环境...${NC}"

# 检查是否在容器中
if [ ! -f "/.dockerenv" ]; then
    echo -e "${YELLOW}⚠️  警告: 建议在容器中运行此脚本${NC}"
fi

# 检查项目文件
echo -e "${BLUE}📦 检查项目文件...${NC}"
if [ -f "composer.json" ]; then
    echo -e "${GREEN}✅ composer.json 存在${NC}"
else
    echo -e "${YELLOW}⚠️  未找到 composer.json${NC}"
fi

# 创建必要的目录
echo -e "${BLUE}📁 创建目录结构...${NC}"
mkdir -p cache
mkdir -p upload
mkdir -p logs
mkdir -p temp

# 设置权限
echo -e "${BLUE}🔐 设置目录权限...${NC}"
chmod -R 777 cache 2>/dev/null || true
chmod -R 777 upload 2>/dev/null || true
chmod -R 777 logs 2>/dev/null || true

# 检查数据库配置
echo -e "${BLUE}🗄️  检查数据库配置...${NC}"
if [ -f "config/database.php" ]; then
    echo -e "${GREEN}✅ 开发数据库配置已存在${NC}"
else
    echo -e "${YELLOW}⚠️  建议创建 config/database.php${NC}"
fi

# 安装前端依赖 (如果存在)
if [ -f "package.json" ]; then
    echo -e "${BLUE}📦 检查前端依赖...${NC}"
    if command -v npm &> /dev/null; then
        npm install
        echo -e "${GREEN}✅ 前端依赖安装完成${NC}"
    else
        echo -e "${YELLOW}⚠️  npm 未安装${NC}"
    fi
fi

# 配置 Git (如果存在)
if [ -d ".git" ]; then
    echo -e "${BLUE}🔧 配置 Git...${NC}"
    git config --local core.autocrlf false 2>/dev/null || true
    echo -e "${GREEN}✅ Git 配置完成${NC}"
fi

# 创建开发配置
echo -e "${BLUE}⚙️  创建开发配置文件...${NC}"

# .env 配置文件 (如果项目使用)
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
    echo -e "${GREEN}✅ 创建了 .env 文件${NC}"
fi

# 开发环境特定配置
if [ ! -f "config/app.dev.php" ]; then
    cat > config/app.dev.php << 'EOF'
<?php
return [
    'debug' => true,
    'env' => 'development',
    'log_level' => 'debug',
    'cache' => [
        'enabled' => false,
        'driver' => 'file'
    ]
];
EOF
    echo -e "${GREEN}✅ 创建了开发环境配置${NC}"
fi

echo -e "${GREEN}🎉 初始化完成！${NC}"
echo -e "${BLUE}📝 下一步:${NC}"
echo "   1. 启动数据库: ./start.sh database"
echo "   2. 启动开发服务器: ./start.sh web"
echo "   3. 访问: http://localhost:8000"