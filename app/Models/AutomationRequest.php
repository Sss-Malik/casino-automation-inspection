<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRequest extends Model
{
    use HasFactory;

    protected $table = 'automation_requests';

    protected $fillable = [
        'task_id',
        'type',
        'payload',
        'status_code',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Each AutomationRequest belongs to one AutomationResult.
     */
    public function result()
    {
        return $this->belongsTo(AutomationResult::class, 'task_id', 'task_id');
    }
}
