<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use App\Services\BuffaloGameService;
use App\Models\LogBuffaloBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BuffaloGameController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Buffalo Game - Get User Balance
     * Endpoint: POST /api/buffalo/get-user-balance
     */
    public function getUserBalance(Request $request)
    {
        Log::info('TTT Buffalo getUserBalance - Request received', [
            'request' => $request->all(),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Verify token
        Log::info('TTT Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token' => $token
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('TTT Buffalo - Token verification failed', [
                'uid' => $uid,
                'token' => $token
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('TTT Buffalo - Token verification successful', [
            'uid' => $uid
        ]);

        // Extract username from UID
        $userName = BuffaloGameService::extractUserNameFromUid($uid);

        if (!$userName) {
            Log::warning('TTT Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user by username
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('TTT Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Get balance (assuming you use bavix/laravel-wallet)
        $balance = $user->balanceFloat;

        Log::info('TTT Buffalo - Balance retrieved successfully', [
            'user' => $userName,
            'balance' => $balance
        ]);

        // Return balance as integer (Buffalo provider expects integer only)
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'balance' => (int) $balance,
        ]);
    }

    /**
     * Buffalo Game - Change Balance (Bet/Win)
     * Endpoint: POST /api/buffalo/change-balance
     */
    public function changeBalance(Request $request)
    {
        // Log::info('TTT Buffalo changeBalance - Request received', [
        //     'request' => $request->all(),
        //     'ip' => $request->ip()
        // ]);

        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
            'changemoney' => 'required|integer',
            'bet' => 'required|integer',
            'win' => 'required|integer',
            'gameId' => 'required|integer',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Verify token
        Log::info('TTT Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token' => $token
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('TTT Buffalo - Token verification failed', [
                'uid' => $uid,
                'token' => $token
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('TTT Buffalo - Token verification successful', [
            'uid' => $uid
        ]);

        // Extract username from UID
        $userName = BuffaloGameService::extractUserNameFromUid($uid);

        if (!$userName) {
            Log::warning('TTT Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('GCC Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Get amounts
        $changeAmount = (int) $request->changemoney;
        $betAmount = abs((int) $request->bet);
        $winAmount = (int) $request->win;

        Log::info('GCC Buffalo - Processing transaction', [
            'user_name' => $user->user_name,
            'user_id' => $user->id,
            'change_amount' => $changeAmount,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'game_id' => $request->gameId
        ]);

        try {
            DB::beginTransaction();

            // Handle transaction
            if ($changeAmount > 0) {
                // Win/Deposit transaction
                $success = $this->walletService->deposit(
                    $user,
                    $changeAmount,
                    TransactionName::GameWin,
                    [
                        'buffalo_game_id' => $request->gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_win'
                    ]
                );
            } else {
                // Loss/Withdraw transaction
                $success = $this->walletService->withdraw(
                    $user,
                    abs($changeAmount),
                    TransactionName::GameLoss,
                    [
                        'buffalo_game_id' => $request->gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_loss'
                    ]
                );
            }

            if (!$success) {
                DB::rollBack();
                
                Log::error('GCC Buffalo - Wallet transaction failed', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'change_amount' => $changeAmount
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'Transaction failed',
                ]);
            }

            // Refresh user model
            $user->refresh();

            Log::info('GCC Buffalo - Transaction successful', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'change_amount' => $changeAmount,
                'new_balance' => $user->balanceFloat
            ]);

            // Log the bet
            $this->logBuffaloBet($user, $request->all());

            DB::commit();

            return response()->json([
                'code' => 1,
                'msg' => 'Balance updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('GCC Buffalo - Transaction error', [
                'user_name' => $user->user_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Log Buffalo bet for reporting
     */
    private function logBuffaloBet(User $user, array $requestData): void
    {
        try {
            LogBuffaloBet::create([
                'member_account' => $user->user_name,
                'player_id' => $user->id,
                'player_agent_id' => $user->agent_id,
                'buffalo_game_id' => $requestData['gameId'] ?? null,
                'request_time' => now(),
                'bet_amount' => abs((int) $requestData['bet']),
                'win_amount' => (int) $requestData['win'],
                'payload' => $requestData,
                'game_name' => 'Buffalo Game',
                'status' => 'completed',
                'before_balance' => $user->balanceFloat - ($requestData['changemoney'] ?? 0),
                'balance' => $user->balanceFloat,
            ]);

            Log::info('GCC Buffalo - Bet logged successfully', [
                'user' => $user->user_name,
                'game_id' => $requestData['gameId']
            ]);

        } catch (\Exception $e) {
            Log::error('GCC Buffalo - Failed to log bet', [
                'error' => $e->getMessage(),
                'user' => $user->user_name
            ]);
        }
    }

    /**
     * Generate Buffalo game authentication data for frontend
     */
    public function generateGameAuth(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $auth = BuffaloGameService::generateBuffaloAuth($user);
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        $roomConfig = BuffaloGameService::getRoomConfig();

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'auth' => $auth,
                'available_rooms' => $availableRooms,
                'all_rooms' => $roomConfig,
                'user_balance' => $user->balanceFloat,
            ],
        ]);
    }

    /**
     * Generate Buffalo game URL for direct launch
     */
    public function generateGameUrl(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|min:1|max:4',
            'lobby_url' => 'nullable|url',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $roomId = $request->room_id;
        $lobbyUrl = $request->lobby_url ?: config('app.url');

        // Check if user has sufficient balance for the room
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        
        if (!isset($availableRooms[$roomId])) {
            return response()->json([
                'code' => 0,
                'msg' => 'Insufficient balance for selected room',
            ]);
        }

        $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'game_url' => $gameUrl,
                'room_info' => $availableRooms[$roomId],
            ],
        ]);
    }

    /**
     * Buffalo Game - Launch Game (Frontend Integration)
     * Compatible with existing frontend LaunchGame hook
     */
    public function launchGame(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'game_id' => 'required|integer',
            'room_id' => 'nullable|integer|min:1|max:4', // Optional room selection
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ], 401);
        }

        try {
            // Check if this is a Buffalo game request
            if ($request->provider_id === 23) { // Assuming 23 is Buffalo provider ID
                // Generate Buffalo game authentication
                $auth = BuffaloGameService::generateBuffaloAuth($user);
                
                // Get room configuration
                $roomId = $request->room_id ?? 1; // Default to room 1
                $availableRooms = BuffaloGameService::getAvailableRooms($user);
                
                // Check if requested room is available for user's balance
                if (!isset($availableRooms[$roomId])) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Room not available for your balance level',
                    ]);
                }
                
                $roomConfig = $availableRooms[$roomId];
                
                // Generate Buffalo game URL (Production - HTTP as per provider format)
                $lobbyUrl = 'https://africanbuffalo.vip';
                $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl);
                
                // Add UID and token to the URL (exact provider format)
                $gameUrl .= '&uid=' . $auth['uid'] . '&token=' . $auth['token'];
                
                Log::info('Buffalo Game Launch', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'room_id' => $roomId,
                    'game_url' => $gameUrl,
                    'auth_data' => $auth
                ]);
                
                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl, // Compatible with existing frontend
                    'game_url' => $gameUrl, // HTTP URL (exact provider format)
                    'room_info' => $roomConfig,
                    'user_balance' => $user->balanceFloat,
                ]);
            }
            
            // For non-Buffalo games, you can add other provider logic here
            return response()->json([
                'code' => 0,
                'msg' => 'Game provider not supported',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Buffalo Game Launch Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Failed to launch game',
            ]);
        }
    }
}