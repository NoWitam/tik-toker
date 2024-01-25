<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use App\Jobs\CreateScript;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use App\Models\Enums\ContentStatus;
use App\Services\ContentService;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class EditContent extends EditRecord implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string $resource = ContentResource::class;

    protected static string $view = 'filament.resources.content-resource.pages.manage-content';
    protected static ?string $title = 'Zarządzaj kontentem';
    public ?array $data = [];

    public function infolist(Infolist $infolist): Infolist
    {
        return self::$resource::infolist($infolist);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('manage')->heading('Zarządzaj')->description(fn ($record) => $record->status->getDescription())
                    ->icon(fn ($record) => $record->status->getIcon())->iconColor(fn ($record) => $record->status->getColor())
                    ->schema([
                        TextInput::make('script.title')->label('Tytuł')->required()->hidden(function ($record) {
                            return in_array($record->status, [
                                ContentStatus::IN_PROCESS,
                                ContentStatus::PUBLISHED,
                            ]);
                        }),
                        TagsInput::make('script.hashtags')->label('Hashtagi')->required()->hidden(function ($record) {
                            return in_array($record->status, [
                                ContentStatus::IN_PROCESS,
                                ContentStatus::PUBLISHED,
                            ]);
                        }),
                        $this->createScenesSchema(),
                        Actions::make([
                            Action::make('save')->label('Zapisz')->action(function ($state, $record)  {

                                $record->update([
                                    'script' => $state['script']
                                ]);

                                Notification::make()
                                    ->title('Scenariusz zapisany pomyślnie')
                                    ->success()
                                    ->send();

                            })->hidden(function ($record) {
                                return in_array($record->status, [
                                    ContentStatus::IN_PROCESS,
                                    ContentStatus::PUBLISHED,
                                ]);
                            }),
                            Action::make('generate')->label('Generuj film')->action(function ($record) {
                                ContentService::generateVideo($record);
                                
                                Notification::make()
                                    ->title('Generowanie filmu dodano do kolejki')
                                    ->success()
                                    ->send();
                            })->hidden(function ($record) {
                                return in_array($record->status, [
                                    ContentStatus::IN_PROCESS,
                                    ContentStatus::PUBLISHED,
                                ]);
                            }),
                            Action::make('saveAndGenerate')->label('Zapisz i generuj film')->action(function ($state, $record) {
                                $record->update([
                                    'script' => $state['script']
                                ]);

                                ContentService::generateVideo($record);

                                Notification::make()
                                    ->title('Scenariusz zapisany pomyślnie oraz generowanie dodane do kolejki')
                                    ->success()
                                    ->send();
                            })->hidden(function ($record) {
                                return in_array($record->status, [
                                    ContentStatus::IN_PROCESS,
                                    ContentStatus::PUBLISHED,
                                ]);
                            }),
                            Action::make('recreate')->label('Generuj scenariusz')->action(function ($record) {
                                CreateScript::dispatch($record)->onQueue('user');
                                
                                Notification::make()
                                    ->title('Generowanie scenariusza dodane do kolejki')
                                    ->success()
                                    ->send();
                            })->hidden(function ($record) {
                                return in_array($record->status, [
                                    ContentStatus::IN_PROCESS,
                                    ContentStatus::PUBLISHED,
                                ]);
                            }),
                            Action::make('restore')->label('Przywróć')->action(function ($record) {
                                $record->update([
                                    'script' => $record->archivalScript
                                ]);

                                Notification::make()
                                    ->title('Scenariusz przywrócony pomyślnie')
                                    ->success()
                                    ->send();
                            })->visible(function ($record) {
                                return $record->status == ContentStatus::CREATED;
                            })
                        ])->fullWidth()
                    ])->columns(1)
                ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function makeInfolist(): Infolist
    {
        return parent::makeInfolist()
            ->record($this->getRecord())
            ->columns($this->hasInlineLabels() ? 1 : 2)
            ->inlineLabel($this->hasInlineLabels());
    }

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
        ];
    }

    private function createScenesSchema()
    {
        $scenesSchema = [];

        if(isset($this->getRecord()->script['details'])) {
            foreach($this->getRecord()->script['details'] as $index => $sceneData)
            {
                $scenesSchema[] = Fieldset::make("Scena {$index}")->schema([
                    Textarea::make("script.details.{$index}.image")->label('Tło')->required(),
                    Textarea::make("script.details.{$index}.content")->label('Tekst')->required()->rows(3),
                ])->columns(1)->columnSpan(1);
            }
    
            return Section::make('Sceny')->schema($scenesSchema)->hidden(function ($record) {
                return in_array($record->status, [
                    ContentStatus::IN_PROCESS,
                    ContentStatus::PUBLISHED,
                ]);
            })->columns(2)->collapsed();
        }

        return Section::make('Sceny')->schema([])->hidden(function ($record) {
            return in_array($record->status, [
                ContentStatus::IN_PROCESS,
                ContentStatus::PUBLISHED,
            ]);
        })->columns(2)->collapsed();

    }
}
