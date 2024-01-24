<?php

namespace App\Models\Traits;

use App\Models\Action;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActions
{
    public function actions() : MorphMany
    {
        return $this->morphMany(Action::class, 'actionable');
    }

    protected static function bootHasActions()
    {
        static::deleting(function ($model) {
            $model->actions()->delete();
        });
    }
}
