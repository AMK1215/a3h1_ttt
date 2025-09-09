# Postman Testing Guide for Launch Game APIs

## Environment Variables Setup

First, create a Postman Environment with these variables:

### Environment Variables
```json
{
  "base_url": "http://localhost:8000/api",
  "bearer_token": "your_bearer_token_here",
  "agent_code": "YOUR_AGENT_CODE",
  "secret_key": "YOUR_SECRET_KEY",
  "member_account": "test_player_001",
  "product_code": 1002,
  "game_type": "slot",
  "request_time": "",
  "signature": ""
}
```

## 1. Client Launch Game API Test

### Request Details
- **Method**: `POST`
- **URL**: `{{base_url}}/shankomee/launch-game`
- **Headers**:
  ```
  Authorization: Bearer {{bearer_token}}
  Content-Type: application/json
  Accept: application/json
  ```

### Request Body
```json
{
  "product_code": {{product_code}},
  "game_type": "{{game_type}}",
  "nickname": "Test Player"
}
```

### Pre-request Script (for automatic signature generation)
```javascript
// Generate timestamp
pm.environment.set("request_time", Math.floor(Date.now() / 1000));

// Generate signature
const agent_code = pm.environment.get("agent_code");
const request_time = pm.environment.get("request_time");
const secret_key = pm.environment.get("secret_key");

const signature = CryptoJS.MD5(request_time + secret_key + 'launchgame' + agent_code).toString();
pm.environment.set("signature", signature);

console.log("Generated signature:", signature);
console.log("Request time:", request_time);
```

## 2. Provider Launch Game API Test

### Request Details
- **Method**: `POST`
- **URL**: `{{base_url}}/provider/launch-game`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  ```

### Request Body
```json
{
  "agent_code": "{{agent_code}}",
  "product_code": {{product_code}},
  "game_type": "{{game_type}}",
  "member_account": "{{member_account}}",
  "balance": 1000.00,
  "request_time": {{request_time}},
  "sign": "{{signature}}",
  "nickname": "Test Player",
  "callback_url": "https://your-callback-url.com/webhook"
}
```

### Pre-request Script (for provider test)
```javascript
// Generate timestamp
pm.environment.set("request_time", Math.floor(Date.now() / 1000));

// Generate signature
const agent_code = pm.environment.get("agent_code");
const request_time = pm.environment.get("request_time");
const secret_key = pm.environment.get("secret_key");

const signature = CryptoJS.MD5(request_time + secret_key + 'launchgame' + agent_code).toString();
pm.environment.set("signature", signature);

console.log("Generated signature:", signature);
console.log("Request time:", request_time);
```

## 3. Authentication Test (Get Bearer Token)

### Request Details
- **Method**: `POST`
- **URL**: `{{base_url}}/login`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  ```

### Request Body
```json
{
  "email": "your_email@example.com",
  "password": "your_password"
}
```

### Test Script (to save token)
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.token) {
        pm.environment.set("bearer_token", response.token);
        console.log("Token saved:", response.token);
    }
}
```

## 4. Step-by-Step Testing Process

### Step 1: Get Authentication Token
1. Use the login request above
2. Copy the token from response
3. Set it in your environment variable `bearer_token`

### Step 2: Test Client Launch Game
1. Make sure you're authenticated
2. Send the client launch game request
3. Check the response for the game URL

### Step 3: Test Provider Launch Game
1. Use the provider request (no authentication needed)
2. The signature will be automatically generated
3. Check the response for the game URL

## 5. Expected Responses

### Successful Client Response
```json
{
  "code": 200,
  "message": "Game launched successfully",
  "url": "https://golden-mm-shan.vercel.app/?user_name=test_player_001&balance=1000"
}
```

### Successful Provider Response
```json
{
  "code": 200,
  "message": "Game launched successfully",
  "url": "https://golden-mm-shan.vercel.app/?user_name=test_player_001&balance=1000"
}
```

### Error Response (Invalid Signature)
```json
{
  "code": 401,
  "message": "Invalid signature"
}
```

## 6. Testing Different Scenarios

### Test Case 1: Valid Request
- Use correct agent_code, secret_key, and signature
- Should return 200 with game URL

### Test Case 2: Invalid Signature
- Change the signature in the request
- Should return 401 error

### Test Case 3: Missing Required Fields
- Remove required fields like `request_time` or `sign`
- Should return 422 validation error

### Test Case 4: Invalid Agent Code
- Use non-existent agent_code
- Should return 404 error

## 7. Debugging Tips

### Check Logs
- Monitor Laravel logs for detailed error information
- Look for signature generation logs

### Verify Signature Generation
- Use the pre-request script to see generated signature
- Compare with what the server expects

### Test Signature Manually
```php
// PHP code to test signature generation
$request_time = time();
$secret_key = "your_secret_key";
$agent_code = "your_agent_code";
$signature = md5($request_time . $secret_key . 'launchgame' . $agent_code);
echo "Signature: " . $signature;
```

## 8. Postman Collection Export

You can export this as a Postman collection with the following structure:

```json
{
  "info": {
    "name": "Launch Game API Tests",
    "description": "Testing client and provider launch game APIs"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"{{email}}\",\n  \"password\": \"{{password}}\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/login",
              "host": ["{{base_url}}"],
              "path": ["login"]
            }
          }
        }
      ]
    },
    {
      "name": "Client APIs",
      "item": [
        {
          "name": "Client Launch Game",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{bearer_token}}"
              },
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"product_code\": {{product_code}},\n  \"game_type\": \"{{game_type}}\",\n  \"nickname\": \"Test Player\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/shankomee/launch-game",
              "host": ["{{base_url}}"],
              "path": ["shankomee", "launch-game"]
            }
          }
        }
      ]
    },
    {
      "name": "Provider APIs",
      "item": [
        {
          "name": "Provider Launch Game",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"agent_code\": \"{{agent_code}}\",\n  \"product_code\": {{product_code}},\n  \"game_type\": \"{{game_type}}\",\n  \"member_account\": \"{{member_account}}\",\n  \"balance\": 1000.00,\n  \"request_time\": {{request_time}},\n  \"sign\": \"{{signature}}\",\n  \"nickname\": \"Test Player\",\n  \"callback_url\": \"https://your-callback-url.com/webhook\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/provider/launch-game",
              "host": ["{{base_url}}"],
              "path": ["provider", "launch-game"]
            }
          }
        }
      ]
    }
  ]
}
```

## 9. Route Setup

Make sure you have the provider route in your `routes/api.php`:

```php
Route::post('/provider/launch-game', [App\Http\Controllers\Api\V1\Game\ProviderLaunchGameController::class, 'launchGameForClient']);
```

This guide will help you thoroughly test both the client and provider launch game APIs with proper signature verification!
