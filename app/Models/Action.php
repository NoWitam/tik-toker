<?php

namespace App\Models;

use App\Models\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => ActionStatus::class,
    ];

}
