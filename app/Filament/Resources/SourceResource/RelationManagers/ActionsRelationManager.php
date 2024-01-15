<?php

namespace App\Filament\RelationManagers;

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

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';
    protected static ?string $title = 'Akcje';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('info')->label('Informacje')->wrap(),
                Tables\Columns\IconColumn::make('status')->icon(fn ($state): string => $state->getIcon())->color(fn ($state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('type')->label('Typ'),
                Tables\Columns\TextColumn::make('attempts')->label('Próby'),
                Tables\Columns\TextColumn::make('created_at')->label('Utworzono'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('test')
                    ->label('Testuj')->icon('heroicon-o-play-pause')
                    ->requiresConfirmation()
                    ->action( function () {
                        ParseSourceTest::dispatch($this->getOwnerRecord())->onQueue('user');
                    }),
                Tables\Actions\Action::make('getKnowledge')
                    ->label('Wydobyj wiedze')->icon('heroicon-o-document-magnifying-glass')
                    ->requiresConfirmation()
                    ->action( function () {
                        ParseSource::dispatch($this->getOwnerRecord())->onQueue('user');
                    })
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->hidden(function ($record) {
                        return $record->status != ActionStatus::WAITING;
                    }),
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        $record->refresh();
                        if($record->status == ActionStatus::FAILED) {
                            Artisan::call('queue:retry', ['id' => [$record->job_uuid]]);
                            $record->status = ActionStatus::WAITING;
                            $record->save();    
                        }

                    })->hidden(function ($record) {
                        return $record->status != ActionStatus::FAILED;
                    }),
                Tables\Actions\ViewAction::make()->hidden(function ($record) {
                    return $record->status != ActionStatus::FAILED AND $record->status != ActionStatus::SUCCESS;
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
