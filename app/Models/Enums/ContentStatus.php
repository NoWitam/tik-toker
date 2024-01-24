<?php

namespace App\Models\Enums;

use App\Jobs\CreateScript;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;

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

    public function getDescription(): string
    {
        return match($this) {
            self::IN_PROCESS => "Kontent w trakcie tworzenia. Poczekaj aż proces ten się skończy",
            self::WAITING => "Kontent czeka na interakcje użytkownika. Wypełnij formularz",
            self::CREATED => "Kontent stowrzony. Możesz go jeszcze edytować przed publikacją",
            self::PUBLISHED => "Kontent opublikowany !",
            self::ERROR => "Błąd tworzenia kontentu :(",
        };
    }
}
