<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\RelationManagers\ResourceRelationManager;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use App\Filament\Resources\ContentResource;

class ContentsRelationManager extends ResourceRelationManager
{
    protected static string $relationship = 'contents';
    protected static ?string $title = 'Kontent';

    public function getResource() : string
    {
        return ContentResource::class;
    }

    public function extendTable(Table $table): Table
    {
        return $table;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist;
    }
}
