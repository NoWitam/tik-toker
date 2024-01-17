<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

enum ContentStatus : int
{
    case IN_PROCESS = 1;
    case WAITING = 2;
    case CREATED = 3;
    case PUBLISHED = 4;
    case ERROR = 5;

    public function getIcon(): string
    {
        return match($this) {
            self::IN_PROCESS => "heroicon-o-clock",
            self::WAITING => "heroicon-o-chat-bubble-left-ellipsis",
            self::CREATED => "heroicon-o-pause-circle",
            self::PUBLISHED => "heroicon-o-play-circle",
            self::ERROR => "heroicon-o-x-circle",
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::IN_PROCESS => "gray",
            self::WAITING => "warning",
            self::CREATED => "success",
            self::PUBLISHED => "success",
            self::ERROR => "danger",
        };
    }
}
