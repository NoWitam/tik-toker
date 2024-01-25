<?php

namespace App\Jobs;

use App\Exceptions\ExceptionWithData;
use App\Jobs\Interfaces\Statusable;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateAudio extends ActionableJob implements Statusable
{
    public $timeout = 300;
    public string $text;
    public $api = "edenai";

    public function __construct(
        public Content $content,
        public int $scene
    ){}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Utwórz audio';
    }

    
    public function run(): array
    {
        $this->text = $this->content->script['details'][$this->scene]['content'];

        $response = $this->getAudio();

        $this->action->increment('cost', $response->cost);

        if($response->status == "success") {
            $className = explode("\\", Content::class);
            $className = array_pop($className);
            
            foreach(range(1, 5) as $index)
            {
                if($index != 5) {
                    try {
                        $file = file_get_contents($response->audio_resource_url);
                        break;
                    } catch (Throwable $e) {
                        sleep(1);
                    }
                } else {
                    $file = file_get_contents($response->audio_resource_url);
                }
            }

            Storage::put("Models/{$className}/{$this->content->id}/scene{$this->scene}.wav", $file);
            
            CreateDiarization::dispatch($this->content, $this->scene)->onQueue($this->queue);

            return [
                "info" => "Audio utworzono pomyślnie",
                "data" => [
                    "numer" => $this->scene,
                    "tekst" => $this->text
                ]
            ];
        } else {
            throw new ExceptionWithData("Status: {$response->status}", $response);
        }
    }

    public function getChangesOnError() : array
    {
        return [
            'status' => ContentStatus::ERROR
        ];
    }

    private function getAudio()
    {
        $response = $this->callApi('POST', 'https://api.edenai.run/v2/audio/text_to_speech', [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . config("app.eden_ai_key"),
                'content-type' => 'application/json',
            ],
            'body' => json_encode([
                "response_as_dict" => true,
                "attributes_as_list" => false,
                "show_original_response" => false,
                "settings" => ["elevenlabs" => "pl-PL_Multilingual_Giovanni"],
                "providers" => "elevenlabs",
                "rate" => 0,
                "pitch" => 0,
                "volume" => 0,
                "sampling_rate" => 0,
                "audio_format" => "mp3",
                "text" => $this->text
            ])
        ]);

        return json_decode($response->getBody()->__toString())->elevenlabs;
    }
}
