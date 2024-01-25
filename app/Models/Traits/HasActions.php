<?php

namespace App\Models\Traits;

use App\Models\Action;
use App\Models\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait HasActions
{
    public function actions() : MorphMany
    {
        return $this->morphMany(Action::class, 'actionable');
    }

    public static function bootHasActions()
    {
        static::deleting(function ($record) {

            $now = Carbon::now()->format("Y-m-d H:i:s");

            foreach($record->actions()->where('status', ActionStatus::WAITING)->get() as $action)
            {
                DB::table('jobs')->where('payload', 'like', '%"uuid":"' . $action->job_uuid . '"%')->delete();

                $action->update([
                    'status' => ActionStatus::FAILED,
                    'info' => "Zasób usunięto o {$now}"
                ]);
            }
        });
    }
}
