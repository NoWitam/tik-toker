<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages;
use App\Filament\Resources\SourceResource\RelationManagers;
use App\Jobs\ParseSource;
use App\Models\Source;
use App\Services\SourceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;
use App\Tables\Columns\ListRelation;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Knowledge Base';

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
                    ]),
                    Forms\Components\Tabs\Tab::make('Kod')->icon('heroicon-s-code')->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('test')->icon('heroicon-o-play-pause')->requiresConfirmation()
                                ->action( function (Source $source) {
                                    ParseSource::dispatch($source, 'test');
                                })
                        ])->fullWidth(),
                        Forms\Components\Toggle::make('isActive')->label('Wyłącz/Włącz'),
                        CodeEditor::make('eval_next')->required()->label('Kod - url następnej strony'),
                        CodeEditor::make('eval_knowledge_url')->required()->label('Kod - url wiedzy'),
                        CodeEditor::make('eval_knowledge_name')->required()->label('Kod - nazwa wiedzy'),
                        CodeEditor::make('eval_knowledge_content')->required()->label('Kod - treść wiedzy'),
                    ])->columns(2),
                ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nazwa'),
                ListRelation::make('tags')->label('Tagi')
            ])
            ->filters([
                //
            ])
            ->actions([
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
            //
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
