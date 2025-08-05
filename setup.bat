@echo off
chcp 65001 >nul
echo 🚀 SecureVerify Setup Script for Windows
echo ======================================

echo 📋 Checking prerequisites...

REM Check PHP
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed. Please install PHP 8.2 or higher.
    pause
    exit /b 1
)
echo ✅ PHP is installed

REM Check Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer is not installed. Please install Composer.
    pause
    exit /b 1
)
echo ✅ Composer is installed

REM Check Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js is not installed. Please install Node.js 18.0 or higher.
    pause
    exit /b 1
)
echo ✅ Node.js is installed

REM Check npm
npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ npm is not installed.
    pause
    exit /b 1
)
echo ✅ npm is installed

REM Check Python
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Python is not installed. Please install Python 3.8 or higher.
    pause
    exit /b 1
)
echo ✅ Python is installed

REM Check Docker
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Docker is not installed. FastAPI service will be run locally.
    set DOCKER_AVAILABLE=false
) else (
    echo ✅ Docker is installed
    set DOCKER_AVAILABLE=true
)

echo.
echo 🔧 Starting setup...

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
composer install

REM Install Node.js dependencies
echo 📦 Installing Node.js dependencies...
npm install

REM Create .env file if it doesn't exist
if not exist .env (
    echo ⚙️  Creating .env file...
    copy .env.example .env
    php artisan key:generate
    echo ✅ .env file created and configured
) else (
    echo ✅ .env file already exists
)

REM Build frontend assets
echo 🏗️  Building frontend assets...
npm run build

REM Setup FastAPI service
echo 🐍 Setting up FastAPI service...

if "%DOCKER_AVAILABLE%"=="true" (
    echo 🐳 Using Docker for FastAPI service...
    cd fastapi_service
    docker-compose up -d
    cd ..
    echo ✅ FastAPI service started with Docker
) else (
    echo 🐍 Setting up FastAPI service locally...
    cd fastapi_service
    
    REM Create virtual environment
    if not exist venv (
        python -m venv venv
    )
    
    REM Activate virtual environment and install dependencies
    call venv\Scripts\activate.bat
    pip install -r requirements.txt
    
    echo ✅ FastAPI dependencies installed
    echo 📝 To start FastAPI service, run:
    echo    cd fastapi_service
    echo    venv\Scripts\activate.bat
    echo    uvicorn main:app --host 0.0.0.0 --port 8001 --reload
    
    cd ..
)

REM Database setup
echo 🗄️  Database setup...
echo 📝 Please ensure MySQL is running and create a database named 'Sarvone'
echo    Then run the following commands:
echo    php artisan migrate
echo    php artisan db:seed

REM Blockchain setup
echo ⛓️  Blockchain setup...
echo 📝 To deploy smart contracts, run:
echo    npx hardhat compile
echo    npx hardhat run scripts/deploy.cjs --network amoy

echo.
echo 🎉 Setup completed!
echo.
echo 📋 Next steps:
echo 1. Configure your .env file with API keys and database credentials
echo 2. Create MySQL database 'Sarvone'
echo 3. Run: php artisan migrate ^&^& php artisan db:seed
echo 4. Start Laravel: php artisan serve
echo 5. Start FastAPI (if not using Docker): cd fastapi_service ^&^& uvicorn main:app --host 0.0.0.0 --port 8001 --reload
echo 6. Access the application at: http://localhost:8000
echo.
echo 📚 For detailed instructions, see README.md
pause 