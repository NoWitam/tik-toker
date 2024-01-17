<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTags;
use App\Models\Interfaces\Taggable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Series extends Model implements Taggable
{
    use HasFactory, HasTags;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function actions()
    {
        return $this->hasManyThrough(Action::class, Content::class, 'series_id', 'actionable_id')
                ->where('actionable_type', Content::class);
    }

}
