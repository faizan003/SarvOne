# Polygon Amoy Testnet Deployment Guide

## 🚀 Quick Start

This guide will help you deploy the CredentialRegistry smart contract to Polygon Amoy testnet and configure SecureVerify to use it.

## 📋 Prerequisites

### 1. **Wallet Setup**
- Install MetaMask or any Web3 wallet
- Add Polygon Amoy testnet to your wallet

### 2. **Get Test MATIC**
- Visit [Polygon Faucet](https://faucet.polygon.technology/)
- Request test MATIC for Amoy testnet
- You need at least 0.1 MATIC for deployment

### 3. **Required Information**
You'll need to collect these details:

```env
# Your wallet private key (NEVER share this!)
BLOCKCHAIN_PRIVATE_KEY=0x1234567890abcdef...

# Optional: PolygonScan API key for contract verification
POLYGONSCAN_API_KEY=your_api_key_here
```

## 🔧 Step-by-Step Deployment

### Step 1: Install Dependencies

```bash
# Install Hardhat and dependencies
npm install

# Or if using yarn
yarn install
```

### Step 2: Configure Environment

Create or update your `.env` file:

```env
# Blockchain Configuration
BLOCKCHAIN_NETWORK=polygon
BLOCKCHAIN_PRIVATE_KEY=0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef
BLOCKCHAIN_TIMEOUT=60

# Polygon Amoy Testnet Configuration
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology
POLYGON_CHAIN_ID=80002
POLYGON_EXPLORER_URL=https://amoy.polygonscan.com
POLYGON_CONTRACT_ADDRESS=  # Will be filled after deployment
POLYGON_GAS_LIMIT=200000
POLYGON_GAS_PRICE=30

# Optional: For contract verification
POLYGONSCAN_API_KEY=your_api_key_here

# IPFS Configuration (if using local node)
IPFS_GATEWAY_URL=https://ipfs.io/ipfs/
IPFS_API_URL=http://localhost:5001/api/v0/
IPFS_TIMEOUT=30

# SecureVerify Configuration
SECUREVERIFY_DID_PREFIX=did:secureverify:
SECUREVERIFY_VC_CONTEXT=https://secureverify.in/credentials/v1
SECUREVERIFY_TIMEZONE=Asia/Kolkata
```

### Step 3: Get Your Private Key

**⚠️ SECURITY WARNING: Never share your private key!**

#### From MetaMask:
1. Open MetaMask
2. Click on your account → Account Details
3. Click "Export Private Key"
4. Enter your password
5. Copy the private key (starts with 0x)

#### From Other Wallets:
- Follow your wallet's documentation to export private key
- Ensure it's in hexadecimal format starting with 0x

### Step 4: Get Test MATIC

1. Visit [Polygon Faucet](https://faucet.polygon.technology/)
2. Select "Amoy Testnet"
3. Enter your wallet address
4. Request test MATIC
5. Wait for confirmation (usually 1-2 minutes)

### Step 5: Add Polygon Amoy to MetaMask

If not already added:

```
Network Name: Polygon Amoy Testnet
RPC URL: https://rpc-amoy.polygon.technology
Chain ID: 80002
Currency Symbol: MATIC
Block Explorer: https://amoy.polygonscan.com
```

### Step 6: Compile the Contract

```bash
# Compile the smart contract
npm run compile

# Or using npx directly
npx hardhat compile
```

### Step 7: Deploy to Amoy Testnet

```bash
# Deploy to Amoy testnet
npm run deploy:amoy

# Or using npx directly
npx hardhat run scripts/deploy.js --network amoy
```

### Step 8: Verify Deployment

After successful deployment, you'll see output like:

```
🚀 Starting CredentialRegistry deployment...
📋 Deploying with account: 0x1234567890123456789012345678901234567890
💰 Account balance: 0.5 MATIC
📦 Deploying CredentialRegistry contract...
✅ CredentialRegistry deployed to: 0xABCDEF1234567890ABCDEF1234567890ABCDEF12
🌐 Network: amoy Chain ID: 80002
📄 Deployment transaction: 0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef12
⏳ Waiting for confirmations...
📊 Contract Stats:
   - Total Credentials: 0
   - Total Issuers: 0
   - Owner: 0x1234567890123456789012345678901234567890

🔧 Add these to your .env file:
=====================================
POLYGON_CONTRACT_ADDRESS=0xABCDEF1234567890ABCDEF1234567890ABCDEF12
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology
POLYGON_CHAIN_ID=80002
POLYGON_EXPLORER_URL=https://amoy.polygonscan.com
=====================================

🔍 To verify the contract, run:
npx hardhat verify --network amoy 0xABCDEF1234567890ABCDEF1234567890ABCDEF12

✨ Deployment completed successfully!
🔗 View on Explorer: https://amoy.polygonscan.com/address/0xABCDEF1234567890ABCDEF1234567890ABCDEF12
```

### Step 9: Update Your .env File

Copy the contract address from the deployment output and add it to your `.env`:

```env
POLYGON_CONTRACT_ADDRESS=0xABCDEF1234567890ABCDEF1234567890ABCDEF12
```

### Step 10: Verify Contract (Optional)

```bash
# Verify the contract on PolygonScan
npx hardhat verify --network amoy YOUR_CONTRACT_ADDRESS

# Example:
npx hardhat verify --network amoy 0xABCDEF1234567890ABCDEF1234567890ABCDEF12
```
npx hardhat verify --network amoy 0x189b9e699C0d86Ce94A4112F60c5420fb72BfF46
## 🔍 Information You Need to Change

### 1. **Environment Variables (.env)**

```env
# REQUIRED: Your wallet's private key
BLOCKCHAIN_PRIVATE_KEY=0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef

# REQUIRED: Contract address (after deployment)
POLYGON_CONTRACT_ADDRESS=0xABCDEF1234567890ABCDEF1234567890ABCDEF12

# OPTIONAL: PolygonScan API key for verification
POLYGONSCAN_API_KEY=your_api_key_here

# OPTIONAL: Custom RPC URL (if you have a dedicated endpoint)
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology

# OPTIONAL: Adjust gas settings if needed
POLYGON_GAS_LIMIT=200000
POLYGON_GAS_PRICE=30
```

### 2. **Database Configuration**

No changes needed - the existing database structure supports blockchain integration.

### 3. **Laravel Configuration**

The `config/services.php` file has been updated to use Amoy testnet by default.

## 🧪 Testing Your Deployment

### 1. **Test Contract Interaction**

```bash
# Start Laravel Tinker
php artisan tinker

# Test blockchain service
$blockchain = app(\App\Services\BlockchainService::class);
$blockchain->isAvailable();

# Test network info
$blockchain->getNetworkInfo();
```

### 2. **Test VC Issuance**

1. Register an organization in SecureVerify
2. Issue a test VC
3. Check the issued VCs dashboard
4. Verify IPFS and blockchain links work

### 3. **Test API Endpoints**

```bash
# Test VC status endpoint
curl -X GET "http://localhost:8000/api/verify/status/urn:uuid:test-vc-id"

# Test blockchain verification
curl -X POST "http://localhost:8000/api/verify/blockchain" \
  -H "Content-Type: application/json" \
  -d '{"credential_hash":"test_hash"}'
```

## 🔧 Troubleshooting

### Common Issues:

1. **"Insufficient funds" error**
   - Get more test MATIC from the faucet
   - Check your wallet balance

2. **"Invalid private key" error**
   - Ensure private key starts with 0x
   - Check for extra spaces or characters

3. **"Network not supported" error**
   - Verify RPC URL is correct
   - Check internet connection

4. **"Contract verification failed"**
   - Ensure you have POLYGONSCAN_API_KEY
   - Wait a few minutes after deployment

### Debug Commands:

```bash
# Check network connection
curl -X POST https://rpc-amoy.polygon.technology \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}'

# Check balance
npx hardhat run scripts/check-balance.js --network amoy

# Test contract compilation
npx hardhat compile --force
```

## 📊 Network Information

### Polygon Amoy Testnet Details:
- **Network Name**: Polygon Amoy Testnet
- **Chain ID**: 80002
- **RPC URL**: https://rpc-amoy.polygon.technology
- **Explorer**: https://amoy.polygonscan.com
- **Faucet**: https://faucet.polygon.technology
- **Currency**: MATIC (testnet)

### Alternative RPC URLs:
- https://rpc-amoy.polygon.technology
- https://polygon-amoy.blockpi.network/v1/rpc/public

## 🚀 Next Steps

After successful deployment:

1. **Register Your Organization** as an issuer in the smart contract
2. **Issue Test VCs** to verify the full flow
3. **Test Public Verification** using the API endpoints
4. **Monitor Gas Usage** and adjust settings if needed
5. **Set up IPFS** for decentralized storage

## 📞 Support

If you encounter issues:

1. Check the deployment logs in `deployments/` folder
2. Verify your `.env` configuration
3. Test with minimal examples
4. Check Laravel logs in `storage/logs/laravel.log`

## 🔐 Security Reminders

- **Never commit private keys** to version control
- **Use environment variables** for sensitive data
- **Test thoroughly** on testnet before mainnet
- **Keep backups** of your deployment information
- **Monitor gas costs** and optimize as needed

---

**Happy Deploying! 🎉**

## Deployment Status

✅ **Successfully Deployed!**

- **Contract Address**: `0x189b9e699C0d86Ce94A4112F60c5420fb72BfF46`
- **Network**: Polygon Amoy Testnet (Chain ID: 80002)
- **Deployment Transaction**: `0xf05dded5bc9f98c4cdfb392369e845663ad8c311b1a5709bf8728129debf13c5`
- **Deployer**: `0x4778eC77AC034d25687fAf8d9457b3f1FC4bB8De`
- **Deployed At**: July 12, 2025, 00:47:58 UTC
- **Gas Used**: 2,285,588
- **Gas Price**: 30 Gwei

🔗 **View on Explorer**: https://amoy.polygonscan.com/address/0x189b9e699C0d86Ce94A4112F60c5420fb72BfF46

## Next Steps

1. **Update your `.env` file** with the contract address:
   ```
   POLYGON_CONTRACT_ADDRESS=0x189b9e699C0d86Ce94A4112F60c5420fb72BfF46
   ```

2. **Test the integration** by issuing a verifiable credential through your Laravel application

3. **Optional**: Get a Polygonscan API key to verify the contract source code:
   ```bash
   npx hardhat verify --network amoy 0x189b9e699C0d86Ce94A4112F60c5420fb72BfF46
   ``` 