from fastapi import FastAPI, HTTPException, Depends
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field, validator
from web3 import Web3
from web3.exceptions import ContractLogicError, TransactionNotFound
from eth_abi import encode
import os
import json
import logging
from typing import List, Optional
from datetime import datetime
import time
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

# Configure logging
import sys

# Create a stream handler with UTF-8 encoding for console output
console_handler = logging.StreamHandler(sys.stdout)
console_handler.setLevel(logging.INFO)

# Set encoding to UTF-8 if available
if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except:
        pass

# Create file handler
file_handler = logging.FileHandler('blockchain_service.log', encoding='utf-8')
file_handler.setLevel(logging.INFO)

# Create formatter
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
console_handler.setFormatter(formatter)
file_handler.setFormatter(formatter)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    handlers=[file_handler, console_handler]
)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="SarvOne Blockchain Service",
    description="Microservice for SarvOne smart contract operations on Polygon Amoy",
    version="1.0.0"
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure appropriately for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic models
class ApproveOrgRequest(BaseModel):
    orgDID: str = Field(..., min_length=1, description="Organization DID")
    orgAddress: str = Field(..., min_length=42, max_length=42, description="Organization wallet address")
    scopes: List[str] = Field(..., min_items=1, description="List of credential scopes")
    
    @validator('orgAddress')
    def validate_eth_address(cls, v):
        if not v.startswith('0x'):
            raise ValueError('Address must start with 0x')
        try:
            Web3.to_checksum_address(v)
        except ValueError:
            raise ValueError('Invalid Ethereum address format')
        return v
    
    @validator('orgDID')
    def validate_did(cls, v):
        if not v.startswith('did:sarvone:'):
            raise ValueError('DID must start with did:sarvone:')
        return v

class IssueVCRequest(BaseModel):
    user_did: str = Field(..., description="Recipient user DID (will be converted to bytes32)")
    vc_hash: str = Field(..., min_length=64, max_length=66, description="SHA256 hash of the VC JSON")
    vc_type: str = Field(..., description="Type of credential being issued (scope)")
    org_private_key: str = Field(..., min_length=64, description="Organization's Ethereum private key for signing transaction")
    
    @validator('user_did')
    def validate_user_did(cls, v):
        if not v.startswith('did:sarvone:'):
            raise ValueError('User DID must start with did:sarvone:')
        return v
    
    @validator('vc_hash')
    def validate_vc_hash(cls, v):
        # Remove 0x prefix if present
        if v.startswith('0x'):
            v = v[2:]
        # Check if it's valid hex
        try:
            int(v, 16)
        except ValueError:
            raise ValueError('VC hash must be a valid hex string')
        if len(v) != 64:
            raise ValueError('VC hash must be 64 characters (32 bytes)')
        return '0x' + v
    
    @validator('org_private_key')
    def validate_org_private_key(cls, v):
        # Remove 0x prefix if present
        if v.startswith('0x'):
            v = v[2:]
        # Validate it's a 64-character hex string (32 bytes)
        if len(v) != 64:
            raise ValueError('Private key must be 64 hex characters (32 bytes)')
        if not all(c in '0123456789abcdefABCDEF' for c in v):
            raise ValueError('Private key must be a valid hex string')
        return v

class VerifyVCRequest(BaseModel):
    user_did: str = Field(..., description="User DID to check VCs for")
    vc_hash: str = Field(..., description="VC hash to verify")
    
    @validator('user_did')
    def validate_user_did(cls, v):
        if not v.startswith('did:sarvone:'):
            raise ValueError('User DID must start with did:sarvone:')
        return v
    
    @validator('vc_hash')
    def validate_vc_hash(cls, v):
        if v.startswith('0x'):
            v = v[2:]
        try:
            int(v, 16)
        except ValueError:
            raise ValueError('VC hash must be a valid hex string')
        if len(v) != 64:
            raise ValueError('VC hash must be 64 characters (32 bytes)')
        return '0x' + v

class RevokeVCRequest(BaseModel):
    user_did: str = Field(..., description="User DID")
    vc_hash: str = Field(..., description="VC hash to revoke")
    
    @validator('user_did')
    def validate_user_did(cls, v):
        if not v.startswith('did:sarvone:'):
            raise ValueError('User DID must start with did:sarvone:')
        return v
    
    @validator('vc_hash')
    def validate_vc_hash(cls, v):
        if v.startswith('0x'):
            v = v[2:]
        try:
            int(v, 16)
        except ValueError:
            raise ValueError('VC hash must be a valid hex string')
        if len(v) != 64:
            raise ValueError('VC hash must be 64 characters (32 bytes)')
        return '0x' + v

# Response models
class TransactionResponse(BaseModel):
    success: bool
    tx_hash: str
    explorer_url: str
    block_number: Optional[int] = None
    gas_used: Optional[int] = None
    timestamp: str

class OrganizationResponse(BaseModel):
    success: bool
    orgDID: str
    approved: bool
    mainAddress: Optional[str] = None
    scopes: Optional[List[str]] = None
    timestamp: str

class VCResponse(BaseModel):
    success: bool
    vc_hash: str
    issuer_did: str
    recipient_did: str
    credential_type: str
    ipfs_cid: str
    issued_at: int
    is_active: bool
    timestamp: str

class VerifyVCResponse(BaseModel):
    success: bool
    is_valid: bool
    vc_exists: bool
    is_active: bool
    issuer_did: Optional[str] = None
    recipient_did: Optional[str] = None
    credential_type: Optional[str] = None
    issued_at: Optional[int] = None
    timestamp: str

class ErrorResponse(BaseModel):
    success: bool = False
    error: str
    timestamp: str

# Environment configuration
class Config:
    def __init__(self):
        self.RPC_URL = os.getenv("BLOCKCHAIN_RPC_URL", "https://rpc-amoy.polygon.technology")
        self.FALLBACK_RPC_URL = os.getenv("BLOCKCHAIN_RPC_FALLBACK_URL")
        self.CONTRACT_ADDRESS = os.getenv("CONTRACT_ADDRESS")
        self.ADMIN_PRIVATE_KEY = os.getenv("ADMIN_PRIVATE_KEY")
        self.CHAIN_ID = int(os.getenv("CHAIN_ID", 80002))
        self.GAS_LIMIT = int(os.getenv("GAS_LIMIT", 200000))
        self.GAS_PRICE_GWEI = int(os.getenv("GAS_PRICE_GWEI", 30))
        self.EXPLORER_URL = os.getenv("EXPLORER_URL", "https://amoy.polygonscan.com/tx/")
        self.CONTRACT_ABI_FILE = os.getenv("CONTRACT_ABI_FILE", "SarvOneABI.json")
        
        # Validate required environment variables
        if not self.CONTRACT_ADDRESS:
            raise ValueError("CONTRACT_ADDRESS environment variable is required")
        if not self.ADMIN_PRIVATE_KEY:
            raise ValueError("ADMIN_PRIVATE_KEY environment variable is required")
        
        # Convert to checksum address
        try:
            self.CONTRACT_ADDRESS = Web3.to_checksum_address(self.CONTRACT_ADDRESS)
        except ValueError:
            raise ValueError("Invalid CONTRACT_ADDRESS format")

config = Config()

# Web3 setup with fallback
def get_web3_instance():
    try:
        w3 = Web3(Web3.HTTPProvider(config.RPC_URL))
        if w3.is_connected():
            logger.info(f"Connected to primary RPC: {config.RPC_URL}")
            return w3
    except Exception as e:
        logger.warning(f"Primary RPC failed: {e}")
    
    if config.FALLBACK_RPC_URL:
        try:
            w3 = Web3(Web3.HTTPProvider(config.FALLBACK_RPC_URL))
            if w3.is_connected():
                logger.info(f"Connected to fallback RPC: {config.FALLBACK_RPC_URL}")
                return w3
        except Exception as e:
            logger.error(f"Fallback RPC also failed: {e}")
    
    raise ConnectionError("Unable to connect to any RPC endpoint")

w3 = get_web3_instance()

# Direct contract interaction without ABI
def build_approve_organization_transaction(org_did: str, org_address: str, scopes: list, from_address: str, nonce: int, gas_limit: int, gas_price: int, chain_id: int):
    """
    Build transaction for approveOrganization function directly
    Function signature: approveOrganization(string,address,string[])
    """
    # Encode the function call data directly
    
    # Function selector for approveOrganization(string,address,string[])
    function_signature = "approveOrganization(string,address,string[])"
    function_selector = Web3.keccak(text=function_signature)[:4]
    
    # Encode parameters
    # string orgDID, address orgAddress, string[] scopes
    encoded_params = encode(
        ['string', 'address', 'string[]'], 
        [org_did, org_address, scopes]
    )
    
    # Combine selector and parameters
    transaction_data = function_selector + encoded_params
    
    # Build transaction
    transaction = {
        'to': config.CONTRACT_ADDRESS,
        'from': from_address,
        'data': transaction_data,
        'nonce': nonce,
        'gas': gas_limit,
        'gasPrice': gas_price,
        'chainId': chain_id,
        'value': 0
    }
    
    return transaction

def build_get_organization_call(org_did: str):
    """
    Build call data for getOrganization function
    Function signature: getOrganization(string)
    Returns: (bool approved, address mainAddress, string[] scopes)
    """
    # Function selector for getOrganization(string)
    function_signature = "getOrganization(string)"
    function_selector = Web3.keccak(text=function_signature)[:4]
    
    # Encode parameters
    encoded_params = encode(['string'], [org_did])
    
    # Combine selector and parameters
    call_data = function_selector + encoded_params
    
    return call_data

def build_issue_vc_transaction(user_did: str, vc_hash: str, vc_type: str, from_address: str, nonce: int, gas_limit: int, gas_price: int, chain_id: int):
    """
    Build transaction for issueVC function
    Function signature: issueVC(bytes32,bytes32,string)
    Contract automatically gets orgDID from msg.sender mapping
    """
    # Function selector for issueVC(bytes32,bytes32,string)
    function_signature = "issueVC(bytes32,bytes32,string)"
    function_selector = Web3.keccak(text=function_signature)[:4]
    
    # Convert DID to bytes32 using SHA-256
    import hashlib
    user_did_hash = hashlib.sha256(user_did.encode()).hexdigest()
    user_did_bytes = bytes.fromhex(user_did_hash)
    
    # Convert VC hash to bytes32
    if vc_hash.startswith('0x'):
        vc_hash = vc_hash[2:]
    vc_hash_bytes = bytes.fromhex(vc_hash)
    
    # Encode parameters
    # bytes32 userDID, bytes32 vcHash, string vcType
    encoded_params = encode(
        ['bytes32', 'bytes32', 'string'], 
        [user_did_bytes, vc_hash_bytes, vc_type]
    )
    
    # Combine selector and parameters
    transaction_data = function_selector + encoded_params
    
    # Build transaction
    transaction = {
        'to': config.CONTRACT_ADDRESS,
        'from': from_address,
        'data': transaction_data,
        'nonce': nonce,
        'gas': gas_limit,
        'gasPrice': gas_price,
        'chainId': chain_id,
        'value': 0
    }
    
    return transaction

def build_get_user_vcs_call(user_did: str):
    """
    Build call data for getUserVCs function
    Function signature: getUserVCs(bytes32)
    Returns: VC[] memory (array of VC structs)
    Accepts either a DID string or a hash string
    """
    # Function selector for getUserVCs(bytes32)
    function_signature = "getUserVCs(bytes32)"
    function_selector = Web3.keccak(text=function_signature)[:4]
    
    # Always use SHA-256 for consistency
    if len(user_did) == 64 and all(c in '0123456789abcdef' for c in user_did.lower()):
        # It's already a hash, convert to bytes32
        user_did_bytes = bytes.fromhex(user_did)
    else:
        # It's a DID string, hash it with SHA-256
        import hashlib
        user_did_hash = hashlib.sha256(user_did.encode()).hexdigest()
        user_did_bytes = bytes.fromhex(user_did_hash)
    
    # Encode parameters
    encoded_params = encode(['bytes32'], [user_did_bytes])
    
    # Combine selector and parameters
    call_data = function_selector + encoded_params
    
    return call_data

def build_revoke_vc_transaction(user_did: str, vc_hash: str, from_address: str, nonce: int, gas_limit: int, gas_price: int, chain_id: int):
    """
    Build transaction for revokeVC function
    Function signature: revokeVC(bytes32,bytes32)
    Contract automatically gets orgDID from msg.sender mapping
    """
    # Function selector for revokeVC(bytes32,bytes32)
    function_signature = "revokeVC(bytes32,bytes32)"
    function_selector = Web3.keccak(text=function_signature)[:4]
    
    # Convert DID to bytes32 using SHA-256
    import hashlib
    user_did_hash = hashlib.sha256(user_did.encode()).hexdigest()
    user_did_bytes = bytes.fromhex(user_did_hash)
    
    # Convert hash to bytes32
    if vc_hash.startswith('0x'):
        vc_hash = vc_hash[2:]
    vc_hash_bytes = bytes.fromhex(vc_hash)
    
    # Encode parameters
    encoded_params = encode(['bytes32', 'bytes32'], [user_did_bytes, vc_hash_bytes])
    
    # Combine selector and parameters
    transaction_data = function_selector + encoded_params
    
    # Build transaction
    transaction = {
        'to': config.CONTRACT_ADDRESS,
        'from': from_address,
        'data': transaction_data,
        'nonce': nonce,
        'gas': gas_limit,
        'gasPrice': gas_price,
        'chainId': chain_id,
        'value': 0
    }
    
    return transaction
try:
    # Remove 0x prefix if present and validate private key
    private_key = config.ADMIN_PRIVATE_KEY
    if private_key.startswith('0x'):
        private_key = private_key[2:]
    
    # Validate it's a valid hex string
    if len(private_key) != 64:
        raise ValueError(f"Private key must be 64 characters long, got {len(private_key)}")
    
    # Try to convert to check if it's valid hex
    int(private_key, 16)
    
    admin_account = w3.eth.account.from_key(private_key)
    logger.info("✅ Admin account loaded successfully")
except Exception as e:
    logger.error(f"❌ Failed to load admin account: {e}")
    raise ValueError(f"Invalid ADMIN_PRIVATE_KEY format: {e}")

logger.info(f"Blockchain service initialized:")
logger.info(f"- Contract Address: {config.CONTRACT_ADDRESS}")
logger.info(f"- Admin Address: {admin_account.address}")
logger.info(f"- Chain ID: {config.CHAIN_ID}")
logger.info(f"- Gas Limit: {config.GAS_LIMIT}")
logger.info(f"- Gas Price: {config.GAS_PRICE_GWEI} Gwei")

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    try:
        # Check Web3 connection
        latest_block = w3.eth.block_number
        
        # Check admin account balance
        balance = w3.eth.get_balance(admin_account.address)
        balance_eth = w3.from_wei(balance, 'ether')
        
        return {
            "status": "healthy",
            "blockchain_connected": True,
            "latest_block": latest_block,
            "admin_balance_eth": float(balance_eth),
            "contract_address": config.CONTRACT_ADDRESS,
            "chain_id": config.CHAIN_ID,
            "timestamp": datetime.now().isoformat()
        }
    except Exception as e:
        logger.error(f"Health check failed: {e}")
        raise HTTPException(status_code=503, detail=f"Service unhealthy: {str(e)}")

@app.post("/approve_org", response_model=TransactionResponse)
async def approve_organization(req: ApproveOrgRequest):
    """
    Approve an organization on the SarvOne smart contract
    """
    start_time = time.time()
    logger.info(f"Received approval request for orgDID: {req.orgDID}")
    
    try:
        # Validate and convert address
        org_address_checksum = Web3.to_checksum_address(req.orgAddress)
        
        # Check admin account balance
        balance = w3.eth.get_balance(admin_account.address)
        # Use higher gas price for balance estimation
        estimated_gas_price = w3.to_wei(config.GAS_PRICE_GWEI * 1.5, 'gwei')  # 50% buffer for estimation
        estimated_gas_cost = config.GAS_LIMIT * estimated_gas_price
        
        if balance < estimated_gas_cost:
            raise HTTPException(
                status_code=400, 
                detail=f"Insufficient admin balance. Required: {w3.from_wei(estimated_gas_cost, 'ether')} ETH, Available: {w3.from_wei(balance, 'ether')} ETH"
            )
        
        # Get current nonce
        nonce = w3.eth.get_transaction_count(admin_account.address, 'pending')
        
        # Calculate gas price with dynamic pricing and buffer
        try:
            # Get current network gas price and add 20% buffer
            network_gas_price = w3.eth.gas_price
            gas_price = int(network_gas_price * 1.2)  # 20% buffer
            logger.info(f"Network gas price: {w3.from_wei(network_gas_price, 'gwei')} Gwei, Using: {w3.from_wei(gas_price, 'gwei')} Gwei")
        except Exception as e:
            # Fallback to configured gas price
            gas_price = w3.to_wei(config.GAS_PRICE_GWEI, 'gwei')
            logger.warning(f"Failed to get network gas price, using fallback: {config.GAS_PRICE_GWEI} Gwei")
        
        logger.info(f"Building transaction with nonce: {nonce}, gas_price: {w3.from_wei(gas_price, 'gwei')} Gwei")
        
        # Build transaction directly without ABI
        transaction = build_approve_organization_transaction(
            org_did=req.orgDID,
            org_address=org_address_checksum,
            scopes=req.scopes,
            from_address=admin_account.address,
            nonce=nonce,
            gas_limit=config.GAS_LIMIT,
            gas_price=gas_price,
            chain_id=config.CHAIN_ID
        )
        
        logger.info(f"Transaction built successfully. Data length: {len(transaction['data'])}")
        
        # Sign transaction
        signed_txn = admin_account.sign_transaction(transaction)
        
        # Send transaction
        tx_hash = w3.eth.send_raw_transaction(signed_txn.raw_transaction)
        tx_hash_hex = "0x" + tx_hash.hex() if not tx_hash.hex().startswith("0x") else tx_hash.hex()
        
        logger.info(f"Transaction sent: {tx_hash_hex}")
        
        # Wait for transaction receipt to confirm success
        try:
            receipt = w3.eth.wait_for_transaction_receipt(tx_hash, timeout=60)
            
            if receipt.status == 1:
                processing_time = time.time() - start_time
                logger.info(f"✅ Transaction confirmed: {tx_hash_hex} (processed in {processing_time:.2f}s)")
                logger.info(f"Block: {receipt.blockNumber}, Gas Used: {receipt.gasUsed}")
                logger.info(f"Organization approved: {req.orgDID} -> {org_address_checksum}")
                logger.info(f"Scopes: {req.scopes}")
                
                return TransactionResponse(
                    success=True,
                    tx_hash=tx_hash_hex,
                    explorer_url=config.EXPLORER_URL + tx_hash_hex,
                    block_number=receipt.blockNumber,
                    gas_used=receipt.gasUsed,
                    timestamp=datetime.now().isoformat()
                )
            else:
                logger.error(f"❌ Transaction failed: {tx_hash_hex}")
                raise HTTPException(
                    status_code=400, 
                    detail=f"Blockchain transaction failed. Check transaction: {config.EXPLORER_URL + tx_hash_hex}"
                )
                
        except Exception as e:
            logger.error(f"❌ Transaction confirmation failed: {e}")
            raise HTTPException(
                status_code=400, 
                detail=f"Transaction may have failed. Please check: {config.EXPLORER_URL + tx_hash_hex}"
            )
        
    except ContractLogicError as e:
        logger.error(f"Contract logic error: {e}")
        raise HTTPException(status_code=400, detail=f"Contract execution failed: {str(e)}")
    
    except ValueError as e:
        error_msg = str(e)
        if "insufficient funds" in error_msg.lower():
            logger.error(f"Insufficient funds for transaction: {e}")
            raise HTTPException(status_code=400, detail="Insufficient funds for transaction")
        elif "nonce too low" in error_msg.lower():
            logger.error(f"Nonce too low: {e}")
            raise HTTPException(status_code=400, detail="Transaction nonce error, please retry")
        else:
            logger.error(f"Transaction value error: {e}")
            raise HTTPException(status_code=400, detail=f"Transaction error: {error_msg}")
    
    except Exception as e:
        processing_time = time.time() - start_time
        logger.error(f"Approval failed for {req.orgDID}: {e} (failed after {processing_time:.2f}s)")
        raise HTTPException(status_code=500, detail=f"Internal server error: {str(e)}")

@app.get("/transaction/{tx_hash}")
async def get_transaction_status(tx_hash: str):
    """
    Get transaction status and receipt
    """
    try:
        if not tx_hash.startswith('0x'):
            tx_hash = '0x' + tx_hash
        
        # Get transaction receipt
        try:
            receipt = w3.eth.get_transaction_receipt(tx_hash)
            transaction = w3.eth.get_transaction(tx_hash)
            
            return {
                "tx_hash": tx_hash,
                "status": "confirmed" if receipt.status == 1 else "failed",
                "block_number": receipt.blockNumber,
                "gas_used": receipt.gasUsed,
                "gas_limit": transaction.gas,
                "gas_price_gwei": w3.from_wei(transaction.gasPrice, 'gwei'),
                "explorer_url": config.EXPLORER_URL + tx_hash,
                "timestamp": datetime.now().isoformat()
            }
        except TransactionNotFound:
            return {
                "tx_hash": tx_hash,
                "status": "pending",
                "explorer_url": config.EXPLORER_URL + tx_hash,
                "timestamp": datetime.now().isoformat()
            }
    
    except Exception as e:
        logger.error(f"Failed to get transaction status for {tx_hash}: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to retrieve transaction: {str(e)}")

@app.get("/get_org/{org_did}", response_model=OrganizationResponse)
async def get_organization(org_did: str):
    """
    Get organization data from the SarvOne smart contract
    """
    start_time = time.time()
    logger.info(f"Received request to get organization: {org_did}")
    
    try:
        # Validate DID format
        if not org_did.startswith("did:sarvone:"):
            raise HTTPException(status_code=400, detail="Invalid DID format. Must start with 'did:sarvone:'")
        
        # Build call data for getOrganization function
        call_data = build_get_organization_call(org_did)
        
        # Make the call to the smart contract
        call_result = w3.eth.call({
            'to': config.CONTRACT_ADDRESS,
            'data': call_data
        })
        
        # Decode the result
        # Returns: (bool approved, address mainAddress, string[] scopes)
        from eth_abi import decode
        decoded_result = decode(['bool', 'address', 'string[]'], call_result)
        
        approved = decoded_result[0]
        main_address = decoded_result[1]
        scopes = decoded_result[2]
        
        processing_time = time.time() - start_time
        
        logger.info(f"Organization data retrieved successfully: {org_did} (processed in {processing_time:.2f}s)")
        logger.info(f"Approved: {approved}, Address: {main_address}, Scopes: {scopes}")
        
        return OrganizationResponse(
            success=True,
            orgDID=org_did,
            approved=approved,
            mainAddress=main_address if approved else None,
            scopes=scopes if approved and scopes else None,
            timestamp=datetime.now().isoformat()
        )
        
    except Exception as e:
        logger.error(f"Error retrieving organization data: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to retrieve organization data: {str(e)}")

@app.post("/issue_vc", response_model=TransactionResponse)
async def issue_vc(req: IssueVCRequest):
    """
    Issue a Verifiable Credential on the SarvOne smart contract
    The organization's private key is used to sign the transaction, ensuring msg.sender is the org's address
    """
    start_time = time.time()
    logger.info(f"Received VC issuance request for user: {req.user_did}, type: {req.vc_type}")
    
    try:
        # Create account from organization's private key
        org_private_key = req.org_private_key
        if not org_private_key.startswith('0x'):
            org_private_key = '0x' + org_private_key
            
        # Create Web3 account from organization's private key
        from eth_account import Account
        org_account = Account.from_key(org_private_key)
        org_address = org_account.address
        
        logger.info(f"Using organization address: {org_address}")
        
        # Check organization account balance
        balance = w3.eth.get_balance(org_address)
        estimated_gas_cost = config.GAS_LIMIT * w3.to_wei(config.GAS_PRICE_GWEI, 'gwei')
        
        if balance < estimated_gas_cost:
            raise HTTPException(
                status_code=400, 
                detail=f"Insufficient organization balance. Required: {w3.from_wei(estimated_gas_cost, 'ether')} ETH, Available: {w3.from_wei(balance, 'ether')} ETH"
            )
        
        # Get current nonce for organization address
        nonce = w3.eth.get_transaction_count(org_address, 'pending')
        
        # Calculate gas price
        gas_price = w3.to_wei(config.GAS_PRICE_GWEI, 'gwei')
        
        logger.info(f"Building VC issuance transaction with org nonce: {nonce}")
        
        # Build transaction using organization's address as sender
        transaction = build_issue_vc_transaction(
            user_did=req.user_did,
            vc_hash=req.vc_hash,
            vc_type=req.vc_type,
            from_address=org_address,
            nonce=nonce,
            gas_limit=config.GAS_LIMIT,
            gas_price=gas_price,
            chain_id=config.CHAIN_ID
        )
        
        logger.info(f"VC transaction built successfully. Data length: {len(transaction['data'])}")
        
        # Sign transaction with organization's private key
        signed_txn = org_account.sign_transaction(transaction)
        
        # Send transaction
        tx_hash = w3.eth.send_raw_transaction(signed_txn.raw_transaction)
        tx_hash_hex = "0x" + tx_hash.hex() if not tx_hash.hex().startswith("0x") else tx_hash.hex()
        
        logger.info(f"VC transaction sent: {tx_hash_hex}")
        
        # Wait for transaction receipt
        try:
            receipt = w3.eth.wait_for_transaction_receipt(tx_hash, timeout=60)
            
            if receipt.status == 1:
                processing_time = time.time() - start_time
                logger.info(f"✅ VC issued successfully: {tx_hash_hex} (processed in {processing_time:.2f}s)")
                logger.info(f"Block: {receipt.blockNumber}, Gas Used: {receipt.gasUsed}")
                logger.info(f"VC Details - User: {req.user_did}, Type: {req.vc_type}, Hash: {req.vc_hash}")
                
                return TransactionResponse(
                    success=True,
                    tx_hash=tx_hash_hex,
                    explorer_url=config.EXPLORER_URL + tx_hash_hex,
                    block_number=receipt.blockNumber,
                    gas_used=receipt.gasUsed,
                    timestamp=datetime.now().isoformat()
                )
            else:
                logger.error(f"❌ VC issuance transaction failed: {tx_hash_hex}")
                raise HTTPException(
                    status_code=400, 
                    detail=f"VC issuance failed. Check transaction: {config.EXPLORER_URL + tx_hash_hex}"
                )
                
        except Exception as e:
            logger.error(f"❌ VC transaction confirmation failed: {e}")
            raise HTTPException(
                status_code=400, 
                detail=f"Transaction may have failed. Please check: {config.EXPLORER_URL + tx_hash_hex}"
            )
        
    except Exception as e:
        processing_time = time.time() - start_time
        logger.error(f"VC issuance failed for {req.user_did}: {e} (failed after {processing_time:.2f}s)")
        raise HTTPException(status_code=500, detail=f"Internal server error: {str(e)}")

@app.get("/get_vc_details/{vc_hash}")
async def get_vc_details(vc_hash: str):
    """
    Get detailed information about a specific VC by its hash
    """
    try:
        logger.info(f"Getting VC details for hash: {vc_hash}")
        
        # Remove 0x prefix if present
        if vc_hash.startswith('0x'):
            vc_hash = vc_hash[2:]
        
        # Validate hash format
        if len(vc_hash) != 64:
            raise HTTPException(status_code=400, detail="Invalid hash format. Must be 64 characters hex string.")
        
        # Convert to bytes32
        vc_hash_bytes = bytes.fromhex(vc_hash)
        
        # Get web3 instance
        w3 = get_web3_instance()
        
        # Get contract instance
        contract = w3.eth.contract(
            address=config.CONTRACT_ADDRESS,
            abi=config.CONTRACT_ABI
        )
        
        # Call the smart contract to get VC details
        # We need to iterate through all VCs to find the one with matching hash
        # This is not efficient but necessary since the contract doesn't have a direct lookup by hash
        
        # Get all VCs for all users and find the matching one
        # This is a simplified approach - in production you might want to maintain an index
        
        # For now, we'll return a basic structure
        # In a real implementation, you'd need to scan the blockchain events or maintain an index
        
        logger.warning("VC details lookup by hash not fully implemented - returning basic info")
        
        return {
            "success": True,
            "vc": {
                "hash": "0x" + vc_hash,
                "issuer_did": "did:sarvone:bnk:sbi:t0a4tz:001:7e25",  # Placeholder
                "recipient_did": "did:sarvone:18f7497cac92c32f",  # Placeholder
                "credential_type": "account_opening",  # Placeholder
                "issued_at": int(time.time()),
                "is_active": True
            },
            "timestamp": datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Error getting VC details: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Internal server error: {str(e)}")

@app.get("/get_user_vcs/{user_did}")
async def get_user_vcs(user_did: str):
    """
    Get all VCs for a user from the SarvOne smart contract
    Accepts either a DID string or a hash of the DID
    """
    start_time = time.time()
    logger.info(f"Received request to get VCs for user: {user_did}")
    
    try:
        # Always use SHA-256 for consistency
        if user_did.startswith("did:sarvone:"):
            # It's a DID, hash it with SHA-256
            import hashlib
            user_did_hash = hashlib.sha256(user_did.encode()).hexdigest()
            logger.info(f"Converted DID to SHA-256 hash: {user_did} -> {user_did_hash}")
        elif len(user_did) == 64 and all(c in '0123456789abcdef' for c in user_did.lower()):
            # It's already a hash, use it directly
            user_did_hash = user_did
            logger.info(f"Using provided hash directly: {user_did_hash}")
        else:
            raise HTTPException(status_code=400, detail="Invalid format. Must be a DID starting with 'did:sarvone:' or a 64-character hex hash")
        
        # Build call data for getUserVCs function using the hash
        call_data = build_get_user_vcs_call(user_did_hash)
        
        # Make the call to the smart contract
        call_result = w3.eth.call({
            'to': config.CONTRACT_ADDRESS,
            'data': call_data
        })
        
        # Decode the result
        # Returns: VC[] memory (array of structs)
        # struct VC { bytes32 hash; string vcType; uint256 issuedAt; string issuerDID; bool revoked; }
        from eth_abi import decode
        
        # For dynamic arrays of structs, we need to decode as (bytes32,string,uint256,string,bool)[]
        try:
            decoded_result = decode(['(bytes32,string,uint256,string,bool)[]'], call_result)
            vcs_data = decoded_result[0]
        except Exception as decode_error:
            logger.warning(f"Failed to decode VCs as array: {decode_error}")
            # If no VCs, return empty array
            vcs_data = []
        
        # Process VCs
        vcs = []
        for vc_tuple in vcs_data:
            vc_hash, vc_type, issued_at, issuer_did, revoked = vc_tuple
            vc_hash_hex = "0x" + vc_hash.hex()
            
            # Try to get transaction hash from database (if available)
            tx_hash = None
            try:
                # This would require database access - for now, we'll add a placeholder
                # In a full implementation, you'd query the database here
                tx_hash = None  # Placeholder - would be fetched from database
            except Exception as db_error:
                logger.warning(f"Could not fetch transaction hash for VC {vc_hash_hex}: {db_error}")
            
            vcs.append({
                "hash": vc_hash_hex,
                "vc_type": vc_type,
                "issued_at": issued_at,
                "issuer_did": issuer_did,
                "revoked": revoked,
                "is_active": not revoked,
                "transaction_hash": tx_hash,
                "explorer_url": tx_hash if tx_hash else None
            })
        
        processing_time = time.time() - start_time
        
        logger.info(f"User VCs retrieved successfully: {user_did} (processed in {processing_time:.2f}s)")
        logger.info(f"Found {len(vcs)} VCs for user")
        
        return {
            "success": True,
            "user_did": user_did,
            "vcs": vcs,
            "total_count": len(vcs),
            "timestamp": datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Error retrieving user VCs: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to retrieve user VCs: {str(e)}")

@app.post("/revoke_vc", response_model=TransactionResponse)
async def revoke_vc(req: RevokeVCRequest):
    """
    Revoke a Verifiable Credential on the SarvOne smart contract
    Note: Only the issuing organization can revoke their own VCs
    """
    start_time = time.time()
    logger.info(f"Received VC revocation request for user: {req.user_did}, hash: {req.vc_hash}")
    
    try:
        # Check admin account balance
        balance = w3.eth.get_balance(admin_account.address)
        estimated_gas_cost = config.GAS_LIMIT * w3.to_wei(config.GAS_PRICE_GWEI, 'gwei')
        
        if balance < estimated_gas_cost:
            raise HTTPException(
                status_code=400, 
                detail=f"Insufficient admin balance. Required: {w3.from_wei(estimated_gas_cost, 'ether')} ETH, Available: {w3.from_wei(balance, 'ether')} ETH"
            )
        
        # Get current nonce
        nonce = w3.eth.get_transaction_count(admin_account.address, 'pending')
        
        # Calculate gas price
        gas_price = w3.to_wei(config.GAS_PRICE_GWEI, 'gwei')
        
        logger.info(f"Building VC revocation transaction with nonce: {nonce}")
        
        # Build transaction
        transaction = build_revoke_vc_transaction(
            user_did=req.user_did,
            vc_hash=req.vc_hash,
            from_address=admin_account.address,
            nonce=nonce,
            gas_limit=config.GAS_LIMIT,
            gas_price=gas_price,
            chain_id=config.CHAIN_ID
        )
        
        logger.info(f"VC revocation transaction built successfully. Data length: {len(transaction['data'])}")
        
        # Sign transaction
        signed_txn = admin_account.sign_transaction(transaction)
        
        # Send transaction
        tx_hash = w3.eth.send_raw_transaction(signed_txn.raw_transaction)
        tx_hash_hex = "0x" + tx_hash.hex() if not tx_hash.hex().startswith("0x") else tx_hash.hex()
        
        logger.info(f"VC revocation transaction sent: {tx_hash_hex}")
        
        # Wait for transaction receipt
        try:
            receipt = w3.eth.wait_for_transaction_receipt(tx_hash, timeout=60)
            
            if receipt.status == 1:
                processing_time = time.time() - start_time
                logger.info(f"✅ VC revoked successfully: {tx_hash_hex} (processed in {processing_time:.2f}s)")
                logger.info(f"Block: {receipt.blockNumber}, Gas Used: {receipt.gasUsed}")
                logger.info(f"Revoked VC - User: {req.user_did}, Hash: {req.vc_hash}")
                
                return TransactionResponse(
                    success=True,
                    tx_hash=tx_hash_hex,
                    explorer_url=config.EXPLORER_URL + tx_hash_hex,
                    block_number=receipt.blockNumber,
                    gas_used=receipt.gasUsed,
                    timestamp=datetime.now().isoformat()
                )
            else:
                logger.error(f"❌ VC revocation transaction failed: {tx_hash_hex}")
                raise HTTPException(
                    status_code=400, 
                    detail=f"VC revocation failed. Check transaction: {config.EXPLORER_URL + tx_hash_hex}"
                )
                
        except Exception as e:
            logger.error(f"❌ VC revocation transaction confirmation failed: {e}")
            raise HTTPException(
                status_code=400, 
                detail=f"Transaction may have failed. Please check: {config.EXPLORER_URL + tx_hash_hex}"
            )
        
    except Exception as e:
        processing_time = time.time() - start_time
        logger.error(f"VC revocation failed for {req.user_did}: {e} (failed after {processing_time:.2f}s)")
        raise HTTPException(status_code=500, detail=f"Internal server error: {str(e)}")

@app.get("/contract/info")
async def get_contract_info():
    """
    Get contract information
    """
    try:
        return {
            "contract_address": config.CONTRACT_ADDRESS,
            "admin_address": admin_account.address,
            "chain_id": config.CHAIN_ID,
            "network": "Polygon Amoy Testnet",
            "explorer_contract_url": f"https://amoy.polygonscan.com/address/{config.CONTRACT_ADDRESS}",
            "gas_limit": config.GAS_LIMIT,
            "gas_price_gwei": config.GAS_PRICE_GWEI
        }
    except Exception as e:
        logger.error(f"Failed to get contract info: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to retrieve contract info: {str(e)}")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8003) 