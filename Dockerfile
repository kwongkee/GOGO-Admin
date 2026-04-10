# GOGO-Admin Dockerfile
# ThinkPHP 5.1 + PHP 7.2 + Nginx

FROM php:7.2-fpm-alpine

# 安装系统依赖
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    libzip-dev \
    autoconf \
    gcc \
    g++ \
    make \
    linux-headers \
    zip \
    unzip \
    git \
    curl

# 安装PHP扩展
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    opcache \
    bcmath \
    pcntl

# 配置Nginx
RUN echo 'server {' > /etc/nginx/http.d/default.conf \
    && echo '    listen 80;' >> /etc/nginx/http.d/default.conf \
    && echo '    server_name _;' >> /etc/nginx/http.d/default.conf \
    && echo '    root /var/www/html;' >> /etc/nginx/http.d/default.conf \
    && echo '    index index.php index.html;' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location / {' >> /etc/nginx/http.d/default.conf \
    && echo '        if (!-e $request_filename) {' >> /etc/nginx/http.d/default.conf \
    && echo '            rewrite ^/(.*)$ /index.php/$1 last;' >> /etc/nginx/http.d/default.conf \
    && echo '        }' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location ~ \\.php {' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_index index.php;' >> /etc/nginx/http.d/default.conf \
    && echo '        include fastcgi_params;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_split_path_info ^(.+\\.php)(.*)$;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_path_info PATH_INFO;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location ~ /\\.ht {' >> /etc/nginx/http.d/default.conf \
    && echo '        deny all;' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '}' >> /etc/nginx/http.d/default.conf

# 配置Supervisor
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf \
    && echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:php-fpm]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=php-fpm -F' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:nginx]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=nginx -g "daemon off;"' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/runtime 2>/dev/null || true

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
