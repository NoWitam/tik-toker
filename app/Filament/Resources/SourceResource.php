<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages;
use App\Filament\Resources\SourceResource\RelationManagers\ActionsRelationManager;
use App\Models\Source;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Baza wiedzy';
    protected static ?string $modelLabel = 'Źródło';
    protected static ?string $pluralModelLabel = 'Źródło';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')->tabs([
                    Forms\Components\Tabs\Tab::make('Informacje')->icon('heroicon-o-chat-bubble-left-ellipsis')->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255)->label('Nazwa'),
                        Forms\Components\TextInput::make('url')->required()->maxLength(255)->label('Link'),
                        Forms\Components\Select::make('tags')->label('Tagi')
                            ->required()
                            ->multiple()
                            ->relationship(name: 'tags', titleAttribute: 'name')
                            ->preload()
                            ->searchable(),
                        Forms\Components\Toggle::make('isActive')->label('Wyłącz/Włącz'),
                    ]),
                    Forms\Components\Tabs\Tab::make('Kod')->icon('heroicon-s-code')->schema([
                        CodeEditor::make('eval_next')->required()->label('Url następnej strony')->hint('Url zapisać do zmiennej $next'),
                        CodeEditor::make('eval_knowledge_url')->required()->label('Url wiedzy')->hint('Urle zapisać do zmiennej $urls'),
                        CodeEditor::make('eval_knowledge_name')->required()->label('Nazwa wiedzy')->hint('Nazwe zapisać do zmiennej $name'),
                        CodeEditor::make('eval_knowledge_content')->required()->label('Treść wiedzy')->hint('Treść zapisać do zmiennej $content'),
                    ])->columns(1),
                ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa'),
                Tables\Columns\TagsColumn::make('tags.name')->label('Tagi'),
                Tables\Columns\ToggleColumn::make('isActive')->label('Aktywność'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->beforeReplicaSaved(function (Model $replica): void {
                        $replica->isActive = false;
                    })
                    ->successRedirectUrl(fn (Model $replica): string => route('filament.admin.resources.sources.edit', [
                        'record' => $replica,
                    ])),
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
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSources::route('/'),
            'create' => Pages\CreateSource::route('/create'),
            'edit' => Pages\EditSource::route('/{record}/edit'),
        ];
    }
}
