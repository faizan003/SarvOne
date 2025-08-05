const { ethers } = require("hardhat");
const fs = require("fs");
const path = require("path");

async function main() {
  console.log("🚀 Starting CredentialRegistry deployment...");
  
  // Get the deployer account
  const [deployer] = await ethers.getSigners();
  console.log("📋 Deploying with account:", deployer.address);
  
  // Check balance
  const balance = await deployer.provider.getBalance(deployer.address);
  console.log("💰 Account balance:", ethers.formatEther(balance), "MATIC");
  
  if (balance < ethers.parseEther("0.01")) {
    console.log("⚠️  Warning: Low balance. You may need more MATIC for deployment.");
  }
  
  // Get the contract factory
  const CredentialRegistry = await ethers.getContractFactory("CredentialRegistry");
  
  console.log("📦 Deploying CredentialRegistry contract...");
  
  try {
    // Deploy the contract with automatic gas estimation
    const credentialRegistry = await CredentialRegistry.deploy();
    
    // Wait for deployment
    console.log("⏳ Waiting for deployment to complete...");
    await credentialRegistry.waitForDeployment();
    
    const contractAddress = await credentialRegistry.getAddress();
    console.log("✅ CredentialRegistry deployed to:", contractAddress);
    
    // Get network information
    const network = await ethers.provider.getNetwork();
    console.log("🌐 Network:", network.name, "Chain ID:", network.chainId.toString());
    
    // Get deployment transaction
    const deploymentTx = credentialRegistry.deploymentTransaction();
    console.log("📄 Deployment transaction:", deploymentTx.hash);
    
    // Wait for a few confirmations
    console.log("⏳ Waiting for confirmations...");
    await deploymentTx.wait(3);
    
    // Get contract stats
    const stats = await credentialRegistry.getStats();
    console.log("📊 Contract Stats:");
    console.log("   - Total Credentials:", stats._totalCredentials.toString());
    console.log("   - Total Issuers:", stats._totalIssuers.toString());
    console.log("   - Owner:", stats._owner);
    
    // Save deployment info
    const deploymentInfo = {
      network: network.name,
      chainId: network.chainId.toString(),
      contractAddress: contractAddress,
      deploymentTx: deploymentTx.hash,
      deployer: deployer.address,
      timestamp: new Date().toISOString(),
      gasUsed: deploymentTx.gasLimit?.toString() || "N/A",
      gasPrice: deploymentTx.gasPrice?.toString() || "N/A"
    };
    
    // Create deployment directory if it doesn't exist
    const deploymentDir = path.join(__dirname, "..", "deployments");
    if (!fs.existsSync(deploymentDir)) {
      fs.mkdirSync(deploymentDir, { recursive: true });
    }
    
    // Save deployment info to file
    const deploymentFile = path.join(deploymentDir, `${network.name}-${network.chainId}.json`);
    fs.writeFileSync(deploymentFile, JSON.stringify(deploymentInfo, null, 2));
    
    console.log("💾 Deployment info saved to:", deploymentFile);
    
    // Generate .env configuration
    console.log("\n🔧 Add these to your .env file:");
    console.log("=====================================");
    console.log(`POLYGON_CONTRACT_ADDRESS=${contractAddress}`);
    console.log(`POLYGON_RPC_URL=https://rpc-amoy.polygon.technology`);
    console.log(`POLYGON_CHAIN_ID=${network.chainId}`);
    console.log(`POLYGON_EXPLORER_URL=https://amoy.polygonscan.com`);
    console.log("=====================================");
    
    // Generate verification command
    console.log("\n🔍 To verify the contract, run:");
    console.log(`npx hardhat verify --network amoy ${contractAddress}`);
    
    console.log("\n✨ Deployment completed successfully!");
    console.log(`🔗 View on Explorer: https://amoy.polygonscan.com/address/${contractAddress}`);
    
  } catch (error) {
    console.error("❌ Deployment failed with error:", error.message);
    
    if (error.code === 'CALL_EXCEPTION') {
      console.log("💡 This might be due to:");
      console.log("   - Constructor validation failed");
      console.log("   - Gas limit too high/low");
      console.log("   - Network congestion");
      console.log("   - Insufficient balance");
    }
    
    throw error;
  }
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error("❌ Deployment failed:", error);
    process.exit(1);
  }); 