#!/bin/bash

# 异次元店铺系统 - DevContainer 连接后脚本
# VS Code 连接到容器后运行

set -e

# 颜色定义
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🎉 欢迎使用异次元店铺系统开发环境！${NC}"
echo ""

# 显示项目信息
echo -e "${BLUE}📋 项目信息:${NC}"
echo "   项目: 异次元店铺系统 (ACG FAKA)"
echo "   版本: 3.2.3"
echo "   PHP: 8.1"
echo "   框架: 自主开发 MVC 框架"
echo ""

# 显示快速开始指南
echo -e "${BLUE}🚀 快速开始:${NC}"
echo "   1. 启动 Web 服务器: ${GREEN}./start.sh web${NC}"
echo "   2. 访问应用: ${GREEN}http://localhost:8000${NC}"
echo "   3. 后台管理: ${GREEN}http://localhost:8000/admin${NC}"
echo ""

# 显示开发工具
echo -e "${BLUE}🔧 可用工具:${NC}"
echo "   • VS Code 扩展已自动安装"
echo "   • PHP 调试已配置 (Xdebug)"
echo "   • Git 工具已就绪"
echo "   • 数据库客户端已安装"
echo ""

# 显示快捷命令
echo -e "${BLUE}⚡ 常用命令:${NC}"
echo "   ${YELLOW}启动服务:${NC}"
echo "     ./start.sh web        - Web 服务器 (端口 8000)"
echo "     ./start.sh database   - 数据库"
echo "     ./start.sh all        - 所有服务"
echo "     ./start.sh stop       - 停止服务"
echo ""
echo "   ${YELLOW}数据库管理:${NC}"
echo "     ./start.sh shell      - 进入数据库容器"
echo "     ./start.sh phpmyadmin - 启动 PHPMyAdmin"
echo "     ./start.sh logs       - 查看日志"
echo ""
echo "   ${YELLOW}开发工具:${NC}"
echo "     composer install          - 安装依赖"
echo "     composer update           - 更新依赖"
echo "     php artisan migrate       - 数据库迁移 (如果使用)"
echo "     git status                - Git 状态"

# 显示有用的快捷键
echo ""
echo -e "${BLUE}⌨️  VS Code 快捷键:${NC}"
echo "   F5                    - 开始调试"
echo "   Ctrl+`                - 打开终端"
echo "   Ctrl+Shift+P          - 命令面板"
echo "   Ctrl+Shift+E          - 资源管理器"
echo "   Ctrl+Shift+X          - 扩展"
echo "   Ctrl+Shift+G          - Git 面板"

# 检查环境状态
echo ""
echo -e "${BLUE}🔍 环境检查:${NC}"

# 检查关键文件
if [ -f "index.php" ]; then
    echo "   ✅ 主入口文件存在"
else
    echo "   ❌ 主入口文件缺失"
fi

if [ -f "composer.json" ]; then
    echo "   ✅ Composer 配置存在"
else
    echo "   ⚠️  Composer 配置缺失"
fi

if [ -f "config/database.php" ] || [ -f "config/database.php" ]; then
    echo "   ✅ 数据库配置存在"
else
    echo "   ⚠️  数据库配置缺失"
fi

# 显示故障排除提示
echo ""
echo -e "${YELLOW}💡 故障排除:${NC}"
echo "   • 如果端口被占用，修改 start.sh 中的端口"
echo "   • 如果数据库连接失败，运行: ./start.sh database"
echo "   • 如果权限问题，尝试: chmod -R 777 cache upload"
echo "   • 查看完整文档: cat .devcontainer-info.md"

echo ""
echo -e "${GREEN}✨ 祝您开发愉快！${NC}"

# 可选：自动启动开发服务器 (取消注释以启用)
# echo -e "${BLUE}🌐 自动启动开发服务器...${NC}"
# exec ./start.sh web