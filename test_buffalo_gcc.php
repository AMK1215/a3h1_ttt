<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\BuffaloGameService;
use App\Models\User;

echo "=== Golden City Casino - Buffalo Game Test ===\n\n";

// Get a test user
$user = User::where('user_name', 'PLAYER0101')->first();

if (!$user) {
    echo "❌ Test user PLAYER0101 not found!\n";
    echo "Please create a test user first.\n";
    exit(1);
}

echo "✅ Test User Found:\n";
echo "   Username: {$user->user_name}\n";
echo "   Balance: {$user->balanceFloat}\n\n";

// Generate UID
$uid = BuffaloGameService::generateUid($user->user_name);
echo "✅ Generated UID:\n";
echo "   {$uid}\n";
echo "   Prefix: " . substr($uid, 0, 3) . " (should be 'gcc')\n";
echo "   Length: " . strlen($uid) . " (should be 32)\n\n";

// Generate Token
$token = BuffaloGameService::generatePersistentToken($user->user_name);
echo "✅ Generated Token:\n";
echo "   {$token}\n";
echo "   Length: " . strlen($token) . " (should be 64)\n\n";

// Test token verification
$isValid = BuffaloGameService::verifyToken($uid, $token);
echo "✅ Token Verification: " . ($isValid ? "PASSED ✓" : "FAILED ✗") . "\n\n";

// Generate game URL
$gameUrl = BuffaloGameService::getGameUrl($user, 2);
echo "✅ Game URL (Room 2 - 500 MMK):\n";
echo "   {$gameUrl}\n\n";

echo "=== Test Complete ===\n";
echo "\n📋 Next Steps:\n";
echo "1. Test get-user-balance endpoint\n";
echo "2. Test change-balance endpoint\n";
echo "3. Configure centralized API to forward to GCC\n";