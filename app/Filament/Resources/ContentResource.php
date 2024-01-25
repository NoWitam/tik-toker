<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers\ActionsRelationManager;
use App\Infolists\Components\JsonList;
use App\Infolists\Components\VideoEntry;
use App\Models\Account;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
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
                Tables\Columns\TextColumn::make('name')->label('Nazwa')->wrap()->searchable('contents.name'),
                Tables\Columns\IconColumn::make('status')->icon(fn ($state): string => $state->getIcon())->color(fn ($state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('series.account.name')->label('Konto'),
                Tables\Columns\TextColumn::make('series.name')->label('Seria'),
                Tables\Columns\TextColumn::make('knowledge.name')->label('Na podstawie')->wrap()->searchable('knowledge.name'),
                Tables\Columns\TextColumn::make('actions_sum_cost')->label('Koszt')->sum('actions', 'cost')->suffix('$')->default(0),
                Tables\Columns\TextColumn::make('publication_time')->label('Czas publikacji'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        ContentStatus::IN_PROCESS->value => 'W trakcie tworzenia',
                        ContentStatus::WAITING->value => 'Czeka na interakcje',
                        ContentStatus::CREATED->value => 'Utworzony',
                        ContentStatus::PUBLISHED->value => 'Opublikowany',
                        ContentStatus::ERROR->value => "Błąd"
                    ])->label('Status'),
                Filter::make('publication_time')
                        ->form([
                            Select::make('publication_time')
                                ->options([
                                    'today' => 'Dzisiaj',
                                    'tomorrow' => 'Jutro',
                                    'this-week' => 'Ten tydzień',
                                    'last-week' => 'Ostatni tydzień'
                                ])->label('Utworzono')
                        ])->query(function (Builder $query, array $data): Builder {

                            if($data['publication_time'] == 'today') {
                                return $query->where('publication_time', '>=', Carbon::now()->startOfDay())
                                            ->where('publication_time', '<=', Carbon::now()->endOfDay());
                            }

                            if($data['publication_time'] == 'tomorrow') {
                                return $query->where('publication_time', '>=', Carbon::now()->startOfDay()->addDays(1))
                                            ->where('publication_time', '<=', Carbon::now()->endOfDay()->addDays(1));
                            }

                            if($data['publication_time'] == 'this-week') {
                                return $query->where('publication_time', '>=', Carbon::now()->startOfWeek())
                                            ->where('publication_time', '<=', Carbon::now()->endOfWeek());
                            }

                            if($data['publication_time'] == 'last-week') {
                                return $query->where('publication_time', '>=', Carbon::now()->startOfWeek()->subDays(7))
                                            ->where('publication_time', '<=', Carbon::now()->endOfWeek()->subDays(7));
                            }

                            return $query;
                        })->indicateUsing(function (array $data): ?string {

                            if($data['publication_time'] == 'today') {
                                return "Publikacja dzisiaj";
                            }

                            if($data['publication_time'] == 'tomorrow') {
                                return "Publikacja jutro";
                            }

                            if($data['publication_time'] == 'this-week') {
                                return "Publikacja w tym tygodniu";
                            }

                            if($data['publication_time'] == 'last-week') {
                                return "Publikacja w ostatnim tygodniu";
                            }

                            return null;
                        })
            ])
            ->actions([
                Tables\Actions\Action::make('manage')->label('Zarządzaj')->url(fn ($record) => self::getUrl('edit', ['record' => $record]))
                                ->color('gray')->icon('heroicon-o-command-line'),
                Tables\Actions\RestoreAction::make()
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
                    }),
                    Tab::make('Wideo')->schema([
                        VideoEntry::make('video')
                    ])->visible(function ($record) {
                        return in_array($record->status, [
                            ContentStatus::CREATED,
                            ContentStatus::PUBLISHED
                        ]);
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

    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
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
