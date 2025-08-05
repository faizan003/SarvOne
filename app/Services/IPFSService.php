<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IPFSService
{
    private $client;
    private $apiUrl;
    private $gatewayUrl;
    private $timeout;
    private $publicGateways;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.ipfs.api_url');
        $this->gatewayUrl = config('services.ipfs.gateway_url');
        $this->timeout = config('services.ipfs.timeout');
        $this->publicGateways = config('services.ipfs.public_gateways');
    }

    /**
     * Store a verifiable credential on IPFS
     *
     * @param array $vcData The verifiable credential data
     * @return array|null Returns array with hash and gateway URLs or null on failure
     */
    public function storeVC(array $vcData): ?array
    {
        try {
            // Convert VC data to JSON
            $jsonData = json_encode($vcData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            
            // Add to IPFS
            $response = $this->client->post($this->apiUrl . 'add', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $jsonData,
                        'filename' => 'vc_' . ($vcData['id'] ?? 'unknown') . '.json',
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ]
                    ]
                ],
                'timeout' => $this->timeout,
                'query' => [
                    'pin' => 'true', // Pin the file to prevent garbage collection
                    'wrap-with-directory' => 'false'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                $hash = $result['Hash'] ?? null;

                if ($hash) {
                    Log::info('VC stored on IPFS successfully', [
                        'vc_id' => $vcData['id'] ?? 'unknown',
                        'ipfs_hash' => $hash,
                        'size' => $result['Size'] ?? 0
                    ]);

                    return [
                        'hash' => $hash,
                        'size' => $result['Size'] ?? 0,
                        'gateway_urls' => $this->generateGatewayUrls($hash),
                        'primary_url' => $this->gatewayUrl . $hash
                    ];
                }
            }

            Log::error('Failed to store VC on IPFS', [
                'status_code' => $response->getStatusCode(),
                'response' => $response->getBody()->getContents()
            ]);

            return null;

        } catch (RequestException $e) {
            Log::error('IPFS storage request failed', [
                'error' => $e->getMessage(),
                'vc_id' => $vcData['id'] ?? 'unknown'
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('IPFS storage failed', [
                'error' => $e->getMessage(),
                'vc_id' => $vcData['id'] ?? 'unknown'
            ]);

            return null;
        }
    }

    /**
     * Retrieve a verifiable credential from IPFS
     *
     * @param string $hash The IPFS hash
     * @return array|null Returns the VC data or null on failure
     */
    public function retrieveVC(string $hash): ?array
    {
        try {
            // Try to get from cache first
            $cacheKey = 'ipfs_vc_' . $hash;
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData) {
                return $cachedData;
            }

            // Try primary gateway first
            $vcData = $this->fetchFromGateway($this->gatewayUrl . $hash);
            
            if (!$vcData) {
                // Try public gateways as fallback
                foreach ($this->publicGateways as $gateway) {
                    $vcData = $this->fetchFromGateway($gateway . $hash);
                    if ($vcData) {
                        break;
                    }
                }
            }

            if ($vcData) {
                // Cache for 1 hour
                Cache::put($cacheKey, $vcData, 3600);
                
                Log::info('VC retrieved from IPFS successfully', [
                    'ipfs_hash' => $hash
                ]);

                return $vcData;
            }

            Log::error('Failed to retrieve VC from IPFS', [
                'ipfs_hash' => $hash
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('IPFS retrieval failed', [
                'error' => $e->getMessage(),
                'ipfs_hash' => $hash
            ]);

            return null;
        }
    }

    /**
     * Fetch data from a specific IPFS gateway
     *
     * @param string $url The full gateway URL
     * @return array|null Returns the VC data or null on failure
     */
    private function fetchFromGateway(string $url): ?array
    {
        try {
            $response = $this->client->get($url, [
                'timeout' => $this->timeout,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            }

            return null;

        } catch (RequestException $e) {
            // Log but don't throw - we want to try other gateways
            Log::debug('IPFS gateway request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Generate gateway URLs for multiple IPFS gateways
     *
     * @param string $hash The IPFS hash
     * @return array Array of gateway URLs
     */
    private function generateGatewayUrls(string $hash): array
    {
        $urls = [];
        
        foreach ($this->publicGateways as $gateway) {
            $urls[] = $gateway . $hash;
        }

        return $urls;
    }

    /**
     * Pin a file to prevent garbage collection
     *
     * @param string $hash The IPFS hash
     * @return bool Success status
     */
    public function pinFile(string $hash): bool
    {
        try {
            $response = $this->client->post($this->apiUrl . 'pin/add', [
                'query' => [
                    'arg' => $hash
                ],
                'timeout' => $this->timeout
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('File pinned successfully', ['hash' => $hash]);
                return true;
            }

            return false;

        } catch (RequestException $e) {
            Log::error('Failed to pin file', [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if IPFS node is accessible
     *
     * @return bool True if accessible, false otherwise
     */
    public function isAccessible(): bool
    {
        try {
            $response = $this->client->get($this->apiUrl . 'version', [
                'timeout' => 5
            ]);

            return $response->getStatusCode() === 200;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get IPFS node information
     *
     * @return array|null Node information or null on failure
     */
    public function getNodeInfo(): ?array
    {
        try {
            $response = $this->client->get($this->apiUrl . 'version', [
                'timeout' => 10
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to get IPFS node info', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Validate IPFS hash format
     *
     * @param string $hash The hash to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidHash(string $hash): bool
    {
        // Basic validation for IPFS hash format
        // CIDv0: starts with Qm, 46 characters, base58
        // CIDv1: various formats, but typically longer
        
        if (strlen($hash) < 40) {
            return false;
        }

        // Check if it's a valid CIDv0 (starts with Qm)
        if (substr($hash, 0, 2) === 'Qm' && strlen($hash) === 46) {
            return preg_match('/^[A-Za-z0-9]+$/', $hash) === 1;
        }

        // For CIDv1 and other formats, do basic length and character check
        return preg_match('/^[A-Za-z0-9]+$/', $hash) === 1 && strlen($hash) >= 40;
    }
} 