
# CCTV 直播流代理服务

基于 PHP 的央视及卫视直播流代理服务，支持 CCTV 全频道、省级卫视及付费频道的直播与回看播放。

已针对 **Wasmer Edge（32 位 PHP WASM）** 进行完整适配，同时提供 **Docker** 部署方案。

---

## 功能特性

- 全频道直播流代理，输出标准 M3U8 播放列表
- 支持回看功能（指定时间段播放）
- 自动 TS 路径补全
- 频道列表 JSON API
- 直播流缓存机制（80 秒）
- 内置 TEA 加密、自定义 Base64、签名算法（32 位 / 64 位 PHP 全兼容）

---

## 项目结构

```
cctv-play-proxy/
├── app/                      # 应用代码目录
│   ├── index.php             # 路由入口（统一入口）
│   ├── channels.php          # 频道数据源（唯一维护点）
│   ├── yspios.php            # 核心业务逻辑（加密、请求、流代理）
│   └── ext/                  # 扩展文件目录（Docker 使用）
│       └── custom.so         # 自定义 PHP 扩展（如需要）
├── config/
│   └── php.ini               # PHP 运行时配置
├── app.toml                  # Wasmer Edge 部署配置
├── wasmer.toml               # Wasmer 包描述文件
├── Dockerfile                # Docker 部署配置
├── .dockerignore
├── .gitignore
└── README.md
```

### 文件说明

| 文件 | 用途 |
|---|---|
| `app/index.php` | 统一路由入口，将 URL 映射到对应处理文件 |
| `app/channels.php` | 所有频道的 ID、直播 PID、画质、频道名称数据 |
| `app/yspios.php` | 核心逻辑：TEA 加密、cKey 生成、央视 API 请求、M3U8 代理输出 |
| `app/ext/custom.so` | 自定义 PHP 扩展（仅 Docker 方案使用，Wasmer 方案不需要） |
| `config/php.ini` | PHP 配置：扩展加载、时区、超时等 |
| `app.toml` | Wasmer Edge 的 HTTP 服务入口和环境变量配置 |
| `wasmer.toml` | Wasmer 包的元信息和模块声明 |

---

## 路由说明

所有 HTTP 请求通过 `app/index.php` 统一分发：

| URL 路径 | 处理文件 | 说明 |
|---|---|---|
| `/` | `yspios.php` | 默认播放页，需要 `?id=` 参数 |
| `/yspios` | `yspios.php` | 同上 |
| `/channels` | 频道 API | 返回全部频道 JSON 列表 |

---

## API 文档

### 1. 直播播放

```
GET /?id={channel_id}
```

返回标准 M3U8 播放列表，可直接在播放器中使用。

**参数：**

| 参数 | 必填 | 说明 | 示例 |
|---|---|---|---|
| `id` | 否 | 频道 ID，默认 `cctv1` | `cctv5`、`hnws` |
| `playseek` | 否 | 回看时间段 | `20250625080000-20250625090000` |

**示例：**

```bash
# 直播
curl "https://your-domain.wasmer.app/?id=cctv1"

# 回看（2025年6月25日 08:00 - 09:00）
curl "https://your-domain.wasmer.app/?id=cctv1&playseek=20250625080000-20250625090000"

# 在播放器中使用
# IINA / VLC / mpv 等直接打开 URL 即可
```

### 2. 频道列表

```
GET /channels
GET /channels?type={filter}
```

返回 JSON 格式的频道列表，包含频道 ID、名称、画质和播放链接。

**筛选参数：**

| type 值 | 说明 |
|---|---|
| 不传 | 返回全部频道 |
| `cctv` | 只返回央视频道（CCTV + CGTN） |
| `local` | 只返回地方卫视 |
| `premium` | 只返回付费频道（shd 画质） |

**响应示例：**

```json
{
    "total": 68,
    "channels": [
        {
            "id": "cctv1",
            "name": "CCTV-1 综合",
            "quality": "fhd",
            "play_url": "https://your-domain.wasmer.app/?id=cctv1"
        },
        {
            "id": "cctv2",
            "name": "CCTV-2 财经",
            "quality": "fhd",
            "play_url": "https://your-domain.wasmer.app/?id=cctv2"
        }
    ]
}
```

### 3. 完整频道 ID 列表

<details>
<summary>点击展开全部频道</summary>

**央视频道：**

| ID | 频道名称 | 画质 |
|---|---|---|
| `cctv1` | CCTV-1 综合 | fhd |
| `cctv2` | CCTV-2 财经 | fhd |
| `cctv3` | CCTV-3 综艺 | fhd |
| `cctv4` | CCTV-4 中文国际 | fhd |
| `cctv5` | CCTV-5 体育 | fhd |
| `cctv5p` | CCTV-5+ 体育赛事 | fhd |
| `cctv6` | CCTV-6 电影 | fhd |
| `cctv7` | CCTV-7 国防军事 | fhd |
| `cctv8` | CCTV-8 电视剧 | fhd |
| `cctv9` | CCTV-9 纪录 | fhd |
| `cctv10` | CCTV-10 科教 | fhd |
| `cctv11` | CCTV-11 戏曲 | fhd |
| `cctv12` | CCTV-12 社会与法 | fhd |
| `cctv13` | CCTV-13 新闻 | fhd |
| `cctv14` | CCTV-14 少儿 | fhd |
| `cctv15` | CCTV-15 音乐 | fhd |
| `cctv16` | CCTV-16 奥林匹克 | fhd |
| `cctv164k` | CCTV-16 4K | fhd |
| `cctv17` | CCTV-17 农业农村 | fhd |
| `cctv4k` | CCTV-4K 超高清 | fhd |
| `cctv8k` | CCTV-8K | fhd |
| `cgtn` | CGTN 英语 | fhd |
| `cgtnfy` | CGTN 法语 | fhd |
| `cgtney` | CGTN 俄语 | fhd |
| `cgtnalby` | CGTN 阿拉伯语 | fhd |
| `cgtnxby` | CGTN 西班牙语 | fhd |
| `cgtnwyjl` | CGTN 外语纪录 | fhd |

**付费频道：**

| ID | 频道名称 | 画质 |
|---|---|---|
| `cctvfyjc` | CCTV 风云剧场 | shd |
| `cctvdyjc` | CCTV 第一剧场 | shd |
| `cctvhjjc` | CCTV 怀旧剧场 | shd |
| `cctvsjdl` | CCTV 世界地理 | shd |
| `cctvfyyy` | CCTV 风云音乐 | shd |
| `cctvbqkj` | CCTV 兵器科技 | shd |
| `cctvfyzq` | CCTV 风云足球 | shd |
| `cctvgeqwq` | CCTV 高尔夫·网球 | shd |
| `cctvnxss` | CCTV 女性时尚 | shd |
| `cctvyswhjp` | CCTV 央视文化精品 | shd |
| `cctvystq` | CCTV 央视台球 | shd |
| `cctvdszn` | CCTV 电视指南 | shd |
| `cctvwsjk` | CCTV 卫生健康 | shd |

**地方卫视：**

| ID | 频道名称 | 画质 |
|---|---|---|
| `bjws` | 北京卫视 | fhd |
| `jsws` | 江苏卫视 | fhd |
| `dfws` | 东方卫视 | fhd |
| `zjws` | 浙江卫视 | fhd |
| `hnws` | 湖南卫视 | fhd |
| `hbws` | 湖北卫视 | fhd |
| `gdws` | 广东卫视 | fhd |
| `gxws` | 广西卫视 | fhd |
| `hljws` | 黑龙江卫视 | fhd |
| `hnws2` | 海南卫视 | fhd |
| `cqws` | 重庆卫视 | fhd |
| `szws` | 深圳卫视 | fhd |
| `scws` | 四川卫视 | fhd |
| `henanws` | 河南卫视 | fhd |
| `fjdnhz` | 福建东南卫视 | fhd |
| `gzhws` | 贵州卫视 | fhd |
| `jxws` | 江西卫视 | fhd |
| `lnws` | 辽宁卫视 | fhd |
| `ahws` | 安徽卫视 | fhd |
| `hbws2` | 河北卫视 | fhd |
| `sdws` | 山东卫视 | fhd |
| `tjws` | 天津卫视 | fhd |
| `jlws` | 吉林卫视 | fhd |
| `shanxiws` | 陕西卫视 | fhd |
| `nxws` | 宁夏卫视 | fhd |
| `nmgws` | 内蒙古卫视 | fhd |
| `ynws` | 云南卫视 | fhd |
| `shanxiws2` | 山西卫视 | fhd |
| `qhws` | 青海卫视 | fhd |
| `xzws` | 西藏卫视 | fhd |
| `cetv1` | 中国教育电视台1 | fhd |
| `gxpd` | 国学频道 | fhd |
| `xjws` | 新疆卫视 | fhd |

</details>

---

## 部署方案一：Wasmer Edge（推荐）

Wasmer Edge 部署在边缘节点，全球访问延迟低，免费额度适合个人项目。

### 前置条件

- [Wasmer CLI](https://wasmer.io/) 已安装
- Wasmer 账号（在 [wasmer.io](https://wasmer.io) 注册）
- Git

### 安装 Wasmer

```bash
curl https://get.wasmer.io -sSfL | sh
source ~/.bashrc   # 或 source ~/.zshrc
```

### 配置文件

**`wasmer.toml`：**

```toml
[package]
name = "your-username/cctv-play-proxy"
version = "0.1.0"
description = "CCTV Live Stream Proxy"

[[module]]
name = "php"
source = "php-cgi.wasm"
abi = "wasi"

[module.interfaces]
wasi = "0.0.0-unstable"
```

**`app.toml`：**

```toml
[application]
name = "cctv-play-proxy"

[[application.http]]
command = "php-cgi"
args = ["/app/app/index.php"]

[application.http.environment]
PHP_INI_SCAN_DIR = "/app/config"
```

**`config/php.ini`：**

```ini
[PHP]
display_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
default_charset = "UTF-8"
allow_url_fopen = On
max_execution_time = 30
default_socket_timeout = 15
date.timezone = Asia/Shanghai
extension = curl
```

### 部署步骤

```bash
# 1. 克隆项目
git clone https://github.com/your-username/cctv-play-proxy.git
cd cctv-play-proxy

# 2. 登录 Wasmer
wasmer login

# 3. 本地测试（可选）
wasmer run . --net
# 访问 http://localhost:8080/?id=cctv1

# 4. 部署到 Wasmer Edge
wasmer deploy

# 5. 按提示选择域名，部署完成后获得访问地址
# 例如：https://cctv-play-proxy.wasmer.app
```

### 32 位 PHP WASM 适配说明

Wasmer 的 PHP WASM 运行时为 **32 位环境**（`PHP_INT_SIZE = 4`，`PHP_INT_MAX = 2147483647`）。以下关键代码已做适配：

| 模块 | 适配内容 |
|---|---|
| GUID 生成 | 使用 `bin2hex(random_bytes(16))` 替代 `mt_rand()` 大数值 |
| TEA 加解密 | 使用 `[hi16, lo16]` 双字对算术，避免 32 位整数溢出 |
| 签名计算 | 使用 16 位分解乘法，避免中间结果超出 `PHP_INT_MAX` |
| 错误报告 | 屏蔽 `E_DEPRECATED`（隐式 float-to-int 转换警告） |

如果在 64 位 PHP 环境中运行此代码，完全兼容，无需修改。

### 更新部署

```bash
# 修改代码后
git add .
git commit -m "update: 描述修改内容"
git push

# 重新部署
wasmer deploy
```

---

## 部署方案二：Docker

适用于需要自定义 `.so` PHP 扩展、已有服务器、或需要更多运行时控制的场景。

### 前置条件

- Docker 已安装
- （可选）Docker Compose

### Dockerfile

```dockerfile
FROM php:8.2-cli-alpine

# 安装系统依赖
RUN apk add --no-cache \
    curl \
    ca-certificates \
    tzdata

# 设置时区
ENV TZ=Asia/Shanghai

# 创建扩展目录（如需要自定义 .so 扩展）
RUN mkdir -p /usr/local/lib/php/extensions/custom

# 复制自定义扩展（如需要）
# COPY app/ext/custom.so /usr/local/lib/php/extensions/custom/custom.so

# 复制 PHP 配置
COPY config/php.ini /usr/local/etc/php/php.ini

# 追加扩展目录配置（如使用自定义扩展）
# RUN echo "extension_dir = /usr/local/lib/php/extensions/custom" >> /usr/local/etc/php/php.ini

# 复制应用代码
COPY app/ /var/www/html/

WORKDIR /var/www/html

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
```

### Docker Compose（可选）

**`docker-compose.yml`：**

```yaml
version: '3.8'

services:
  cctv-proxy:
    build: .
    container_name: cctv-proxy
    ports:
      - "8080:8080"
    environment:
      - TZ=Asia/Shanghai
    restart: unless-stopped
```

### 构建与运行

```bash
# 使用 Docker
docker build -t cctv-proxy .
docker run -d -p 8080:8080 --name cctv-proxy cctv-proxy

# 使用 Docker Compose
docker-compose up -d

# 访问
curl "http://localhost:8080/?id=cctv1"
curl "http://localhost:8080/channels"
```

### 调试

```bash
# 查看日志
docker logs -f cctv-proxy

# 进入容器
docker exec -it cctv-proxy sh

# 容器内检查扩展
php -m
php -r "var_dump(extension_loaded('curl'));"

# 检查 .so 依赖（在宿主机执行）
docker exec cctv-proxy ldd /usr/local/lib/php/extensions/custom/custom.so
```

### 使用自定义 `.so` 扩展

取消 Dockerfile 中注释的三行：

```dockerfile
COPY app/ext/custom.so /usr/local/lib/php/extensions/custom/custom.so
RUN echo "extension_dir = /usr/local/lib/php/extensions/custom" >> /usr/local/etc/php/php.ini
```

同时在 `config/php.ini` 中添加：

```ini
extension=custom.so
```

重新构建：

```bash
docker build -t cctv-proxy .
docker run -d -p 8080:8080 --name cctv-proxy cctv-proxy
```

---

## 部署方案三：传统 VPS

适用于想要最简单直接的部署方式。

### 环境要求

- PHP 8.1+（推荐 8.2）
- PHP 扩展：curl
- Nginx 或 Apache（可选，用于反向代理）

### 部署步骤

```bash
# 1. 安装 PHP
sudo apt update
sudo apt install -y php-cli php-curl

# 2. 部署代码
sudo mkdir -p /var/www/cctv
sudo cp -r app/* /var/www/cctv/

# 3. 如需自定义扩展
sudo cp app/ext/custom.so /usr/lib/php/$(php -r 'echo PHP_API_VERSION;')/
sudo bash -c 'echo "extension=custom.so" >> /etc/php/8.2/cli/php.ini'

# 4. 验证扩展
php -m | grep custom

# 5. 启动内置服务器（测试用）
cd /var/www/cctv && php -S 0.0.0.0:8080 index.php

# 6. 生产环境建议使用 Nginx + PHP-FPM
```

### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/cctv;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 部署方案对比

| 特性 | Wasmer Edge | Docker | VPS |
|---|---|---|---|
| 自定义 `.so` 扩展 | 不支持 | 支持 | 支持 |
| 全球边缘加速 | 支持 | 不支持 | 不支持 |
| 免费额度 | 有 | 取决于宿主 | 无 |
| 部署难度 | 低 | 中 | 低 |
| 运行环境 | 32 位 WASM | 64 位 Linux | 64 位 Linux |
| 需要改代码适配 | 需要（已内置兼容） | 不需要 | 不需要 |

> 如果你的项目**必须使用自定义 `.so` 扩展**，请选择 Docker 或 VPS 方案。Wasmer 方案的代码已内置 32 位兼容，可直接使用。

---

## 技术架构

```
用户请求
    │
    ▼
index.php（路由层）
    ├── /           → yspios.php（播放代理）
    ├── /yspios     → yspios.php（播放代理）
    └── /channels   → channels.php（频道列表 API）
                           │
                           ▼
                    yspios.php
                           │
                  ┌────────┼────────┐
                  ▼        ▼        ▼
              CKeyManager  缓存层   M3U8 代理
              (TEA加密)    (Cookie)  (TS路径补全)
                  │
                  ▼
          bkliveinfo.ysp.cctv.cn
          (央视直播 API)
```

### 加密流程

```
频道ID + 时间戳 + GUID
         │
         ▼
   buildPacket()  ──→  二进制数据包
         │
         ▼
   calcSignature()  ──→  签名校验
         │
         ▼
   oiSymmetryEncrypt2()  ──→  TEA CBC 加密
         │
         ▼
   xorArray()  ──→  XOR 混淆
         │
         ▼
   customEncode()  ──→  自定义 Base64
         │
         ▼
      "--01" + encoded  ──→  cKey
         │
         ▼
   央视 API 请求（附带 cKey）
```

---

## 添加新频道

编辑 `app/channels.php`，在 `$channels` 数组中新增一行：

```php
'新频道id' => ['cnlid', 'livepid', '画质', '频道名称'],
```

**参数说明：**

| 字段 | 说明 | 示例 |
|---|---|---|
| 键名 | 频道 ID，用于 `?id=` 参数 | `cctv1` |
| `cnlid` | 央视频道 ID | `2024078201` |
| `livepid` | 直播 PID | `600001859` |
| 画质 | `fhd`（高清）或 `shd`（标清） | `fhd` |
| 频道名称 | 显示名称 | `CCTV-1 综合` |

添加后无需修改其他文件，频道列表 API 和播放功能自动生效。

---

## 常见问题

### Wasmer 部署后显示 404

检查 `app.toml` 中 `args` 路径是否正确。入口文件应为 `/app/app/index.php`。

### Wasmer 部署后显示 "获取播放地址失败"

确认是否为 32 位 PHP 环境问题。访问诊断接口确认：

```bash
curl "https://your-domain.wasmer.app/"
# 如果返回 M3U8 内容则正常
# 如果返回错误信息，查看服务器返回的 iretcode 和 errinfo
```

### 回看功能不工作

确保 `playseek` 参数格式正确：`YYYYMMDDHHMMSS-YYYYMMDDHHMMSS`（开始-结束，共 28 个数字字符）。

### Docker 构建后扩展加载失败

```bash
# 进入容器检查
docker exec -it cctv-proxy sh

# 检查扩展文件是否存在
ls -la /usr/local/lib/php/extensions/custom/

# 检查依赖
ldd /usr/local/lib/php/extensions/custom/custom.so

# 检查 PHP 是否识别
php -m
```

---

## 许可声明

本项目仅供学习与研究使用。

## 友情赞助商


1. [Cloudcone VPS](https://app.cloudcone.com/?ref=12850)

2. [![This Website is Powered by DigitalPlat FreeDomain Get a free domain from DigitalPlat.](https://img.shields.io/badge/DigitalPlat-Get%20a%20free%20domain%20from%20DigitalPlat.-2563eb?style=flat-square&logo=databricks&logoColor=ffffff)](https://dash.domain.digitalplat.org/signup?ref=3lT6CU6HGe)


