<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackendGames extends Model
{
    protected $table = 'backend_games';

    protected $fillable = [
        'name',
        'backend_url',
        'username',
        'password',
        'game_url',
        'image_url',
        'status',
        'binding_key',
        'accounts_creation_pd'
    ];

    /**
     * Every backend the automation knows about, for filter dropdowns and
     * request validation. Disabled backends (status 0) are included so their
     * history stays reachable; soft-deleted rows are not.
     */
    public static function options()
    {
        return static::whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'status']);
    }

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
