<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class BuffaloGameService
{
    /**
     * Site configuration for Golden City Casino
     */
    private const SITE_NAME = 'goldencitycasino123.site';
    private const SITE_PREFIX = 'gcc';
    private const SITE_URL = 'https://ag.goldencitycasino123.site';

    /**
     * Generate UID (32 characters) for Buffalo API
     * Format: prefix(3) + base64_encoded_username(variable) + padding to 32 chars
     */
    public static function generateUid(string $userName): string
    {
        // Encode username to base64 (URL-safe)
        $encoded = rtrim(strtr(base64_encode($userName), '+/', '-_'), '=');
        
        // Create a 32-character UID: prefix + encoded username + hash padding
        $prefix = self::SITE_PREFIX; // 3 chars: "gcc"
        $remaining = 32 - strlen($prefix);
        
        // If encoded username is longer than available space, use hash instead
        if (strlen($encoded) > $remaining - 10) {
            $hash = md5($userName . self::SITE_URL);
            return $prefix . substr($hash, 0, $remaining);
        }
        
        // Pad with hash to reach 32 characters total
        $padding = substr(md5($userName . self::SITE_URL), 0, $remaining - strlen($encoded));
        return $prefix . $encoded . $padding;
    }

    /**
     * Generate token (64 characters) for Buffalo API
     * Note: Buffalo provider doesn't use secret keys
     */
    public static function generateToken(string $uid): string
    {
        // Generate a 64-character token using SHA256
        return hash('sha256', $uid . self::SITE_URL . time());
    }

    /**
     * Generate persistent token for user (stored in database)
     */
    public static function generatePersistentToken(string $userName): string
    {
        // Generate persistent token using SHA256
        $uniqueString = $userName . self::SITE_URL . 'buffalo-persistent-token';
        return hash('sha256', $uniqueString);
    }

    /**
     * Verify token
     */
    public static function verifyToken(string $uid, string $token): bool
    {
        try {
            // Extract username from UID
            $userName = self::extractUserNameFromUid($uid);
            
            if (!$userName) {
                Log::warning('GCC Buffalo - Could not extract username from UID', [
                    'uid' => $uid
                ]);
                return false;
            }

            // Find user
            $user = User::where('user_name', $userName)->first();
            
            if (!$user) {
                Log::warning('GCC Buffalo - User not found for token verification', [
                    'userName' => $userName
                ]);
                return false;
            }

            // Generate expected token
            $expectedToken = self::generatePersistentToken($userName);

            $isValid = hash_equals($expectedToken, $token);

            if ($isValid) {
                Log::info('GCC Buffalo - Token verified successfully', [
                    'user' => $userName
                ]);
            } else {
                Log::warning('GCC Buffalo - Token verification failed', [
                    'user' => $userName,
                    'expected' => substr($expectedToken, 0, 10) . '...',
                    'received' => substr($token, 0, 10) . '...'
                ]);
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('GCC Buffalo - Token verification error', [
                'error' => $e->getMessage(),
                'uid' => $uid
            ]);
            return false;
        }
    }

    /**
     * Extract username from UID
     */
    public static function extractUserNameFromUid(string $uid): ?string
    {
        // Remove prefix (first 3 characters: "gcc")
        $uidWithoutPrefix = substr($uid, 3);
        
        // Try to decode the base64 encoded part
        try {
            // Find the encoded username part (before the hash padding)
            for ($len = strlen($uidWithoutPrefix); $len >= 4; $len--) {
                $encodedPart = substr($uidWithoutPrefix, 0, $len);
                
                // Add back padding if needed
                $paddedEncoded = $encodedPart . str_repeat('=', (4 - strlen($encodedPart) % 4) % 4);
                
                // Try to decode
                $decoded = base64_decode(strtr($paddedEncoded, '-_', '+/'), true);
                
                if ($decoded !== false) {
                    // Clean the decoded string - remove any non-printable characters
                    $cleaned = preg_replace('/[^\x20-\x7E]/', '', $decoded);
                    
                    if (!empty($cleaned)) {
                        // Check if this username exists (use cleaned string)
                        $user = User::where('user_name', $cleaned)->first();
                        if ($user) {
                            return $cleaned;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('GCC Buffalo - Failed to decode UID', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: Search by UID pattern in database
        try {
            $users = User::select('id', 'user_name')->get();
            foreach ($users as $user) {
                $generatedUid = self::generateUid($user->user_name);
                if ($generatedUid === $uid) {
                    return $user->user_name;
                }
            }
        } catch (\Exception $e) {
            Log::error('GCC Buffalo - Error in fallback UID search', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get game URL for user
     */
    public static function getGameUrl(User $user, int $roomId = 2): string
    {
        $uid = self::generateUid($user->user_name);
        $token = self::generatePersistentToken($user->user_name);

        $data = [
            "gameId" => 23, // Buffalo game ID
            "roomId" => $roomId,
            "uid" => $uid,
            "token" => $token,
            "lobbyUrl" => self::SITE_URL,
        ];

        $baseUrl = 'http://prime7.wlkfkskakdf.com/';
        return $baseUrl . '?' . http_build_query($data);
    }

    /**
     * Generate Buffalo authentication data
     * Returns UID and Token for frontend
     */
    // public static function generateBuffaloAuth(User $user): array
    // {
    //     $uid = self::generateUid($user->user_name);
    //     $token = self::generatePersistentToken($user->user_name);

    //     return [
    //         'uid' => $uid,
    //         'token' => $token,
    //         'user_name' => $user->user_name,
    //     ];
    // }

    public static function generateBuffaloAuth(User $user): array
    {
        $uid = self::generateUid($user->user_name);
        $token = self::generatePersistentToken($user->user_name); // Pass username string, not User object

        return [
            'uid' => $uid,
            'token' => $token,
            'user_name' => $user->user_name,
        ];
    }

    /**
     * Generate Buffalo game URL with lobby URL
     */
    // public static function generateGameUrl(User $user, int $roomId, string $lobbyUrl): string
    // {
    //     $uid = self::generateUid($user->user_name);
    //     $token = self::generatePersistentToken($user->user_name);

    //     $data = [
    //         "gameId" => 23, // Buffalo game ID
    //         "roomId" => $roomId,
    //         "uid" => $uid,
    //         "token" => $token,
    //         "lobbyUrl" => $lobbyUrl,
    //     ];

    //     $baseUrl = 'http://prime7.wlkfkskakdf.com/';
    //     return $baseUrl . '?' . http_build_query($data);
    // }

    public static function generateGameUrl(User $user, int $roomId = 1, string $lobbyUrl = ''): string
    {
        // Use HTTP exactly as provider examples show
        $baseUrl = 'http://prime7.wlkfkskakdf.com/';
        $gameId = 23; // Buffalo game ID from provider examples
        
        // Use provided lobby URL or default to production site
        $finalLobbyUrl = $lobbyUrl ?: 'https://africanbuffalo.vip';
        
        // Generate the base URL without auth (auth will be added by controller)
        $gameUrl = $baseUrl . '?gameId=' . $gameId . 
                   '&roomId=' . $roomId . 
                   '&lobbyUrl=' . urlencode($finalLobbyUrl);
        
        return $gameUrl;
    }

    /**
     * Get available rooms based on user balance
     */
    // public static function getAvailableRooms(User $user): array
    // {
    //     $balance = $user->balanceFloat;
    //     $allRooms = self::getRoomConfig();
    //     $availableRooms = [];

    //     foreach ($allRooms as $roomId => $roomConfig) {
    //         if ($balance >= $roomConfig['min_balance']) {
    //             $availableRooms[$roomId] = $roomConfig;
    //         }
    //     }

    //     return $availableRooms;
    // }

    public static function getRoomConfig(): array
    {
        return [
            1 => ['min_bet' => 50, 'name' => '50 အခန်း', 'level' => 'Low'],
            2 => ['min_bet' => 500, 'name' => '500 အခန်း', 'level' => 'Medium'],
            3 => ['min_bet' => 5000, 'name' => '5000 အခန်း', 'level' => 'High'],
            4 => ['min_bet' => 10000, 'name' => '10000 အခန်း', 'level' => 'VIP'],
        ];
    }

    /**
     * Get available rooms for user based on balance
     */
    public static function getAvailableRooms(User $user): array
    {
        $userBalance = $user->balanceFloat; // Use bavix wallet trait
        $rooms = self::getRoomConfig();
        $availableRooms = [];

        foreach ($rooms as $roomId => $config) {
            if ($userBalance >= $config['min_bet']) {
                $availableRooms[$roomId] = $config;
            }
        }

        return $availableRooms;
    }

    /**
     * Get room configuration
     */
    // public static function getRoomConfig(): array
    // {
    //     return [
    //         1 => [
    //             'id' => 1,
    //             'name' => 'Bronze Room',
    //             'min_balance' => 1000,
    //             'min_bet' => 100,
    //             'max_bet' => 5000,
    //             'description' => 'Entry level room',
    //         ],
    //         2 => [
    //             'id' => 2,
    //             'name' => 'Silver Room',
    //             'min_balance' => 5000,
    //             'min_bet' => 500,
    //             'max_bet' => 10000,
    //             'description' => 'Mid-tier room',
    //         ],
    //         3 => [
    //             'id' => 3,
    //             'name' => 'Gold Room',
    //             'min_balance' => 10000,
    //             'min_bet' => 1000,
    //             'max_bet' => 50000,
    //             'description' => 'High stakes room',
    //         ],
    //         4 => [
    //             'id' => 4,
    //             'name' => 'Diamond Room',
    //             'min_balance' => 50000,
    //             'min_bet' => 5000,
    //             'max_bet' => 100000,
    //             'description' => 'VIP room',
    //         ],
    //     ];
    // }
}