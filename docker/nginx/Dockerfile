FROM nginx:alpine

# 复制 Nginx 配置文件
COPY nginx.conf /etc/nginx/conf.d/default.conf

# 设置工作目录
WORKDIR /var/www/html

# 暴露端口
EXPOSE 80

# 保持容器运行
CMD ["nginx", "-g", "daemon off;"]