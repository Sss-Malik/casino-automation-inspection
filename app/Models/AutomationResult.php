<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationResult extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'task_id',
        'status',
        'data',
        'backend_id',
        'order_id',
        'screenshot_url',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $table = 'automation_results';

    public function backend() {
        return $this->belongsTo(BackendGames::class, 'backend_id');
    }

    public function request()
    {
        return $this->hasOne(AutomationRequest::class, 'task_id', 'task_id');
    }
}
