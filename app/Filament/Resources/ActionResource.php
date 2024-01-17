<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActionResource\Pages;
use App\Models\Action;
use App\Models\Enums\ActionStatus;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Stringable;
use Filament\Infolists;
use App\Infolists\Components\JsonList;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static ?string $navigationIcon = 'heroicon-m-cog';
    protected static ?string $navigationGroup = 'Zarządzanie';
    protected static ?string $modelLabel = 'Akcje';
    protected static ?string $pluralModelLabel = 'Akcje';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function canCreate(): bool
    {
       return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('info')->label('Informacje')->wrap(),
                Tables\Columns\IconColumn::make('status')->icon(fn ($state): string => $state->getIcon())->color(fn ($state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('type')->label('Typ'),
                Tables\Columns\TextColumn::make('attempts')->label('Próby'),
                Tables\Columns\TextColumn::make('cost')->label('Koszt')->suffix('$'),
                Tables\Columns\TextColumn::make('created_at')->label('Utworzono'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->hidden(function ($record) {
                        return $record->status != ActionStatus::WAITING;
                    }),
                Tables\Actions\Action::make('retry')
                    ->label('Ponów')
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
                Tables\Actions\Action::make('showParent')
                    ->label('Sprawdź')
                    ->icon('heroicon-o-eye')
                    ->action(function ($record) {

                        $className = str($record->actionable_type)
                            ->whenContains(
                                '\\Resources\\',
                                fn (Stringable $slug): Stringable => $slug->afterLast('\\Resources\\'),
                                fn (Stringable $slug): Stringable => $slug->classBasename(),
                            )
                            ->beforeLast('Resource')
                            ->plural()
                            ->explode('\\')
                            ->map(fn (string $string) => str($string)->kebab()->slug())
                            ->implode('/');

                        return redirect()->route("filament.admin.resources.{$className}.edit", [
                            'record' => $record->actionable
                        ]);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActions::route('/'),
        ];
    }
}
