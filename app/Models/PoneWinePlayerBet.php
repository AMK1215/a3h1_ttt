<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoneWinePlayerBet extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'win_lose_amount', 'pone_wine_bet_id'];

    public function poneWineBet()
    {
        return $this->belongsTo(PoneWineBet::class);
    }

    public function poneWineBetInfos()
    {
        return $this->hasMany(PoneWineBetInfo::class, 'pone_wine_player_bet_id', 'id');
    }
}
