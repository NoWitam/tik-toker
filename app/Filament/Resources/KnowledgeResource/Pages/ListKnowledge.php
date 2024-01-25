<?php

namespace App\Filament\Resources\KnowledgeResource\Pages;

use App\Filament\Resources\KnowledgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListKnowledge extends ListRecords
{
    protected static string $resource = KnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
