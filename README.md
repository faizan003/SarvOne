# 🚀 SarvOne - India's Digital Identity & Governance Platform

**SarvOne** is a revolutionary end-to-end digital identity and governance platform that transforms how Indian citizens interact with government services, financial institutions, and educational organizations. Built with Laravel, FastAPI, Tailwind CSS, and blockchain technology.

## 🎯 Features

- **W3C Compliant Verifiable Credentials** - Standards-based digital credentials
- **Blockchain-Powered Security** - Polygon blockchain integration with IPFS storage
- **Government Scheme Automation** - AI-powered eligibility matching and notifications
- **Privacy-First Data Control** - Granular access control and user consent management
- **Mobile-First Design** - Responsive interface for all devices
- **Multi-Organization Support** - 5 organization types (UIDAI, Government, Banks, Schools, Land Property)

## 🏗️ Architecture

```
SarvOne/
├── Laravel Backend (PHP 8.2+)          # Main application logic
├── FastAPI Service (Python 3.8+)       # Blockchain & AI services
├── Frontend (Tailwind CSS + Vite)      # User interface
├── Smart Contracts (Solidity)          # Blockchain logic
└── Docker Services                     # Containerized deployment
```

## 📋 Prerequisites

### System Requirements
- **PHP**: 8.2 or higher
- **Node.js**: 18.0 or higher
- **Python**: 3.8 or higher
- **MySQL**: 8.0 or higher
- **Docker**: 20.10 or higher
- **Composer**: Latest version
- **Git**: Latest version

### Required Software
- **XAMPP/WAMP/MAMP** (for local development)
- **Visual Studio Code** (recommended IDE)
- **Postman** (for API testing)

## 🚀 Installation Guide

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/SarvOne.git
cd SarvOne
```

### 2. Laravel Backend Setup

#### Install PHP Dependencies
```bash
composer install
```

#### Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

#### Database Setup
```bash
# Create MySQL database named 'Sarvone'
mysql -u root -p
CREATE DATABASE Sarvone;
exit;

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

#### Install Node.js Dependencies
```bash
npm install
```

#### Build Frontend Assets
```bash
npm run build
```

### 3. FastAPI Service Setup

#### Option A: Docker (Recommended)
```bash
cd fastapi_service
docker-compose up -d
```

#### Option B: Local Python Setup
```bash
cd fastapi_service

# Create virtual environment
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Run FastAPI service
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

### 4. Blockchain Setup

#### Install Hardhat Dependencies
```bash
npm install -g hardhat
npm install
```

#### Deploy Smart Contracts
```bash
# Compile contracts
npx hardhat compile

# Deploy to Polygon Amoy testnet
npx hardhat run scripts/deploy.cjs --network amoy
```

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Laravel Configuration
APP_NAME="Sarvone"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Sarvone
DB_USERNAME=root
DB_PASSWORD=your_password

# Blockchain Configuration
BLOCKCHAIN_NETWORK=Amoy
ADMIN_PRIVATE_KEY=your_private_key
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology
CONTRACT_ADDRESS=your_deployed_contract_address

# FastAPI Service
BLOCKCHAIN_SERVICE_URL=http://localhost:8001

# Twilio SMS Configuration
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_PHONE_NUMBER=your_twilio_number

# Pinata IPFS Configuration
PINATA_API_KEY=your_pinata_key
PINATA_SECRET_KEY=your_pinata_secret
PINATA_JWT_KEY=your_pinata_jwt
```

### Required API Keys

1. **Twilio Account** - For SMS notifications
2. **Pinata Account** - For IPFS storage
3. **PolygonScan API Key** - For contract verification
4. **QuickNode API** - For blockchain RPC

## 🏃‍♂️ Running the Application

### 1. Start Laravel Backend
```bash
# Development server
php artisan serve

# Or with queue worker
php artisan serve & php artisan queue:work
```

### 2. Start FastAPI Service
```bash
# If using Docker
cd fastapi_service
docker-compose up -d

# If using local Python
cd fastapi_service
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

### 3. Start Frontend Development
```bash
# In a new terminal
npm run dev
```

### 4. Access the Application
- **Main Application**: http://localhost:8000
- **FastAPI Service**: http://localhost:8001
- **API Documentation**: http://localhost:8001/docs

## 🧪 Testing

### Run Laravel Tests
```bash
php artisan test
```

### Run Smart Contract Tests
```bash
npx hardhat test
```

### Test API Endpoints
```bash
# Test FastAPI health check
curl http://localhost:8001/health

# Test Laravel API
curl http://localhost:8000/api/government-schemes
```

## 📱 Usage Guide

### For Citizens
1. **Register** at http://localhost:8000/register
2. **Complete Aadhaar Verification** (simulated)
3. **View Dashboard** with your digital credentials
4. **Check Government Schemes** in Opportunity Hub
5. **Manage Data Access** in Data Access Control

### For Organizations
1. **Register Organization** at http://localhost:8000/organization/register
2. **Wait for Government Approval**
3. **Login** and access organization dashboard
4. **Issue Verifiable Credentials** to users
5. **Verify Credentials** from other organizations

### For Government Admins
1. **Access Admin Panel** at http://localhost:8000/government/login
2. **Review Organization Applications**
3. **Approve/Reject Organizations**
4. **Monitor System Usage**

## 🔧 Development

### Project Structure
```
SarvOne/
├── app/
│   ├── Http/Controllers/     # Laravel controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── ...
├── resources/views/         # Blade templates
├── routes/                  # Route definitions
├── database/               # Migrations & seeders
├── contracts/              # Smart contracts
├── fastapi_service/        # FastAPI application
├── public/                 # Public assets
└── config/                 # Configuration files
```

### Key Components

#### Laravel Backend
- **DashboardController**: Main user dashboard and API endpoints
- **OrganizationController**: Organization management and VC operations
- **CredentialService**: VC issuance and verification logic
- **IPFSService**: IPFS integration for credential storage
- **BlockchainService**: Blockchain interaction

#### FastAPI Service
- **main.py**: Main FastAPI application
- **face_recognition.py**: AI-powered face recognition
- **security.py**: Authentication and authorization
- **video_processor.py**: Video processing utilities

#### Smart Contracts
- **SarvOneEnhanced.sol**: Main contract for credential management
- **CredentialRegistry.sol**: Credential registry and verification

### Database Schema
- **users**: User accounts and profiles
- **organizations**: Organization details and approvals
- **verifiable_credentials**: Digital credentials storage
- **government_schemes**: Government scheme definitions
- **user_data_access_preferences**: User privacy preferences
- **access_logs**: Audit trail for data access

## 🚀 Deployment

### Production Deployment

#### 1. Laravel Deployment
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. FastAPI Deployment
```bash
# Build Docker image
cd fastapi_service
docker build -t SarvOne-fastapi .

# Run with production settings
docker run -d -p 8001:8001 SarvOne-fastapi
```

#### 3. Database Migration
```bash
php artisan migrate --force
```

### Docker Deployment
```bash
# Build and run all services
docker-compose -f docker-compose.yml up -d
```

## 🔒 Security Features

- **W3C Verifiable Credentials**: Standards-compliant digital credentials
- **Blockchain Verification**: Immutable credential anchoring
- **IPFS Storage**: Decentralized, tamper-proof storage
- **Multi-Factor Authentication**: Enhanced security
- **Audit Trails**: Complete access logging
- **Privacy Controls**: Granular data access permissions

## 📊 Monitoring & Logs

### Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### FastAPI Logs
```bash
# If using Docker
docker logs SarvOne-fastapi

# If using local Python
tail -f fastapi_service/logs/app.log
```

### Blockchain Monitoring
- **PolygonScan**: https://amoy.polygonscan.com
- **Contract Address**: Check your deployed contract

## 🐛 Troubleshooting

### Common Issues

#### 1. FastAPI Service Not Starting
```bash
# Check if port 8001 is available
netstat -an | grep 8001

# Restart Docker container
docker-compose restart
```

#### 2. Database Connection Issues
```bash
# Check MySQL service
sudo service mysql status

# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

#### 3. Blockchain Connection Issues
```bash
# Test RPC connection
curl -X POST -H "Content-Type: application/json" \
  --data '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}' \
  https://rpc-amoy.polygon.technology
```

#### 4. IPFS Upload Issues
```bash
# Test Pinata connection
curl -X GET "https://api.pinata.cloud/data/testAuthentication" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Debug Commands
```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Check Laravel status
php artisan about

# Test blockchain connection
npx hardhat run scripts/check-balance.cjs --network amoy
```

## 📚 API Documentation

### Laravel API Endpoints
- **GET** `/api/my-vcs` - Get user's verifiable credentials
- **GET** `/api/government-schemes` - Get available government schemes
- **POST** `/api/verify-vc-blockchain` - Verify credential on blockchain

### FastAPI Endpoints
- **GET** `/health` - Service health check
- **POST** `/issue_vc` - Issue verifiable credential
- **POST** `/verify_vc` - Verify credential
- **POST** `/face_verify` - Face recognition verification

### Organization API
- **POST** `/organization/api/issue-credential` - Issue credential
- **POST** `/organization/api/verify-credential` - Verify credential
- **GET** `/organization/api/issued-credentials` - Get issued credentials

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Create an issue on GitHub
- **Email**: faizankhanm63611@gmail.com

## 🎯 Roadmap

- [ ] AI-powered fraud detection
- [ ] Cross-chain compatibility
- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Integration with DigiLocker
- [ ] Multi-language support

---

**Built with ❤️ for India's Digital Transformation**

*SarvOne - Empowering Every Indian Citizen with Digital Identity*
