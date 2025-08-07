<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackendGames extends Model
{
    protected $table = 'backend_games';

    public function tasks() {
        return $this->hasMany(AutomationResult::class, 'backend_id');
    }

    public function logs() {
        return $this->hasMany(Logs::class, 'backend_id');
    }

    public function backendAccounts() {
        return $this->hasMany(BackendAccounts::class, 'backend_id');
    }

}
