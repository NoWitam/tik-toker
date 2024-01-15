<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

enum ActionStatus : int
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    case SUNDAY = 7;
}
