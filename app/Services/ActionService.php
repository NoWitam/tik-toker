<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Enums\ActionStatus;
use Illuminate\Support\Facades\Artisan;

class ActionService
{

    static function retry(Action $action)
    {
        $action->refresh();
        if($action->status == ActionStatus::FAILED) {
            $action->status = ActionStatus::WAITING;
            $action->retries++;
            $action->save();  
            Artisan::call('queue:retry', ['id' => [$action->job_uuid]]);  
        }
    }

}
