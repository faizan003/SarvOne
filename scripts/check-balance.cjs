const { ethers } = require("hardhat");

async function main() {
  console.log("💰 Checking wallet balance...");
  
  // Get the deployer account
  const [deployer] = await ethers.getSigners();
  console.log("📋 Wallet address:", deployer.address);
  
  // Get balance
  const balance = await deployer.provider.getBalance(deployer.address);
  console.log("💵 Balance:", ethers.formatEther(balance), "MATIC");
  
  // Get network information
  const network = await ethers.provider.getNetwork();
  console.log("🌐 Network:", network.name, "Chain ID:", network.chainId.toString());
  
  // Check if balance is sufficient
  const minBalance = ethers.parseEther("0.01");
  if (balance < minBalance) {
    console.log("⚠️  Warning: Balance is low. You may need more MATIC for deployment.");
    console.log("🔗 Get test MATIC from: https://faucet.polygon.technology/");
  } else {
    console.log("✅ Balance is sufficient for deployment.");
  }
  
  // Get latest block number
  const blockNumber = await deployer.provider.getBlockNumber();
  console.log("📦 Latest block:", blockNumber);
  
  // Get gas price
  const gasPrice = await deployer.provider.getFeeData();
  console.log("⛽ Gas price:", ethers.formatUnits(gasPrice.gasPrice, "gwei"), "Gwei");
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error("❌ Error:", error);
    process.exit(1);
  }); 