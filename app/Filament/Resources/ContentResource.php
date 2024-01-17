<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers\ActionsRelationManager;
use App\Models\Account;
use App\Models\Content;
use App\Models\Series;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?string $navigationGroup = 'Twórca';
    protected static ?string $modelLabel = 'Kontent';
    protected static ?string $pluralModelLabel = 'Kontent';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label('Nazwa')->columnSpanFull(),

                Forms\Components\Select::make('series.account_id')->label('Konto')
                    ->required()
                    ->options(Account::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated( fn (callable $set) => $set('series_id', null)),
                Forms\Components\Select::make('series_id')->label('Seria')
                    ->required()
                    ->options( function (callable $get) {
                        if($get('series.account_id') != null) { 
                            return Series::where('account_id', $get('series.account_id'))->pluck('name', 'id');
                        } else {
                          return [];
                        }
                    })->reactive()->searchable(),


                Forms\Components\Select::make('knowledge_id')->label('Na podstawie')
                    ->required()
                    ->relationship(name: 'knowledge', titleAttribute: 'name')
                    ->preload()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('publication_time')->label('Data publikacji')->required()
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa')->wrap(),
                Tables\Columns\IconColumn::make('status')->icon(fn ($state): string => $state->getIcon())->color(fn ($state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('series.account.name')->label('Konto'),
                Tables\Columns\TextColumn::make('series.name')->label('Seria'),
                Tables\Columns\TextColumn::make('knowledge.name')->label('Na podstawie')->wrap(),
                Tables\Columns\TextColumn::make('actions_sum_cost')->label('Koszt')->sum('actions', 'cost')->suffix('$')->default(0),
                Tables\Columns\TextColumn::make('publication_time')->label('Czas publikacji'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),
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
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
