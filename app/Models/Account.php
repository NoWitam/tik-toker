<?php

namespace App\Models;

use Filament\Forms\Components\Actions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public function series()
    {
        return $this->hasMany(Series::class);
    }

    public function publications()
    {
        return $this->hasManyThrough(Publication::class, Series::class);
    }

    public function contents()
    {
        return $this->hasManyThrough(Content::class, Series::class);
    }
}
