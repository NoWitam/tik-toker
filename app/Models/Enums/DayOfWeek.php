<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Filament\Support\Contracts\HasLabel;

enum DayOfWeek : int implements HasLabel
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    case SUNDAY = 7;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MONDAY => 'Poniedziałek',
            self::TUESDAY => 'Wtorek',
            self::WEDNESDAY => 'Środa',
            self::THURSDAY => 'Czwartek',
            self::FRIDAY => 'Piątek',
            self::SATURDAY => 'Sobota',
            self::SUNDAY => 'Niedziela'
        };
    }
}
