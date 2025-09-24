<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackendAccounts extends Model
{
    protected $table = 'backend_accounts';

    protected $fillable = [
        'game_id',
        'user_id',
        'backend_id',
        'username',
        'password',
        'is_assigned'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function backendGame() {
        return $this->belongsTo(BackendGames::class, 'backend_id');
    }

}
