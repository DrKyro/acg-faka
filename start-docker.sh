#!/bin/bash

# ACG 发卡系统 Docker 启动脚本

echo "正在启动 ACG 发卡系统 Docker 环境..."

# 检查 Docker 是否安装
if ! command -v docker &> /dev/null
then
    echo "错误: 未检测到 Docker，请先安装 Docker"
    exit 1
fi

# 检查 Docker Compose 是否安装
if ! command -v docker-compose &> /dev/null
then
    echo "错误: 未检测到 Docker Compose，请先安装 Docker Compose"
    exit 1
fi

# 检查 .env 文件是否存在
if [ ! -f ".env" ]; then
    echo "警告: .env 文件不存在，将使用默认配置"
    echo "如需自定义配置，请创建 .env 文件"
fi

# 构建并启动服务
echo "正在构建并启动 Docker 服务..."
docker-compose up -d

if [ $? -eq 0 ]; then
    echo "服务启动成功！"
    echo ""
    echo "请访问以下地址："
    echo "前台: http://localhost:8080"
    echo "后台: http://localhost:8080/admin"
    echo ""
    echo "首次访问会自动跳转到安装页面，请按照提示完成安装。"
    echo ""
    echo "查看服务状态: docker-compose ps"
    echo "查看日志: docker-compose logs"
else
    echo "服务启动失败，请检查错误信息"
    exit 1
fi