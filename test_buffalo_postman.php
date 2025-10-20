<?php

/**
 * Buffalo Game - Generate Test Credentials for Postman
 * Run: php test_buffalo_postman.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\BuffaloGameService;

echo "═══════════════════════════════════════════════════\n";
echo "  Buffalo Game - Postman Test Credentials\n";
echo "═══════════════════════════════════════════════════\n\n";

// Get test user (or create one)
$testUsername = 'PLAYER0101';
$user = User::where('user_name', $testUsername)->first();

if (!$user) {
    echo "❌ User '{$testUsername}' not found!\n";
    echo "\nAvailable users:\n";
    $users = User::where('type', 40)->limit(5)->get();
    foreach ($users as $u) {
        echo "  - {$u->user_name} (ID: {$u->id}, Balance: {$u->balanceFloat} MMK)\n";
    }
    echo "\n";
    
    // Use first available user
    $user = $users->first();
    if (!$user) {
        echo "❌ No users found in database!\n";
        exit(1);
    }
    $testUsername = $user->user_name;
}

echo "✅ Test User: {$user->user_name}\n";
echo "   User ID: {$user->id}\n";
echo "   Current Balance: " . number_format($user->balanceFloat, 2) . " MMK\n\n";

// Generate credentials
$uid = BuffaloGameService::generateUid($user->user_name);
$token = BuffaloGameService::generatePersistentToken($user->user_name);

echo "═══════════════════════════════════════════════════\n";
echo "  CREDENTIALS FOR POSTMAN\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "UID:\n";
echo $uid . "\n\n";

echo "Token:\n";
echo $token . "\n\n";

echo "Username:\n";
echo $user->user_name . "\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "  API ENDPOINT TESTS\n";
echo "═══════════════════════════════════════════════════\n\n";

// Test 1: Get Balance
echo "📍 TEST 1: Get Balance\n";
echo "───────────────────────────────────────────────────\n";
echo "Method: POST\n";
echo "URL: https://ag.goldencitycasino123.site/api/buffalo/get-balance\n\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n\n";
echo "Body (raw JSON):\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Test 2: Place Bet (Loss)
echo "📍 TEST 2: Place Bet (Loss)\n";
echo "───────────────────────────────────────────────────\n";
echo "Method: POST\n";
echo "URL: https://ag.goldencitycasino123.site/api/buffalo/change-balance\n\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n\n";
echo "Body (raw JSON):\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => -500,  // Lost 500 MMK
    'bet' => -500,          // Bet amount
    'win' => 0,             // No win
    'gameId' => 23
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Test 3: Win (Small Win)
echo "📍 TEST 3: Small Win\n";
echo "───────────────────────────────────────────────────\n";
echo "Method: POST\n";
echo "URL: https://ag.goldencitycasino123.site/api/buffalo/change-balance\n\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n\n";
echo "Body (raw JSON):\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 200,   // Won 700 MMK total (500 bet + 200 profit)
    'bet' => -500,          // Bet amount
    'win' => 700,           // Total win
    'gameId' => 23
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Test 4: Big Win
echo "📍 TEST 4: Big Win 🎉\n";
echo "───────────────────────────────────────────────────\n";
echo "Method: POST\n";
echo "URL: https://ag.goldencitycasino123.site/api/buffalo/change-balance\n\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n\n";
echo "Body (raw JSON):\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 4500,  // Won 5000 MMK total (500 bet + 4500 profit)
    'bet' => -500,          // Bet amount
    'win' => 5000,          // Total win
    'gameId' => 23
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Test 5: Jackpot
echo "📍 TEST 5: Jackpot! 💎\n";
echo "───────────────────────────────────────────────────\n";
echo "Method: POST\n";
echo "URL: https://ag.goldencitycasino123.site/api/buffalo/change-balance\n\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n\n";
echo "Body (raw JSON):\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 49000, // Won 50000 MMK total (1000 bet + 49000 profit)
    'bet' => -1000,         // Bet amount
    'win' => 50000,         // Total win
    'gameId' => 23
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "  EXPECTED RESPONSES\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "✅ Success Response:\n";
echo json_encode([
    'code' => 1,
    'msg' => 'Success',
    'balance' => 5000  // Current balance
], JSON_PRETTY_PRINT) . "\n\n";

echo "❌ Error Response (Invalid Token):\n";
echo json_encode([
    'code' => 0,
    'msg' => 'Invalid token'
], JSON_PRETTY_PRINT) . "\n\n";

echo "❌ Error Response (User Not Found):\n";
echo json_encode([
    'code' => 0,
    'msg' => 'User not found'
], JSON_PRETTY_PRINT) . "\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "  VERIFICATION\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "To verify token is correct:\n";
$isValid = BuffaloGameService::verifyToken($uid, $token);
echo "Token Valid: " . ($isValid ? '✅ YES' : '❌ NO') . "\n\n";

echo "To extract username from UID:\n";
$extractedUsername = BuffaloGameService::extractUserNameFromUid($uid);
echo "Extracted Username: " . ($extractedUsername ?? 'NULL') . "\n";
echo "Matches Original: " . ($extractedUsername === $user->user_name ? '✅ YES' : '❌ NO') . "\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "  POSTMAN COLLECTION (Import This)\n";
echo "═══════════════════════════════════════════════════\n\n";

$postmanCollection = [
    'info' => [
        'name' => 'Buffalo Game API Tests',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ],
    'item' => [
        [
            'name' => 'Get Balance',
            'request' => [
                'method' => 'POST',
                'header' => [
                    ['key' => 'Content-Type', 'value' => 'application/json']
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode([
                        'uid' => $uid,
                        'token' => $token
                    ], JSON_PRETTY_PRINT)
                ],
                'url' => [
                    'raw' => 'https://ag.goldencitycasino123.site/api/buffalo/get-balance',
                    'protocol' => 'http',
                    'host' => ['localhost'],
                    'path' => ['api', 'buffalo', 'get-balance']
                ]
            ]
        ],
        [
            'name' => 'Change Balance - Loss',
            'request' => [
                'method' => 'POST',
                'header' => [
                    ['key' => 'Content-Type', 'value' => 'application/json']
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode([
                        'uid' => $uid,
                        'token' => $token,
                        'changemoney' => -500,
                        'bet' => -500,
                        'win' => 0,
                        'gameId' => 23
                    ], JSON_PRETTY_PRINT)
                ],
                'url' => [
                    'raw' => 'https://ag.goldencitycasino123.site/api/buffalo/change-balance',
                    'protocol' => 'http',
                    'host' => ['localhost'],
                    'path' => ['api', 'buffalo', 'change-balance']
                ]
            ]
        ],
        [
            'name' => 'Change Balance - Big Win',
            'request' => [
                'method' => 'POST',
                'header' => [
                    ['key' => 'Content-Type', 'value' => 'application/json']
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode([
                        'uid' => $uid,
                        'token' => $token,
                        'changemoney' => 4500,
                        'bet' => -500,
                        'win' => 5000,
                        'gameId' => 23
                    ], JSON_PRETTY_PRINT)
                ],
                'url' => [
                    'raw' => 'https://ag.goldencitycasino123.site/api/buffalo/change-balance',
                    'protocol' => 'http',
                    'host' => ['localhost'],
                    'path' => ['api', 'buffalo', 'change-balance']
                ]
            ]
        ]
    ]
];

$collectionFile = 'buffalo_postman_collection.json';
file_put_contents($collectionFile, json_encode($postmanCollection, JSON_PRETTY_PRINT));
echo "✅ Postman collection saved to: {$collectionFile}\n";
echo "   Import this file in Postman: File > Import > {$collectionFile}\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "  Quick Copy-Paste for Postman\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "Copy this entire JSON for the body:\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => -500,
    'bet' => -500,
    'win' => 0,
    'gameId' => 23
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "✅ Test credentials generated successfully!\n";
echo "═══════════════════════════════════════════════════\n";

