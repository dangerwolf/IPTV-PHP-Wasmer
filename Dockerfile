# =============================================================
# 阶段一：构建阶段（如果有需要编译的依赖可以在这里处理）
# =============================================================
FROM php:8.2-cli-alpine AS builder

# 如果你的自定义 .so 依赖某些 Alpine 系统库
# 可以在这里安装，例如：
# RUN apk add --no-cache libcurl openssl-dev

# =============================================================
# 阶段二：运行阶段
# =============================================================
FROM php:8.2-cli-alpine

# 安装运行时系统依赖（根据你的 .so 实际依赖调整）
RUN apk add --no-cache \
    curl \
    ca-certificates \
    # 如果 .so 依赖这些库，取消注释：
    # libcurl \
    # openssl \
    # libxml2 \
    tzdata

# 设置时区
ENV TZ=Asia/Shanghai

# 创建扩展目录
RUN mkdir -p /usr/local/lib/php/extensions/custom

# 复制自定义 .so 扩展
COPY ext/custom.so /usr/local/lib/php/extensions/custom/custom.so

# 复制 php.ini（包含 extension 配置）
COPY config/php.ini /usr/local/etc/php/php.ini

# 如果需要配置扩展目录，在 php.ini 中添加：
# extension_dir = /usr/local/lib/php/extensions/custom
# 或者在这里用 RUN 追加：
RUN echo "extension_dir = /usr/local/lib/php/extensions/custom" >> /usr/local/etc/php/php.ini

# 复制应用代码
COPY src/index.php /var/www/html/index.php

# 工作目录
WORKDIR /var/www/html

# 暴露端口
EXPOSE 8080

# 内置 PHP 服务器作为容器运行方式
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
