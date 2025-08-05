#!/bin/bash

# SecureVerify Setup Script
# This script automates the setup process for the SecureVerify project

echo "🚀 SecureVerify Setup Script"
echo "=============================="

# Check if running on Windows
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" ]]; then
    echo "⚠️  This script is designed for Linux/Mac. For Windows, please follow the manual setup instructions in README.md"
    exit 1
fi

# Check prerequisites
echo "📋 Checking prerequisites..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP version: $PHP_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi
echo "✅ Composer is installed"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18.0 or higher."
    exit 1
fi

NODE_VERSION=$(node --version)
echo "✅ Node.js version: $NODE_VERSION"

# Check npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed."
    exit 1
fi
echo "✅ npm is installed"

# Check Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is not installed. Please install Python 3.8 or higher."
    exit 1
fi

PYTHON_VERSION=$(python3 --version)
echo "✅ $PYTHON_VERSION"

# Check Docker
if ! command -v docker &> /dev/null; then
    echo "⚠️  Docker is not installed. FastAPI service will be run locally."
    DOCKER_AVAILABLE=false
else
    echo "✅ Docker is installed"
    DOCKER_AVAILABLE=true
fi

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL is not installed. Please install MySQL 8.0 or higher."
    echo "   You can use XAMPP, WAMP, or MAMP for local development."
fi

echo ""
echo "🔧 Starting setup..."

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env
    php artisan key:generate
    echo "✅ .env file created and configured"
else
    echo "✅ .env file already exists"
fi

# Build frontend assets
echo "🏗️  Building frontend assets..."
npm run build

# Setup FastAPI service
echo "🐍 Setting up FastAPI service..."

if [ "$DOCKER_AVAILABLE" = true ]; then
    echo "🐳 Using Docker for FastAPI service..."
    cd fastapi_service
    docker-compose up -d
    cd ..
    echo "✅ FastAPI service started with Docker"
else
    echo "🐍 Setting up FastAPI service locally..."
    cd fastapi_service
    
    # Create virtual environment
    if [ ! -d "venv" ]; then
        python3 -m venv venv
    fi
    
    # Activate virtual environment
    source venv/bin/activate
    
    # Install Python dependencies
    pip install -r requirements.txt
    
    echo "✅ FastAPI dependencies installed"
    echo "📝 To start FastAPI service, run:"
    echo "   cd fastapi_service"
    echo "   source venv/bin/activate"
    echo "   uvicorn main:app --host 0.0.0.0 --port 8001 --reload"
    
    cd ..
fi

# Database setup
echo "🗄️  Database setup..."
echo "📝 Please ensure MySQL is running and create a database named 'Sarvone'"
echo "   Then run the following commands:"
echo "   php artisan migrate"
echo "   php artisan db:seed"

# Blockchain setup
echo "⛓️  Blockchain setup..."
echo "📝 To deploy smart contracts, run:"
echo "   npx hardhat compile"
echo "   npx hardhat run scripts/deploy.cjs --network amoy"

echo ""
echo "🎉 Setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your .env file with API keys and database credentials"
echo "2. Create MySQL database 'Sarvone'"
echo "3. Run: php artisan migrate && php artisan db:seed"
echo "4. Start Laravel: php artisan serve"
echo "5. Start FastAPI (if not using Docker): cd fastapi_service && uvicorn main:app --host 0.0.0.0 --port 8001 --reload"
echo "6. Access the application at: http://localhost:8000"
echo ""
echo "📚 For detailed instructions, see README.md" 