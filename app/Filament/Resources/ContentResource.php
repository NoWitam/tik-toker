<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers\ActionsRelationManager;
use App\Infolists\Components\JsonList;
use App\Models\Account;
use App\Models\Content;
use App\Models\Series;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
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
                Tables\Actions\Action::make('manage')->label('Zarządzaj')->url(fn ($record) => self::getUrl('edit', ['record' => $record]))
                                ->color('gray')->icon('heroicon-o-command-line')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('tabs')->tabs([
                    Tab::make("Informacje")->schema([
                        TextEntry::make('name')->label('Nazwa')->size(TextEntrySize::Large)->weight(FontWeight::SemiBold),
                        TextEntry::make('publication_time')->label('Data publikacji')->size(TextEntrySize::Large)->weight(FontWeight::SemiBold),
                        TextEntry::make('series.account.name')->label('Konto')->size(TextEntrySize::Large)->weight(FontWeight::SemiBold),
                        TextEntry::make('series.name')->label('Seria')->size(TextEntrySize::Large)->weight(FontWeight::SemiBold),
                    ])->columns(2),
                    Tab::make('Na podstawie')->schema([
                        TextEntry::make('knowledge.name')->label('Nazwa')->size(TextEntrySize::Large)->weight(FontWeight::SemiBold) ,
                        TextEntry::make('knowledge.tags.name')->label('Tagi')->badge()->separator(','),
                        TextEntry::make('knowledge.content')->label('Treść'),   
                    ]),
                    Tab::make('Scenariusz')->schema([
                        JsonList::make('archivalScript')->label("Skrypt")        
                    ])->columns(1)->hidden(function ($record) {
                        return $record->archivalScript == null;
                    })
                ])
            ])->columns(1);
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
