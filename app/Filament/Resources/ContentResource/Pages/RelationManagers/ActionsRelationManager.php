<?php

namespace App\Filament\Resources\ContentResource\RelationManagers;

use App\Filament\RelationManagers\ResourceRelationManager;
use App\Infolists\Components\JsonList;
use App\Jobs\ParseSource;
use App\Models\Enums\ActionStatus;
use Filament\Infolists;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Jobs\ParseSourceTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Filament\Infolists\Infolist;
use Novadaemon\FilamentPrettyJson\PrettyJson;
use App\Filament\Resources\ActionResource;

class ActionsRelationManager extends ResourceRelationManager
{
    protected static string $relationship = 'actions';
    protected static ?string $title = 'Akcje';

    public function getResource() : string
    {
        return ActionResource::class;
    }

    public function extendTable(Table $table): Table
    {
        return $table
            ->pushActions([
                Tables\Actions\ViewAction::make('view')
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('info')->label('Informacja'),
                JsonList::make('data')
            ])->columns(1);
    }
}
