<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

enum ActionStatus : int
{
    case WAITING = 1;
    case PROCESSING = 2;
    case SUCCESS = 3;
    case FAILED = 4;

    public function getIcon(): string
    {
        return match($this) {
            self::WAITING => "heroicon-o-clock",
            self::PROCESSING => "heroicon-o-forward",
            self::SUCCESS => "heroicon-o-check-circle",
            self::FAILED => "heroicon-o-x-circle",
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::WAITING => "gray",
            self::PROCESSING => "warning",
            self::SUCCESS => "success",
            self::FAILED => "danger",
        };
    }
}
