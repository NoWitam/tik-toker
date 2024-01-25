<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Enums\DayOfWeek;
use App\Services\SeriesService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeriesRelationManager extends RelationManager
{
    protected static string $relationship = 'series';
    
    protected static ?string $title = 'Serie';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tags')->label('Tagi')
                    ->required()
                    ->multiple()
                    ->relationship(name: 'tags', titleAttribute: 'name')
                    ->preload()
                    ->searchable(),
                Forms\Components\Repeater::make('publications')->relationship()->schema([
                    Forms\Components\Select::make('day')->options(DayOfWeek::class)->label('Dzień tygodnia')->required(),
                    Forms\Components\TimePicker::make('time')->label('Godzina')->required()
                ])->columns(2)->columnSpanFull()->label('Publikacje'),
                Forms\Components\Textarea::make('instruction')
                    ->required()
                    ->label('Instrukcja')
                    ->maxLength(10200)
                    ->rows(20)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa')->searchable()->sortable(),
                Tables\Columns\TagsColumn::make('tags.name')->label('Tagi'),
                Tables\Columns\TextColumn::make('contents_count')->label('Filmy')->counts('contents')->sortable(),
                Tables\Columns\TextColumn::make('progress')->label('Progres')->getStateUsing(function(Model $record) {
                    $percent = $record->contents_count / SeriesService::getAvailableKnowledgeBuilder($record)->count() * 100;
                    return round($percent, 2) . "%";
                }),
                Tables\Columns\TextColumn::make('actions_sum_cost')->label('Koszt')->sum('actions', 'cost')->suffix('$')->default(0),
                Tables\Columns\TextColumn::make('publications_count')->label('Filmy per tydzień')->counts('publications')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('isActive')->nullable()->label('Aktywność')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Aktywne')
                    ->falseLabel('Nie aktywne')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('publications'),
                        false: fn (Builder $query) => $query->doesntHave('publications'),
                        blank: fn (Builder $query) => $query
                    )
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
