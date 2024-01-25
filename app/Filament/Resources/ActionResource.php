<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActionResource\Pages;
use App\Models\Action;
use App\Models\Enums\ActionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Stringable;
use App\Services\ActionService;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        ActionStatus::WAITING->value => 'W kolejce',
                        ActionStatus::PROCESSING->value => 'Procedowane',
                        ActionStatus::FAILED->value => 'Nie udane',
                        ActionStatus::SUCCESS->value => 'Udane'
                    ])->label('Status'),
                Filter::make('created_at')
                        ->form([
                            Select::make('created_at')
                                ->options([
                                    'today' => 'Dzisiaj',
                                    'this-week' => 'Ten tydzień',
                                    'last-week' => 'Ostatni tydzień'
                                ])->label('Utworzono')
                        ])->query(function (Builder $query, array $data): Builder {

                            if($data['created_at'] == 'today') {
                                return $query->where('created_at', '>=', Carbon::now()->startOfDay());
                            }

                            if($data['created_at'] == 'this-week') {
                                return $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                            }

                            if($data['created_at'] == 'last-week') {
                                return $query->where('created_at', '>=', Carbon::now()->startOfWeek()->subDays(7))
                                            ->where('created_at', '<=', Carbon::now()->endOfWeek()->subDays(7));
                            }

                            return $query;
                        })->indicateUsing(function (array $data): ?string {

                            if($data['created_at'] == 'today') {
                                return "Utworzone dzisiaj";
                            }

                            if($data['created_at'] == 'this-week') {
                                return "Utworzone w tym tygodniu";
                            }

                            if($data['created_at'] == 'last-week') {
                                return "Utworzone w ostatnim tygodniu";
                            }

                            return null;
                        })
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label('Ponów')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        ActionService::retry($record);
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
            ])
            ->poll('10s');
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
