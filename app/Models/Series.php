<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTags;
use App\Models\Interfaces\Taggable;

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
}
