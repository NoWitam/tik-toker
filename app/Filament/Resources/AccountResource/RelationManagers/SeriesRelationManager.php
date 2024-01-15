<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Enums\DayOfWeek;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Tables\Columns\TextColumn::make('name')->label('Nazwa'),
                Tables\Columns\TagsColumn::make('tags.name')->label('Tagi')
            ])
            ->filters([
                //
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
