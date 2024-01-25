<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Filament\Resources\AccountResource\RelationManagers\ContentsRelationManager;
use App\Filament\Resources\AccountResource\RelationManagers\SeriesRelationManager;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';
    protected static ?string $navigationGroup = 'Twórca';
    protected static ?string $modelLabel = 'Konto';
    protected static ?string $pluralModelLabel = 'Konto';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label('Nazwa'),
                Forms\Components\TextInput::make('access_token')->required()->maxLength(255)->label('Token dostępu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa')->searchable(),
                Tables\Columns\TextColumn::make('publications_count')->label('Filmy per tydzień')->counts('publications'),
                Tables\Columns\TextColumn::make('contents_count')->label('Filmy')->counts('contents'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
            SeriesRelationManager::class,
            ContentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
