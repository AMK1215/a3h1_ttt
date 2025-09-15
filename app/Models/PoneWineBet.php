<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoneWineBet extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'match_id', 'win_number', 'status'];

    public function players()
    {
        return $this->hasMany(PoneWinePlayerBet::class, 'pone_wine_bet_id', 'id');
    }

    /**
     * Store game match data from JSON structure
     */
    public static function storeGameMatchData($data)
    {
        // Create the main game match record
        $gameMatch = self::create([
            'room_id' => $data['roomId'],
            'match_id' => $data['matchId'],
            'win_number' => $data['winNumber'],
            'status' => 1
        ]);

        // Store players and their bets
        foreach ($data['players'] as $playerData) {
            $player = $gameMatch->players()->create([
                'player_id' => $playerData['playerId'],
                'win_lose_amount' => $playerData['winLoseAmount']
            ]);

            // Store bet information for each player
            foreach ($playerData['betInfos'] as $betInfo) {
                $player->poneWineBetInfos()->create([
                    'bet_number' => $betInfo['betNumber'],
                    'bet_amount' => $betInfo['betAmount']
                ]);
            }
        }

        return $gameMatch;
    }
}
