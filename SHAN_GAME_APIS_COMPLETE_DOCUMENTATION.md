# Shan Game APIs - Complete Documentation

## Overview

This document provides comprehensive API documentation for all Shan Game related endpoints. These APIs handle game transactions, balance management, game launching, and callback processing for the Shan gaming platform.

## Base URL

```
https://your-domain.com/api
```

## Authentication

- **Bearer Token**: Required for authenticated endpoints (using Laravel Sanctum)
- **Signature Authentication**: Required for provider callbacks (MD5 signature verification)
- **API Key**: Some endpoints require API key authentication

---

## API Endpoints

### 1. Get Balance (Shan)

Retrieve player balance information for Shan games.

#### Endpoint
```
POST /api/shan/balance
```

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
    "batch_requests": [
        {
            "member_account": "PLAYER001",
            "product_code": 100200
        }
    ],
    "operator_code": "SCT931",
    "currency": "MMK",
    "sign": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "request_time": 1640995200
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `batch_requests` | array | Yes | Array of balance requests |
| `batch_requests[].member_account` | string | Yes | Player account identifier |
| `batch_requests[].product_code` | integer | Yes | Product/game code |
| `operator_code` | string | Yes | Operator identifier |
| `currency` | string | Yes | Currency code (MMK only) |
| `sign` | string | Yes | MD5 signature for authentication |
| `request_time` | integer | Yes | Unix timestamp |

#### Response Example
```json
{
    "status": "success",
    "data": [
        {
            "member_account": "PLAYER001",
            "product_code": 100200,
            "balance": 1500.50,
            "code": 0,
            "message": "Success"
        }
    ]
}
```

#### Response Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Invalid Signature |
| 2 | Member Not Found |
| 3 | Internal Server Error |

---

### 2. Balance Update Callback

Handle balance updates from the game provider.

#### Endpoint
```
POST /api/shan/client/balance-update
```

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
    "wager_code": "hl1pIjmevmVU",
    "game_type_id": 15,
    "players": [
        {
            "player_id": "PLAYER001",
            "balance": 1200.75
        }
    ],
    "banker_balance": 50000.00,
    "timestamp": "2025-01-15T10:30:00Z",
    "total_player_net": -299.25,
    "banker_amount_change": 299.25
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `wager_code` | string | Yes | Unique wager identifier |
| `game_type_id` | integer | No | Game type identifier |
| `players` | array | Yes | Array of player data |
| `players[].player_id` | string | Yes | Player identifier |
| `players[].balance` | numeric | Yes | New player balance |
| `banker_balance` | numeric | No | Banker's balance |
| `timestamp` | string | Yes | ISO timestamp |
| `total_player_net` | numeric | No | Total player net amount |
| `banker_amount_change` | numeric | No | Banker amount change |

#### Response Example
```json
{
    "status": "success",
    "code": "SUCCESS",
    "message": "Balances updated successfully."
}
```

#### Error Response
```json
{
    "status": "error",
    "code": "INVALID_REQUEST_DATA",
    "message": "Invalid request data: The given data was invalid."
}
```

---

### 3. Launch Game (Shan)

Launch a Shan game for an authenticated player.

#### Endpoint
```
POST /api/shankomee/launch-game
```

#### Authentication
```
Authorization: Bearer {token}
```

#### Request Headers
```
Content-Type: application/json
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

#### Request Body
```json
{
    "product_code": 1002,
    "game_type": "slot",
    "nickname": "PlayerNick"
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `product_code` | integer | Yes | Product/game code |
| `game_type` | string | Yes | Type of game (slot, live, etc.) |
| `nickname` | string | No | Player nickname |

#### Response Example
```json
{
    "code": 200,
    "message": "Game launched successfully",
    "url": "https://goldendragon7.pro/?user_name=PLAYER001&balance=1500.50"
}
```

#### Error Response
```json
{
    "code": 401,
    "message": "Authentication required. Please log in."
}
```

---

### 4. Shankomee Get Balance

Get balance information for Shankomee integration.

#### Endpoint
```
POST /api/shan/shangetbalance
```

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
    "batch_requests": [
        {
            "member_account": "PLAYER001",
            "product_code": 1002,
            "balance": 1500.50
        }
    ],
    "operator_code": "SCT931",
    "currency": "MMK",
    "request_time": 1640995200,
    "sign": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `batch_requests` | array | Yes | Array of balance requests |
| `batch_requests[].member_account` | string | Yes | Player account identifier |
| `batch_requests[].product_code` | integer | Yes | Product/game code |
| `batch_requests[].balance` | numeric | Yes | Current balance from external source |
| `operator_code` | string | Yes | Operator identifier |
| `currency` | string | Yes | Currency code |
| `request_time` | integer | Yes | Unix timestamp |
| `sign` | string | Yes | MD5 signature |

#### Response Example
```json
{
    "status": "success",
    "data": [
        {
            "member_account": "PLAYER001",
            "product_code": 1002,
            "balance": 1500.50,
            "currency": "MMK",
            "status": "success"
        }
    ]
}
```

---

### 5. Create Transaction

Create a new game transaction with player and banker data.

#### Endpoint
```
POST /api/transactions
```

#### Middleware
```
transaction
```

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
    "banker": {
        "player_id": "BANKER001"
    },
    "players": [
        {
            "player_id": "PLAYER001",
            "bet_amount": 100.00,
            "win_lose_status": 1
        },
        {
            "player_id": "PLAYER002",
            "bet_amount": 50.00,
            "win_lose_status": 0
        }
    ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `banker` | object | Yes | Banker information |
| `banker.player_id` | string | Yes | Banker player identifier |
| `players` | array | Yes | Array of player data |
| `players[].player_id` | string | Yes | Player identifier |
| `players[].bet_amount` | numeric | Yes | Bet amount (min: 0) |
| `players[].win_lose_status` | integer | Yes | Win (1) or Lose (0) |

#### Response Example
```json
{
    "status": "success",
    "message": "Transaction Successful",
    "data": [
        {
            "player_id": "PLAYER001",
            "balance": 1100.00
        },
        {
            "player_id": "PLAYER002",
            "balance": 1450.00
        },
        {
            "player_id": "BANKER001",
            "balance": 4950.00
        }
    ]
}
```

#### Error Response
```json
{
    "status": "error",
    "message": "Duplicate transaction!",
    "details": "This round already settled."
}
```

---

## Signature Generation

### MD5 Signature for Shan APIs

The signature is generated using the following formula:

```php
$signature = md5($operator_code . $request_time . 'getbalance' . $secret_key);
```

#### Example
```php
$operator_code = "SCT931";
$request_time = 1640995200;
$action = "getbalance";
$secret_key = "your_secret_key_here";

$signature = md5($operator_code . $request_time . $action . $secret_key);
// Result: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

---

## Postman Collection

### Environment Variables

Create a Postman environment with the following variables:

```json
{
    "base_url": "https://your-domain.com/api",
    "bearer_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "operator_code": "SCT931",
    "secret_key": "your_secret_key_here",
    "member_account": "PLAYER001",
    "product_code": 1002
}
```

### Pre-request Scripts

Add this script to generate signatures automatically:

```javascript
// Generate timestamp
pm.environment.set("request_time", Math.floor(Date.now() / 1000));

// Generate signature
const operator_code = pm.environment.get("operator_code");
const request_time = pm.environment.get("request_time");
const action = "getbalance";
const secret_key = pm.environment.get("secret_key");

const signature = CryptoJS.MD5(operator_code + request_time + action + secret_key).toString();
pm.environment.set("signature", signature);
```

---

## Error Handling

### Common Error Codes

| HTTP Status | Code | Description |
|-------------|------|-------------|
| 200 | SUCCESS | Request successful |
| 400 | INVALID_REQUEST_DATA | Invalid request parameters |
| 401 | UNAUTHORIZED | Authentication required |
| 403 | INVALID_SIGNATURE | Signature verification failed |
| 404 | MEMBER_NOT_FOUND | Player not found |
| 409 | DUPLICATE_TRANSACTION | Transaction already processed |
| 422 | VALIDATION_ERROR | Request validation failed |
| 500 | INTERNAL_SERVER_ERROR | Server error |

### Error Response Format

```json
{
    "status": "error",
    "code": "ERROR_CODE",
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

---

## Testing Examples

### cURL Examples

#### Get Balance
```bash
curl -X POST https://your-domain.com/api/shan/balance \
  -H "Content-Type: application/json" \
  -d '{
    "batch_requests": [
        {
            "member_account": "PLAYER001",
            "product_code": 1002
        }
    ],
    "operator_code": "SCT931",
    "currency": "MMK",
    "sign": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "request_time": 1640995200
  }'
```

#### Launch Game
```bash
curl -X POST https://your-domain.com/api/shankomee/launch-game \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "product_code": 1002,
    "game_type": "slot"
  }'
```

#### Create Transaction
```bash
curl -X POST https://your-domain.com/api/transactions \
  -H "Content-Type: application/json" \
  -d '{
    "banker": {
        "player_id": "BANKER001"
    },
    "players": [
        {
            "player_id": "PLAYER001",
            "bet_amount": 100.00,
            "win_lose_status": 1
        }
    ]
  }'
```

### JavaScript Examples

#### Get Balance
```javascript
const response = await fetch('https://your-domain.com/api/shan/balance', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    batch_requests: [
      {
        member_account: 'PLAYER001',
        product_code: 1002
      }
    ],
    operator_code: 'SCT931',
    currency: 'MMK',
    sign: 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
    request_time: Math.floor(Date.now() / 1000)
  })
});

const data = await response.json();
console.log(data);
```

#### Launch Game
```javascript
const response = await fetch('https://your-domain.com/api/shankomee/launch-game', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    product_code: 1002,
    game_type: 'slot'
  })
});

const data = await response.json();
console.log(data);
```

---

## Rate Limits

- **Standard**: 100 requests per minute
- **Premium**: 500 requests per minute
- **Enterprise**: 1000 requests per minute

## Security

- All API calls use HTTPS
- Signature verification for provider callbacks
- Bearer token authentication for player endpoints
- Input validation and sanitization
- Idempotency checks for transactions

## Support

For technical support or questions:
- **Email**: api-support@your-domain.com
- **Documentation**: https://docs.your-domain.com
- **Status Page**: https://status.your-domain.com

---

© 2025 Your Company. All rights reserved.
