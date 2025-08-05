<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SecureVerify Services Configuration
    |--------------------------------------------------------------------------
    */

    'ipfs' => [
        'gateway_url' => env('IPFS_GATEWAY_URL', 'https://ipfs.io/ipfs/'),
        'api_url' => env('IPFS_API_URL', 'http://localhost:5001/api/v0/'),
        'timeout' => env('IPFS_TIMEOUT', 30),
        'public_gateways' => [
            'https://ipfs.io/ipfs/',
            'https://gateway.pinata.cloud/ipfs/',
            'https://cloudflare-ipfs.com/ipfs/',
        ],
    ],

    'blockchain' => [
        'network' => env('BLOCKCHAIN_NETWORK', 'polygon'),
        'polygon' => [
            'rpc_url' => env('POLYGON_RPC_URL', 'https://rpc-amoy.polygon.technology'),
            'chain_id' => env('POLYGON_CHAIN_ID', 80002),
            'explorer_url' => env('POLYGON_EXPLORER_URL', 'https://amoy.polygonscan.com'),
            'contract_address' => env('POLYGON_CONTRACT_ADDRESS'),
            'gas_limit' => env('POLYGON_GAS_LIMIT', 1000000),
            'gas_price' => env('POLYGON_GAS_PRICE', 30), // Gwei
        ],
        'ethereum' => [
            'rpc_url' => env('ETHEREUM_RPC_URL', 'https://mainnet.infura.io/v3/'),
            'chain_id' => env('ETHEREUM_CHAIN_ID', 1),
            'explorer_url' => env('ETHEREUM_EXPLORER_URL', 'https://etherscan.io'),
            'contract_address' => env('ETHEREUM_CONTRACT_ADDRESS'),
            'gas_limit' => env('ETHEREUM_GAS_LIMIT', 200000),
            'gas_price' => env('ETHEREUM_GAS_PRICE', 20), // Gwei
        ],
        'private_key' => env('BLOCKCHAIN_PRIVATE_KEY'),
        'timeout' => env('BLOCKCHAIN_TIMEOUT', 60),
    ],

    'secureverify' => [
        'did_prefix' => env('SECUREVERIFY_DID_PREFIX', 'did:secureverify:'),
        'vc_context' => env('SECUREVERIFY_VC_CONTEXT', 'https://secureverify.in/credentials/v1'),
        'api_version' => env('SECUREVERIFY_API_VERSION', '1.0'),
        'timezone' => env('SECUREVERIFY_TIMEZONE', 'Asia/Kolkata'),
    ],

    'blockchain_service' => [
        'url' => env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003'),
        'timeout' => env('BLOCKCHAIN_SERVICE_TIMEOUT', 30),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_PHONE_NUMBER', '+15005550006'),
    ],

                'pinata' => [
        'jwt_key' => env('PINATA_JWT_KEY'),
    ],

];
