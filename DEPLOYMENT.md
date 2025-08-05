# 🚀 SecureVerify Deployment Guide

This guide covers deploying SecureVerify to production and staging environments.

## 📋 Prerequisites

### Production Server Requirements
- **OS**: Ubuntu 20.04+ or CentOS 8+
- **CPU**: 4+ cores
- **RAM**: 8GB+ 
- **Storage**: 100GB+ SSD
- **Network**: Stable internet connection

### Required Software
- **Nginx**: Web server
- **PHP 8.2+**: Application runtime
- **MySQL 8.0+**: Database
- **Redis**: Caching and sessions
- **Docker**: Containerization
- **SSL Certificate**: HTTPS support

## 🏗️ Production Deployment

### 1. Server Setup

#### Update System
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip software-properties-common
```

#### Install PHP 8.2
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath
```

#### Install MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

#### Install Nginx
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

#### Install Redis
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### Install Docker
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

### 2. Application Deployment

#### Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/yourusername/secureverify.git
sudo chown -R www-data:www-data secureverify
cd secureverify
```

#### Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

#### Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

#### Configure .env for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=secureverify_prod
DB_USERNAME=secureverify_user
DB_PASSWORD=strong_password_here

# Cache and Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Blockchain (Production)
BLOCKCHAIN_NETWORK=polygon
POLYGON_RPC_URL=https://polygon-rpc.com
CONTRACT_ADDRESS=your_production_contract_address

# FastAPI Service
BLOCKCHAIN_SERVICE_URL=https://api.yourdomain.com

# Twilio (Production)
TWILIO_SID=your_production_sid
TWILIO_AUTH_TOKEN=your_production_token
TWILIO_PHONE_NUMBER=your_production_number
TWILIO_TEST_MODE=false

# Pinata (Production)
PINATA_API_KEY=your_production_key
PINATA_SECRET_KEY=your_production_secret
PINATA_JWT_KEY=your_production_jwt
```

#### Database Setup
```bash
# Create database and user
sudo mysql -u root -p
CREATE DATABASE secureverify_prod;
CREATE USER 'secureverify_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON secureverify_prod.* TO 'secureverify_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed --force
```

#### Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 3. FastAPI Service Deployment

#### Using Docker (Recommended)
```bash
cd fastapi_service
docker build -t secureverify-fastapi .
docker run -d --name fastapi-prod -p 8001:8001 --restart unless-stopped secureverify-fastapi
```

#### Using Systemd Service
```bash
# Create service file
sudo nano /etc/systemd/system/secureverify-fastapi.service
```

```ini
[Unit]
Description=SecureVerify FastAPI Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/secureverify/fastapi_service
Environment=PATH=/var/www/secureverify/fastapi_service/venv/bin
ExecStart=/var/www/secureverify/fastapi_service/venv/bin/uvicorn main:app --host 0.0.0.0 --port 8001
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable secureverify-fastapi
sudo systemctl start secureverify-fastapi
```

### 4. Nginx Configuration

#### Main Application
```bash
sudo nano /etc/nginx/sites-available/secureverify
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Root directory
    root /var/www/secureverify/public;
    index index.php index.html index.htm;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|eot|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Logs
    access_log /var/log/nginx/secureverify_access.log;
    error_log /var/log/nginx/secureverify_error.log;
}
```

#### FastAPI Service
```bash
sudo nano /etc/nginx/sites-available/secureverify-api
```

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;

    # Proxy to FastAPI
    location / {
        proxy_pass http://127.0.0.1:8001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Logs
    access_log /var/log/nginx/secureverify_api_access.log;
    error_log /var/log/nginx/secureverify_api_error.log;
}
```

#### Enable Sites
```bash
sudo ln -s /etc/nginx/sites-available/secureverify /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/secureverify-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. SSL Certificate Setup

#### Install Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

#### Obtain SSL Certificates
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
sudo certbot --nginx -d api.yourdomain.com
```

#### Auto-renewal
```bash
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Queue Worker Setup

#### Create Queue Worker Service
```bash
sudo nano /etc/systemd/system/secureverify-queue.service
```

```ini
[Unit]
Description=SecureVerify Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/secureverify
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable secureverify-queue
sudo systemctl start secureverify-queue
```

### 7. Monitoring and Logs

#### Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/secureverify
```

```
/var/www/secureverify/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

#### Monitor Services
```bash
# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status secureverify-fastapi
sudo systemctl status secureverify-queue

# Monitor logs
sudo tail -f /var/log/nginx/secureverify_error.log
sudo tail -f /var/www/secureverify/storage/logs/laravel.log
```

## 🐳 Docker Deployment

### Docker Compose Setup
```bash
# Create docker-compose.yml
nano docker-compose.yml
```

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    volumes:
      - ./storage:/var/www/html/storage
    restart: unless-stopped

  fastapi:
    build: ./fastapi_service
    ports:
      - "8001:8001"
    environment:
      - PYTHONPATH=/app
    volumes:
      - ./fastapi_service/logs:/app/logs
    restart: unless-stopped

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: secureverify_prod
      MYSQL_USER: secureverify_user
      MYSQL_PASSWORD: strong_password_here
      MYSQL_ROOT_PASSWORD: root_password_here
    volumes:
      - mysql_data:/var/lib/mysql
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
      - fastapi
    restart: unless-stopped

volumes:
  mysql_data:
  redis_data:
```

### Deploy with Docker
```bash
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

## 🔄 CI/CD Pipeline

### GitHub Actions Workflow
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.4
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        script: |
          cd /var/www/secureverify
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo systemctl reload nginx
```

## 🔒 Security Checklist

- [ ] SSL certificates installed and auto-renewing
- [ ] Firewall configured (UFW)
- [ ] Database passwords are strong
- [ ] API keys are secure and rotated
- [ ] File permissions are correct
- [ ] Sensitive files are protected
- [ ] Regular backups are configured
- [ ] Monitoring and alerting setup
- [ ] Rate limiting configured
- [ ] Security headers implemented

## 📊 Performance Optimization

### PHP-FPM Tuning
```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
```

### MySQL Tuning
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 128M
query_cache_type = 1
```

### Redis Tuning
```bash
sudo nano /etc/redis/redis.conf
```

```ini
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/secureverify
sudo chmod -R 755 /var/www/secureverify/storage
sudo chmod -R 755 /var/www/secureverify/bootstrap/cache
```

#### 2. Database Connection Issues
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

#### 3. FastAPI Service Issues
```bash
# Check FastAPI logs
docker logs secureverify-fastapi

# Restart FastAPI service
sudo systemctl restart secureverify-fastapi
```

#### 4. Nginx Issues
```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx status
sudo systemctl status nginx

# View Nginx logs
sudo tail -f /var/log/nginx/error.log
```

## 📈 Monitoring Setup

### Install Monitoring Tools
```bash
# Install htop for system monitoring
sudo apt install -y htop

# Install netdata for real-time monitoring
bash <(curl -Ss https://my-netdata.io/kickstart.sh)
```

### Setup Log Monitoring
```bash
# Install fail2ban for security
sudo apt install -y fail2ban

# Configure fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

**For additional support, refer to the main README.md file or create an issue on GitHub.** 