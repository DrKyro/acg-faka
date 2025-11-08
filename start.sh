#!/bin/bash

# 异次元店铺系统 Docker 启动脚本
# 使用方法: ./start.sh [command]

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 函数定义
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查 Docker 和 Docker Compose
check_requirements() {
    print_info "检查系统要求..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker 未安装，请先安装 Docker"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose 未安装，请先安装 Docker Compose"
        exit 1
    fi
    
    print_success "系统要求检查通过"
}

# 创建必要的目录
create_directories() {
    print_info "创建必要的目录..."
    
    mkdir -p cache
    mkdir -p upload
    mkdir -p logs
    
    # 设置权限
    chmod -R 777 cache
    chmod -R 777 upload
    chmod -R 777 logs
    
    print_success "目录创建完成"
}

# 初始化项目
init() {
    print_info "初始化项目..."
    
    # 创建启动脚本可执行
    chmod +x "$0"
    
    # 安装 Composer 依赖 (如果本地没有vendor目录)
    if [ ! -d "vendor" ]; then
        print_info "安装 Composer 依赖..."
        composer install --no-dev --optimize-autoloader
    fi
    
    print_success "项目初始化完成"
}

# 启动服务
start() {
    print_info "启动服务..."
    
    # 启动服务
    docker-compose up -d
    
    # 等待服务启动
    print_info "等待服务启动..."
    sleep 10
    
    # 检查服务状态
    if docker-compose ps | grep -q "Up"; then
        print_success "服务启动成功！"
        print_info "访问地址: http://localhost:8088"
        print_info "数据库端口: 3306"
    else
        print_error "服务启动失败，请检查日志"
        docker-compose logs
    fi
}

# 停止服务
stop() {
    print_info "停止服务..."
    docker-compose down
    print_success "服务已停止"
}

# 重启服务
restart() {
    print_info "重启服务..."
    docker-compose restart
    print_success "服务已重启"
}

# 查看日志
logs() {
    docker-compose logs -f
}

# 查看状态
status() {
    print_info "服务状态:"
    docker-compose ps
    
    echo ""
    print_info "资源使用情况:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"
}

# 构建镜像
build() {
    print_info "构建镜像..."
    docker-compose build --no-cache
    print_success "镜像构建完成"
}

# 清理
clean() {
    print_warning "这将删除所有容器、镜像和数据，是否继续？ (y/N)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        print_info "清理所有资源..."
        docker-compose down -v
        docker-compose rm -f
        docker system prune -af
        print_success "清理完成"
    else
        print_info "取消清理操作"
    fi
}

# 删除旧的重新构建启动
rebuild() {
    print_info "删除旧的容器和镜像，重新构建并启动服务..."
    
    # 停止服务
    print_info "停止服务..."
    docker-compose down
    
    # 清理旧的容器和镜像
    print_info "清理旧的容器和镜像..."
    docker-compose down -v --remove-orphans
    docker rmi $(docker images --filter "reference=$(basename "$PWD")*" -q) 2>/dev/null || true
    docker system prune -f
    
    # 重新构建镜像
    print_info "重新构建镜像..."
    docker-compose build --no-cache
    
    # 创建必要目录
    create_directories
    
    # 启动服务
    print_info "启动服务..."
    docker-compose up -d
    
    # 等待服务启动
    print_info "等待服务启动..."
    sleep 10
    
    # 检查服务状态
    if docker-compose ps | grep -q "Up"; then
        print_success "重新构建并启动成功！"
        print_info "访问地址: http://localhost:8088"
        print_info "数据库端口: 3306"
    else
        print_error "服务启动失败，请检查日志"
        docker-compose logs
    fi
}

# 安装向导
install_wizard() {
    print_info "启动安装向导..."
    print_info "请在浏览器中访问: http://localhost:8080"
    print_info "按照安装向导完成设置"
    
    # 打开浏览器 (Linux/Mac)
    if command -v xdg-open &> /dev/null; then
        xdg-open http://localhost:8080
    elif command -v open &> /dev/null; then
        open http://localhost:8080
    fi
}

# 显示帮助信息
show_help() {
    echo "异次元店铺系统 Docker 启动脚本"
    echo ""
    echo "使用方法: $0 [命令]"
    echo ""
    echo "可用命令:"
    echo "  init        初始化项目"
    echo "  start       启动所有服务"
    echo "  stop        停止所有服务"
    echo "  restart     重启所有服务"
    echo "  status      查看服务状态"
    echo "  logs        查看日志"
    echo "  build       构建镜像"
    echo "  rebuild     删除旧的重新构建并启动"
    echo "  install     启动安装向导"
    echo "  clean       清理所有资源"
    echo "  help        显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 init && $0 start    # 初始化并启动服务"
    echo "  $0 logs                # 查看日志"
    echo "  $0 install             # 启动安装向导"
    echo "  $0 rebuild             # 删除旧的镜像重新构建并启动"
}

# 主程序
main() {
    case "${1:-start}" in
        "init")
            check_requirements
            create_directories
            init
            ;;
        "start")
            check_requirements
            create_directories
            start
            ;;
        "stop")
            stop
            ;;
        "restart")
            restart
            ;;
        "logs")
            logs
            ;;
        "status")
            status
            ;;
        "build")
            check_requirements
            build
            ;;
        "rebuild")
            check_requirements
            rebuild
            ;;
        "clean")
            clean
            ;;
        "install")
            install_wizard
            ;;
        "help"|"--help"|"-h")
            show_help
            ;;
        *)
            print_error "未知命令: $1"
            show_help
            exit 1
            ;;
    esac
}

# 执行主程序
main "$@"