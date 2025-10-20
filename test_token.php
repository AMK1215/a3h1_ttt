<?php
// Bootstrap Laravel application
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\BuffaloGameService;
use App\Models\User;

echo "=== Buffalo Game Token Generator ===\n\n";

// Find a test player
$user = User::where('user_name', 'PLAYER0101')->first(); // Change to your player username

if ($user) {
    echo "Found user: {$user->user_name} (ID: {$user->id})\n";
    echo "User balance: {$user->balanceFloat}\n\n";
    
    $auth = BuffaloGameService::generateBuffaloAuth($user);
    
    echo "Generated Buffalo Auth:\n";
    echo "UID: " . $auth['uid'] . "\n";
    echo "Token: " . $auth['token'] . "\n\n";
    
    // Verify it works
    $isValid = BuffaloGameService::verifyToken($auth['uid'], $auth['token']);
    echo "Token Verification: " . ($isValid ? '✅ VALID' : '❌ INVALID') . "\n\n";
    
    // Debug: Show what the verification process does
    echo "=== Debug Info ===\n";
    $extractedUsername = BuffaloGameService::extractUserNameFromUid($auth['uid']);
    echo "Extracted username: " . ($extractedUsername ?: 'NULL') . "\n";
    
    if ($extractedUsername) {
        $foundUser = User::where('user_name', $extractedUsername)->first();
        if ($foundUser) {
            echo "Found user for verification: {$foundUser->user_name} (ID: {$foundUser->id})\n";
            $expectedToken = BuffaloGameService::generatePersistentToken($foundUser);
            echo "Expected token: {$expectedToken}\n";
            echo "Provided token: {$auth['token']}\n";
            echo "Tokens match: " . (hash_equals($expectedToken, $auth['token']) ? 'YES' : 'NO') . "\n";
        } else {
            echo "❌ User not found for extracted username\n";
        }
    }
    echo "\n";
    
    echo "=== Test API Call ===\n";
    echo "Use these credentials to test the API:\n";
    echo "POST /api/buffalo/get-user-balance\n";
    echo "{\n";
    echo "    \"uid\": \"{$auth['uid']}\",\n";
    echo "    \"token\": \"{$auth['token']}\"\n";
    echo "}\n";
    
} else {
    echo "❌ User 'PLAYER0101' not found\n";
    echo "Available users:\n";
    $users = User::where('type', 'player')->limit(5)->get(['user_name', 'id']);
    foreach ($users as $u) {
        echo "- {$u->user_name} (ID: {$u->id})\n";
    }
}