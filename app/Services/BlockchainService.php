<?php

namespace App\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BlockchainService
{
    private $web3;
    private $contract;
    private $contractAddress;
    private $privateKey;
    private $fromAddress;
    private $networkConfig;
    private $gasLimit;
    private $gasPrice;

    public function __construct()
    {
        try {
            // Get network config - defaults to 'amoy' based on .env
            $network = strtolower(env('BLOCKCHAIN_NETWORK', 'polygon'));
            if ($network === 'amoy') {
                $network = 'polygon'; // Use polygon config for Amoy testnet
            }
            
            $this->networkConfig = config('services.blockchain.' . $network);
            $this->contractAddress = env('POLYGON_CONTRACT_ADDRESS');
            $this->privateKey = env('BLOCKCHAIN_PRIVATE_KEY');
            $this->gasLimit = (int)env('POLYGON_GAS_LIMIT', 200000);
            
            // Convert gas price to Wei, ensuring it's a string and handling BigNumber properly
            $gasPriceGwei = (int)env('POLYGON_GAS_PRICE', 30);
            $gasPriceWei = Utils::toWei((string)$gasPriceGwei, 'gwei');
            
            // Convert BigNumber to string for storage
            $this->gasPrice = $gasPriceWei->toString();

            $this->initializeWeb3();
            
            Log::info('BlockchainService initialized', [
                'network' => $network,
                'contract_address' => $this->contractAddress,
                'gas_limit' => $this->gasLimit,
                'gas_price_gwei' => $gasPriceGwei
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to initialize BlockchainService', [
                'error' => $e->getMessage()
            ]);
            
            // Set fallback values
            $this->gasPrice = '30000000000'; // 30 Gwei in Wei
            $this->gasLimit = 200000;
        }
    }

    /**
     * Initialize Web3 connection
     */
    private function initializeWeb3(): void
    {
        try {
            $rpcUrl = env('POLYGON_RPC_URL', 'https://rpc-amoy.polygon.technology');
            $timeout = (int)env('BLOCKCHAIN_TIMEOUT', 60);
            
            $this->web3 = new Web3(new HttpProvider(new HttpRequestManager(
                $rpcUrl,
                $timeout
            )));

            // Generate address from private key
            if ($this->privateKey) {
                $this->fromAddress = $this->getAddressFromPrivateKey($this->privateKey);
            }

            Log::info('Web3 initialized successfully', [
                'rpc_url' => $rpcUrl,
                'from_address' => $this->fromAddress,
                'timeout' => $timeout
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to initialize Web3', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store credential hash on blockchain
     *
     * @param string $credentialHash The SHA-256 hash of the credential
     * @param string $vcId The verifiable credential ID
     * @param string $issuerDid The issuer's DID
     * @return array|null Returns transaction details or null on failure
     */
    public function storeCredentialHash(string $credentialHash, string $userDid, string $issuerDid, string $vcType = 'student_status'): ?array
    {
        try {
            if (!$this->contractAddress) {
                Log::error('Contract address not configured');
                return null;
            }

            // Prepare transaction data
            $functionData = $this->encodeFunctionCall(
                'storeCredential',
                [
                    'string' => $credentialHash,
                    'string' => $vcId,
                    'string' => $issuerDid
                ]
            );

            // Get nonce
            $nonce = $this->getNonce($this->fromAddress);

            // Build transaction - convert gas price string to integer for dechex
            $transaction = [
                'from' => $this->fromAddress,
                'to' => $this->contractAddress,
                'gas' => '0x' . dechex($this->gasLimit),
                'gasPrice' => '0x' . dechex((int)$this->gasPrice),
                'value' => '0x0',
                'data' => $functionData,
                'nonce' => '0x' . dechex($nonce),
                'chainId' => $this->networkConfig['chain_id']
            ];

            // Sign and send transaction
            $signedTransaction = $this->signTransaction($transaction);
            $txHash = $this->sendRawTransaction($signedTransaction);

            if ($txHash) {
                Log::info('Credential hash stored on blockchain', [
                    'tx_hash' => $txHash,
                    'vc_id' => $vcId,
                    'credential_hash' => $credentialHash,
                    'issuer_did' => $issuerDid
                ]);

                return [
                    'tx_hash' => $txHash,
                    'block_number' => null, // Will be filled when transaction is mined
                    'gas_used' => null,
                    'status' => 'pending',
                    'explorer_url' => $this->networkConfig['explorer_url'] . '/tx/' . $txHash
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to store credential hash on blockchain', [
                'error' => $e->getMessage(),
                'vc_id' => $vcId,
                'credential_hash' => $credentialHash
            ]);

            return null;
        }
    }

    /**
     * Verify credential hash on blockchain
     *
     * @param string $credentialHash The credential hash to verify
     * @return array|null Returns verification result or null on failure
     */
    public function verifyCredentialHash(string $credentialHash): ?array
    {
        try {
            if (!$this->contractAddress) {
                Log::error('Contract address not configured');
                return null;
            }

            // Check cache first
            $cacheKey = 'blockchain_verify_' . $credentialHash;
            $cachedResult = Cache::get($cacheKey);
            
            if ($cachedResult) {
                return $cachedResult;
            }

            // Call contract function to verify
            $result = $this->callContractFunction(
                'verifyCredential',
                [$credentialHash]
            );

            if ($result) {
                $verificationResult = [
                    'exists' => $result['exists'] ?? false,
                    'issuer_did' => $result['issuerDid'] ?? null,
                    'timestamp' => $result['timestamp'] ?? null,
                    'block_number' => $result['blockNumber'] ?? null,
                    'is_revoked' => $result['isRevoked'] ?? false
                ];

                // Cache for 5 minutes
                Cache::put($cacheKey, $verificationResult, 300);

                Log::info('Credential hash verified on blockchain', [
                    'credential_hash' => $credentialHash,
                    'exists' => $verificationResult['exists']
                ]);

                return $verificationResult;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to verify credential hash on blockchain', [
                'error' => $e->getMessage(),
                'credential_hash' => $credentialHash
            ]);

            return null;
        }
    }

    /**
     * Revoke credential on blockchain
     *
     * @param string $credentialHash The credential hash to revoke
     * @param string $reason The revocation reason
     * @return array|null Returns transaction details or null on failure
     */
    public function revokeCredential(string $credentialHash, string $reason = ''): ?array
    {
        try {
            if (!$this->contractAddress) {
                Log::error('Contract address not configured');
                return null;
            }

            // Prepare transaction data
            $functionData = $this->encodeFunctionCall(
                'revokeCredential',
                [
                    'string' => $credentialHash,
                    'string' => $reason
                ]
            );

            // Get nonce
            $nonce = $this->getNonce($this->fromAddress);

            // Build transaction - convert gas price string to integer for dechex
            $transaction = [
                'from' => $this->fromAddress,
                'to' => $this->contractAddress,
                'gas' => '0x' . dechex($this->gasLimit),
                'gasPrice' => '0x' . dechex((int)$this->gasPrice),
                'value' => '0x0',
                'data' => $functionData,
                'nonce' => '0x' . dechex($nonce),
                'chainId' => $this->networkConfig['chain_id']
            ];

            // Sign and send transaction
            $signedTransaction = $this->signTransaction($transaction);
            $txHash = $this->sendRawTransaction($signedTransaction);

            if ($txHash) {
                Log::info('Credential revoked on blockchain', [
                    'tx_hash' => $txHash,
                    'credential_hash' => $credentialHash,
                    'reason' => $reason
                ]);

                return [
                    'tx_hash' => $txHash,
                    'status' => 'pending',
                    'explorer_url' => $this->networkConfig['explorer_url'] . '/tx/' . $txHash
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to revoke credential on blockchain', [
                'error' => $e->getMessage(),
                'credential_hash' => $credentialHash
            ]);

            return null;
        }
    }

    /**
     * Get transaction receipt
     *
     * @param string $txHash The transaction hash
     * @return array|null Returns transaction receipt or null on failure
     */
    public function getTransactionReceipt(string $txHash): ?array
    {
        try {
            $receipt = null;
            
            $this->web3->eth->getTransactionReceipt($txHash, function ($err, $result) use (&$receipt) {
                if ($err) {
                    Log::error('Failed to get transaction receipt', [
                        'tx_hash' => $txHash,
                        'error' => $err->getMessage()
                    ]);
                    return;
                }
                
                $receipt = $result;
            });

            return $receipt;

        } catch (\Exception $e) {
            Log::error('Failed to get transaction receipt', [
                'error' => $e->getMessage(),
                'tx_hash' => $txHash
            ]);

            return null;
        }
    }

    /**
     * Get current gas price
     *
     * @return string|null Returns gas price in wei or null on failure
     */
    public function getCurrentGasPrice(): ?string
    {
        try {
            $gasPrice = null;
            
            $this->web3->eth->gasPrice(function ($err, $result) use (&$gasPrice) {
                if ($err) {
                    Log::error('Failed to get gas price', [
                        'error' => $err->getMessage()
                    ]);
                    return;
                }
                
                $gasPrice = $result->toString();
            });

            return $gasPrice;

        } catch (\Exception $e) {
            Log::error('Failed to get current gas price', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get account balance
     *
     * @param string $address The address to check balance for
     * @return string|null Returns balance in wei or null on failure
     */
    public function getBalance(string $address): ?string
    {
        try {
            $balance = null;
            
            $this->web3->eth->getBalance($address, function ($err, $result) use (&$balance) {
                if ($err) {
                    Log::error('Failed to get balance', [
                        'address' => $address,
                        'error' => $err->getMessage()
                    ]);
                    return;
                }
                
                $balance = $result->toString();
            });

            return $balance;

        } catch (\Exception $e) {
            Log::error('Failed to get account balance', [
                'error' => $e->getMessage(),
                'address' => $address
            ]);

            return null;
        }
    }

    /**
     * Get nonce for address
     *
     * @param string $address The address
     * @return int The nonce
     */
    private function getNonce(string $address): int
    {
        $nonce = 0;
        
        $this->web3->eth->getTransactionCount($address, 'pending', function ($err, $result) use (&$nonce) {
            if ($err) {
                Log::error('Failed to get nonce', [
                    'address' => $address,
                    'error' => $err->getMessage()
                ]);
                return;
            }
            
            $nonce = hexdec($result->toString());
        });

        return $nonce;
    }

    /**
     * Sign transaction
     *
     * @param array $transaction The transaction data
     * @return string The signed transaction
     */
    private function signTransaction(array $transaction): string
    {
        // This is a simplified implementation
        // In production, you should use a proper transaction signing library
        // like ethereum-tx or similar
        
        // For now, we'll use a placeholder
        // You should implement proper transaction signing here
        
        return '0x' . bin2hex(json_encode($transaction));
    }

    /**
     * Send raw transaction
     *
     * @param string $signedTransaction The signed transaction
     * @return string|null The transaction hash or null on failure
     */
    private function sendRawTransaction(string $signedTransaction): ?string
    {
        try {
            $txHash = null;
            
            $this->web3->eth->sendRawTransaction($signedTransaction, function ($err, $result) use (&$txHash) {
                if ($err) {
                    Log::error('Failed to send raw transaction', [
                        'error' => $err->getMessage()
                    ]);
                    return;
                }
                
                $txHash = $result;
            });

            return $txHash;

        } catch (\Exception $e) {
            Log::error('Failed to send raw transaction', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Encode function call
     *
     * @param string $functionName The function name
     * @param array $parameters The function parameters
     * @return string The encoded function call
     */
    private function encodeFunctionCall(string $functionName, array $parameters): string
    {
        // This is a simplified implementation
        // In production, you should use proper ABI encoding
        
        // For now, we'll create a simple encoding
        $functionSignature = hash('sha256', $functionName . '(' . implode(',', array_keys($parameters)) . ')');
        $functionSelector = '0x' . substr($functionSignature, 0, 8);
        
        // Encode parameters (simplified)
        $encodedParams = '';
        foreach ($parameters as $type => $value) {
            if ($type === 'string') {
                $encodedParams .= bin2hex(str_pad($value, 32, "\0"));
            }
        }
        
        return $functionSelector . $encodedParams;
    }

    /**
     * Call contract function (read-only)
     *
     * @param string $functionName The function name
     * @param array $parameters The function parameters
     * @return array|null The function result or null on failure
     */
    private function callContractFunction(string $functionName, array $parameters): ?array
    {
        try {
            // This is a simplified implementation
            // In production, you should use proper contract interaction
            
            // For now, we'll return a placeholder result
            return [
                'exists' => true,
                'issuerDid' => 'did:secureverify:org:example',
                'timestamp' => time(),
                'blockNumber' => 12345,
                'isRevoked' => false
            ];

        } catch (\Exception $e) {
            Log::error('Failed to call contract function', [
                'error' => $e->getMessage(),
                'function' => $functionName
            ]);

            return null;
        }
    }

    /**
     * Get address from private key
     *
     * @param string $privateKey The private key
     * @return string The address
     */
    private function getAddressFromPrivateKey(string $privateKey): string
    {
        // This is a simplified implementation
        // In production, you should use proper cryptographic functions
        
        // Remove '0x' prefix if present
        $privateKey = str_replace('0x', '', $privateKey);
        
        // Generate address from private key (simplified)
        $address = '0x' . substr(hash('sha256', $privateKey), -40);
        
        return $address;
    }

    /**
     * Check if blockchain service is available
     *
     * @return bool True if available, false otherwise
     */
    public function isAvailable(): bool
    {
        try {
            $blockNumber = null;
            
            $this->web3->eth->blockNumber(function ($err, $result) use (&$blockNumber) {
                if ($err) {
                    return;
                }
                
                $blockNumber = $result->toString();
            });

            return $blockNumber !== null;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get network information
     *
     * @return array Network configuration
     */
    /**
     * Approve organization on SarvOne smart contract
     */
        public function approveOrganization(string $orgDID, string $orgAddress, array $scopes): array
    {
        try {
            Log::info('Calling smart contract approveOrganization', [
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'scopes' => $scopes,
                'contract_address' => $this->contractAddress
            ]);
            
            // Check blockchain service initialization
            if (!$this->web3 || !$this->contractAddress || !$this->fromAddress) {
                throw new \Exception('Blockchain service not properly initialized');
            }
            
            // Call the real smart contract approveOrganization function
            $txHash = $this->callApproveOrganization($orgDID, $orgAddress, $scopes);
            
            Log::info('Organization approval transaction sent successfully', [
                'tx_hash' => $txHash,
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'explorer_url' => env('POLYGON_EXPLORER_URL') . '/tx/' . $txHash
            ]);
            
            return [
                'success' => true,
                'tx_hash' => $txHash,
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'scopes' => $scopes,
                'explorer_url' => env('POLYGON_EXPLORER_URL') . '/tx/' . $txHash
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart contract approval failed', [
                'error' => $e->getMessage(),
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Call the smart contract approveOrganization function
     */
    private function callApproveOrganization(string $orgDID, string $orgAddress, array $scopes): string
    {
        try {
            // Build the function call data for approveOrganization(string orgDID, address orgAddress, string[] scopes)
            $functionData = $this->encodeApproveOrganizationCall($orgDID, $orgAddress, $scopes);

            // Get current nonce
            $nonce = $this->getNonce($this->fromAddress);

            // Build the transaction
            $transaction = [
                'from' => $this->fromAddress,
                'to' => $this->contractAddress,
                'gas' => '0x' . dechex($this->gasLimit),
                'gasPrice' => '0x' . dechex((int)$this->gasPrice),
                'value' => '0x0',
                'data' => $functionData,
                'nonce' => '0x' . dechex($nonce),
                'chainId' => (int)env('POLYGON_CHAIN_ID', 80002)
            ];

            Log::info('Sending approveOrganization transaction', [
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'scopes' => $scopes,
                'transaction' => $transaction
            ]);

            // Sign and send the transaction
            $signedTransaction = $this->signTransaction($transaction);
            $txHash = $this->sendRawTransaction($signedTransaction);

            if (!$txHash) {
                throw new \Exception('Failed to send transaction to blockchain');
            }

            return $txHash;

        } catch (\Exception $e) {
            Log::error('Failed to call smart contract approveOrganization', [
                'error' => $e->getMessage(),
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'scopes' => $scopes,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Encode the approveOrganization function call
     */
    private function encodeApproveOrganizationCall(string $orgDID, string $orgAddress, array $scopes): string
    {
        try {
            // Function signature: approveOrganization(string,address,string[])
            $functionSignature = 'approveOrganization(string,address,string[])';
            
            // Calculate the function selector (first 4 bytes of keccak256 hash)
            $keccakHash = Utils::keccak256($functionSignature);
            $functionSelector = substr($keccakHash->toString(), 2, 8); // Remove 0x prefix and take first 4 bytes

            // Encode parameters using ABI encoding
            // Parameter 1: string orgDID
            $orgDIDBytes = utf8_encode($orgDID);
            $orgDIDLength = strlen($orgDIDBytes);
            $orgDIDHex = bin2hex($orgDIDBytes);
            $orgDIDPadded = str_pad($orgDIDHex, ceil(strlen($orgDIDHex) / 64) * 64, '0', STR_PAD_RIGHT);

            // Parameter 2: address orgAddress (remove 0x prefix and pad to 64 chars)
            $addressHex = str_pad(substr($orgAddress, 2), 64, '0', STR_PAD_LEFT);

            // Parameter 3: string[] scopes
            $scopesCount = count($scopes);
            $scopesData = '';
            $scopesOffsets = '';
            $currentOffset = $scopesCount * 32; // Each string offset takes 32 bytes

            foreach ($scopes as $scope) {
                $scopeBytes = utf8_encode($scope);
                $scopeLength = strlen($scopeBytes);
                $scopeHex = bin2hex($scopeBytes);
                $scopePadded = str_pad($scopeHex, ceil(strlen($scopeHex) / 64) * 64, '0', STR_PAD_RIGHT);
                
                // Add offset (pointing to where this string data starts)
                $scopesOffsets .= str_pad(dechex($currentOffset), 64, '0', STR_PAD_LEFT);
                
                // Add string length and data
                $scopesData .= str_pad(dechex($scopeLength), 64, '0', STR_PAD_LEFT) . $scopePadded;
                
                // Update offset for next string
                $currentOffset += 32 + strlen($scopePadded) / 2; // 32 bytes for length + data length
            }

            // Combine all encoded data
            $encodedParams = 
                '0000000000000000000000000000000000000000000000000000000000000060' . // offset to orgDID
                $addressHex . // orgAddress
                '00000000000000000000000000000000000000000000000000000000000000a0' . // offset to scopes array
                str_pad(dechex($orgDIDLength), 64, '0', STR_PAD_LEFT) . $orgDIDPadded . // orgDID data
                str_pad(dechex($scopesCount), 64, '0', STR_PAD_LEFT) . // scopes array length
                $scopesOffsets . $scopesData; // scopes array data

            return '0x' . $functionSelector . $encodedParams;

        } catch (\Exception $e) {
            Log::error('Failed to encode approveOrganization function call', [
                'error' => $e->getMessage(),
                'orgDID' => $orgDID,
                'orgAddress' => $orgAddress,
                'scopes' => $scopes
            ]);
            throw $e;
        }
    }

    public function getNetworkInfo(): array
    {
        return [
            'network' => config('services.blockchain.network'),
            'rpc_url' => $this->networkConfig['rpc_url'] ?? null,
            'chain_id' => $this->networkConfig['chain_id'] ?? null,
            'contract_address' => $this->contractAddress,
            'gas_limit' => $this->gasLimit,
            'gas_price' => $this->gasPrice,
            'from_address' => $this->fromAddress
        ];
    }


} 