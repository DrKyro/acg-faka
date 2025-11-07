#!/bin/bash

# 异次元店铺系统 - DevContainer 启动后脚本
# 容器启动后运行

set -e

echo -e "${BLUE}🚀 启动开发环境...${NC}"

# 等待服务准备就绪
sleep 5

# 显示环境信息
echo -e "${GREEN}📊 环境信息:${NC}"
echo "   PHP 版本: $(php --version | head -1)"
echo "   Composer 版本: $(composer --version)"
echo "   工作目录: $(pwd)"
echo "   用户: $(whoami)"

# 检查关键目录
echo -e "${BLUE}📁 检查目录状态:${NC}"
for dir in "cache" "upload" "logs" "vendor"; do
    if [ -d "$dir" ]; then
        echo "   ✅ $dir"
    else
        echo "   ❌ $dir (不存在)"
    fi
done

# 显示服务状态
echo -e "${BLUE}🔍 检查服务状态:${NC}"

# 检查数据库
if command -v mysql &> /dev/null; then
    if mysql -h database -u acg_faka -pdev_password -e "SELECT 1;" 2>/dev/null; then
        echo "   ✅ MySQL 数据库"
    else
        echo "   ⏳ MySQL 数据库 (连接中...)"
    fi
else
    echo "   ⏳ MySQL 客户端将随后可用"
fi

# 检查端口状态
echo -e "${BLUE}🌐 端口状态:${NC}"
for port in 8000 3306 6379; do
    if netstat -tuln 2>/dev/null | grep -q ":$port "; then
        echo "   ✅ 端口 $port (已占用)"
    else
        echo "   ⭕ 端口 $port (空闲)"
    fi
done

# 显示快捷提示
echo -e "${GREEN}💡 快捷命令:${NC}"
echo "   ./start.sh web        - 启动 Web 服务器"
echo "   ./start.sh database   - 启动数据库"
echo "   ./start.sh phpmyadmin - 启动 PHPMyAdmin"
echo ""
echo -e "${GREEN}🎯 下一步:${NC}"
echo "   1. 运行: ./start.sh web"
echo "   2. 访问: http://localhost:8000"
echo "   3. 配置数据库连接"
echo ""
echo -e "${YELLOW}💡 Fish Shell 说明:${NC}"
if ! command -v fish &> /dev/null; then
    echo "   - Fish 未自动安装"
    echo "   - 运行: ./install-fish.sh 手动安装"
    echo "   - 或联系管理员安装: sudo apt-get install -y fish"
else
    echo "   - Fish Shell 已配置为默认终端"
fi

# 确保 fish 终端配置
if command -v fish &> /dev/null; then
    echo -e "${BLUE}🐚 配置 Fish 终端...${NC}"
    # 设置 VS Code 终端配置为 fish
    mkdir -p ~/.config/fish
    echo 'set -gx PATH "$HOME/.npm-global/bin" $PATH' > ~/.config/fish/config.fish 2>/dev/null || true
    echo -e "${GREEN}✅ Fish 终端配置完成${NC}"
fi