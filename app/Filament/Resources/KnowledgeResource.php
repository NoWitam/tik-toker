<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeResource\Pages;
use App\Models\Knowledge;
use App\Models\Tag;
use App\Tables\Columns\ListRelation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KnowledgeResource extends Resource
{
    protected static ?string $model = Knowledge::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Baza wiedzy';
    protected static ?string $modelLabel = 'Wiedza';
    protected static ?string $pluralModelLabel = 'Wiedza';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label('Nazwa'),
                Forms\Components\Select::make('tags')->label('Tagi')
                    ->required()
                    ->multiple()
                    ->relationship(name: 'tags', titleAttribute: 'name')
                    ->preload()
                    ->searchable(),
                    Forms\Components\TextInput::make('unique')->required()->maxLength(255)->label('Identyfikator'),
                Forms\Components\Textarea::make('content')->rows(20)->required()->label('Treść')->columnSpanFull(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa'),
                Tables\Columns\TagsColumn::make('tags.name')->label('Tagi')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListKnowledge::route('/'),
            'create' => Pages\CreateKnowledge::route('/create'),
            'edit' => Pages\EditKnowledge::route('/{record}/edit'),
        ];
    }
}
