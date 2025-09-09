# Shan Game API Documentation

## Overview

The Shan Game API provides comprehensive access to gaming transaction data, player reports, and member transaction details. This API is designed for gaming operators, agents, and third-party integrations who need real-time access to gaming data.

## Base URL

```
https://luckymillion.pro/api
```

## Authentication

All API endpoints require proper authentication. Contact our support team to obtain your API credentials.

## Rate Limits

- **Standard Plan**: 100 requests per minute
- **Premium Plan**: 500 requests per minute
- **Enterprise Plan**: 1000 requests per minute

## Response Format

All API responses follow a consistent JSON structure:

```json
{
    "status": "Request was successful.",
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    }
}
```

## Error Handling

### Error Response Format

```json
{
    "status": "Error",
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### HTTP Status Codes

- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limit Exceeded
- `500` - Internal Server Error

---

## API Endpoints

### 1. Report Transactions

Get aggregated report transactions grouped by agent and member account.

#### Endpoint
```
POST /report-transactions
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `agent_code` | string | Yes | Agent code to filter by (e.g., "SCT931") |
| `date_from` | date | No | Start date filter (YYYY-MM-DD) |
| `date_to` | date | No | End date filter (YYYY-MM-DD) |
| `member_account` | string | No | Specific member account to filter by |
| `group_by` | string | No | Grouping option: `agent_id`, `member_account`, or `both` (default: `both`) |

#### Request Example

```json
{
    "agent_code": "SCT931",
    "date_from": "2025-01-01",
    "date_to": "2025-01-31",
    "member_account": "PLAYER0101",
    "group_by": "both"
}
```

#### Response Example

```json
{
    "status": "Request was successful.",
    "message": "Report transactions retrieved successfully",
    "data": {
        "agent_info": {
            "agent_id": 15,
            "agent_code": "SCT931",
            "agent_name": "TTTGaming"
        },
        "filters": {
            "date_from": "2025-01-01",
            "date_to": "2025-01-31",
            "member_account": "PLAYER0101",
            "group_by": "both"
        },
        "report_data": [
            {
                "agent_id": 15,
                "member_account": "PLAYER0101",
                "total_transactions": 39,
                "total_transaction_amount": "4260.00",
                "total_bet_amount": "4080.00",
                "total_valid_amount": "4080.00",
                "avg_before_balance": "4053.92",
                "avg_after_balance": "4025.71",
                "first_transaction": "2025-09-06 08:05:45",
                "last_transaction": "2025-09-06 10:08:34",
                "agent": {
                    "id": 15,
                    "user_name": "AG19930285",
                    "name": "TTTGaming"
                }
            }
        ],
        "summary": {
            "total_groups": 1,
            "total_transactions": 39,
            "total_transaction_amount": "4,260.00",
            "total_bet_amount": "4,080.00",
            "total_valid_amount": "4,080.00",
            "unique_agents": 1,
            "unique_members": 1
        }
    }
}
```

#### Response Fields

**Agent Info:**
- `agent_id` - Unique agent identifier
- `agent_code` - Agent code used for filtering
- `agent_name` - Display name of the agent

**Report Data:**
- `agent_id` - Agent identifier
- `member_account` - Member account identifier
- `total_transactions` - Total number of transactions
- `total_transaction_amount` - Sum of all transaction amounts
- `total_bet_amount` - Sum of all bet amounts
- `total_valid_amount` - Sum of all valid amounts
- `avg_before_balance` - Average balance before transactions
- `avg_after_balance` - Average balance after transactions
- `first_transaction` - Timestamp of first transaction
- `last_transaction` - Timestamp of last transaction

**Summary:**
- `total_groups` - Number of groups returned
- `total_transactions` - Total transactions across all groups
- `total_transaction_amount` - Total transaction amount across all groups
- `total_bet_amount` - Total bet amount across all groups
- `total_valid_amount` - Total valid amount across all groups
- `unique_agents` - Number of unique agents (when grouped by agent)
- `unique_members` - Number of unique members (when grouped by member)

---

### 2. Member Transactions

Get individual transaction details for a specific member account.

#### Endpoint
```
POST /member-transactions
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `agent_code` | string | Yes | Agent code to filter by |
| `member_account` | string | Yes | Member account to get transactions for |
| `date_from` | date | No | Start date filter (YYYY-MM-DD) |
| `date_to` | date | No | End date filter (YYYY-MM-DD) |
| `limit` | integer | No | Maximum number of records to return (1-100, default: 50) |

#### Request Example

```json
{
    "agent_code": "SCT931",
    "member_account": "PLAYER0101",
    "date_from": "2025-01-01",
    "date_to": "2025-01-31",
    "limit": 20
}
```

#### Response Example

```json
{
    "status": "Request was successful.",
    "message": "Member transactions retrieved successfully",
    "data": {
        "agent_info": {
            "agent_id": 15,
            "agent_code": "SCT931",
            "agent_name": "TTTGaming"
        },
        "member_account": "PLAYER0101",
        "filters": {
            "date_from": "2025-01-01",
            "date_to": "2025-01-31",
            "limit": 20
        },
        "transactions": [
            {
                "id": 243,
                "user_id": 16,
                "transaction_amount": "30.00",
                "bet_amount": "30.00",
                "valid_amount": "30.00",
                "status": "1",
                "banker": "0",
                "before_balance": "3525.20",
                "after_balance": "3555.20",
                "created_at": "2025-09-06T10:44:31.000000Z",
                "updated_at": "2025-09-06T10:44:31.000000Z",
                "agent_id": 15,
                "member_account": "PLAYER0101",
                "settled_status": "settled_win",
                "wager_code": "hl1pIjmevmVU",
                "agent_code": "SCT931",
                "agent": {
                    "id": 15,
                    "user_name": "AG19930285",
                    "name": "TTTGaming"
                }
            }
        ],
        "total_found": 20
    }
}
```

#### Response Fields

**Transaction Details:**
- `id` - Unique transaction identifier
- `user_id` - User identifier
- `transaction_amount` - Amount of the transaction
- `bet_amount` - Bet amount for this transaction
- `valid_amount` - Valid amount for this transaction
- `status` - Transaction status (`1` = Win, `0` = Loss)
- `banker` - Banker indicator (`0` = Player, `1` = Banker)
- `before_balance` - Player balance before transaction
- `after_balance` - Player balance after transaction
- `created_at` - Transaction creation timestamp
- `updated_at` - Transaction last update timestamp
- `agent_id` - Agent identifier
- `member_account` - Member account identifier
- `settled_status` - Settlement status (`settled_win`, `settled_loss`, `pending`)
- `wager_code` - Unique wager identifier
- `agent_code` - Agent code

---

## Data Types

### Date Format
All dates should be provided in `YYYY-MM-DD` format.

### Timestamp Format
All timestamps are in ISO 8601 format: `YYYY-MM-DDTHH:mm:ss.ssssssZ`

### Status Values

#### Transaction Status
- `1` - Win
- `0` - Loss

#### Settled Status
- `settled_win` - Transaction settled as win
- `settled_loss` - Transaction settled as loss
- `pending` - Transaction pending settlement

#### Banker Status
- `0` - Player transaction
- `1` - Banker transaction

---

## Usage Examples

### cURL Examples

#### Get Report Transactions
```bash
curl -X POST https://luckymillion.pro/api/report-transactions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "agent_code": "SCT931",
    "date_from": "2025-01-01",
    "date_to": "2025-01-31",
    "group_by": "both"
  }'
```

#### Get Member Transactions
```bash
curl -X POST https://luckymillion.pro/api/member-transactions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "agent_code": "SCT931",
    "member_account": "PLAYER0101",
    "limit": 50
  }'
```

### JavaScript Examples

#### Fetch Report Transactions
```javascript
const response = await fetch('https://luckymillion.pro/api/report-transactions', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_TOKEN'
  },
  body: JSON.stringify({
    agent_code: 'SCT931',
    date_from: '2025-01-01',
    date_to: '2025-01-31',
    group_by: 'both'
  })
});

const data = await response.json();
console.log(data);
```

#### Fetch Member Transactions
```javascript
const response = await fetch('https://luckymillion.pro/api/member-transactions', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_TOKEN'
  },
  body: JSON.stringify({
    agent_code: 'SCT931',
    member_account: 'PLAYER0101',
    limit: 20
  })
});

const data = await response.json();
console.log(data);
```

### PHP Examples

#### Get Report Transactions
```php
<?php
$url = 'https://luckymillion.pro/api/report-transactions';
$data = [
    'agent_code' => 'SCT931',
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31',
    'group_by' => 'both'
];

$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer YOUR_API_TOKEN'
        ],
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

print_r($response);
?>
```

---

## SDKs and Libraries

### Official SDKs
- **PHP SDK**: Available on GitHub
- **JavaScript SDK**: Available on NPM
- **Python SDK**: Available on PyPI

### Community Libraries
- **Laravel Package**: `shan/game-api`
- **Node.js Package**: `shan-game-api`
- **Python Package**: `shan-game-api`

---

## Support and Contact

### Technical Support
- **Email**: api-support@luckymillion.pro
- **Documentation**: https://docs.luckymillion.pro
- **Status Page**: https://status.luckymillion.pro

### Business Inquiries
- **Email**: business@luckymillion.pro
- **Phone**: +1-555-0123
- **Sales**: sales@luckymillion.pro

### API Status
Check our status page for real-time API availability and performance metrics.

---

## Changelog

### Version 1.2.0 (2025-01-15)
- Added member transactions endpoint
- Enhanced error handling
- Improved response formatting

### Version 1.1.0 (2025-01-01)
- Added date filtering to report transactions
- Added grouping options
- Enhanced summary statistics

### Version 1.0.0 (2024-12-01)
- Initial API release
- Report transactions endpoint
- Basic authentication

---

## License

This API documentation is proprietary and confidential. Unauthorized distribution is prohibited.

© 2025 LuckyMillion Pro. All rights reserved.
