<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables\Table;

abstract class ResourceRelationManager extends RelationManager
{

    abstract public function getResource() : string;

    abstract public function extendTable(Table $table): Table;

    public function table(Table $table): Table
    {
        return $this->extendTable($this->getResource()::table($table));
    }
}