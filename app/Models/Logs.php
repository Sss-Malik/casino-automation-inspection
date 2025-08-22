<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';


    public function backend() {
        return $this->belongsTo(BackendGames::class, 'backend_id');
    }

    public function automationResult() {
        return $this->belongsTo(AutomationResult::class, 'task_id', 'task_id');
    }

}
