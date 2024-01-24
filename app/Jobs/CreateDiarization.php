<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Interfaces\Statusable;
use Illuminate\Support\Facades\Storage;

class CreateDiarization extends ActionableJob
{
    public $timeout = 300;
    public $api = "edenai";

    public function __construct(
        public Content $content,
        public int $scene,
    ){}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Utwórz napisy do audio';
    }

    
    public function run(): array
    {
        $result = $this->loadDiarization();

        SaveDiarization::dispatch($this->content, $this->scene, $result['public_id'])->onQueue($this->queue)->setAction($this->action);

        return [
            "info" => "Napisy dodane do kolejki",
            "data" => [
                "numer" => $this->scene,
                "jobId" => $result['public_id']
            ]
        ];
    }
    private function loadDiarization()
    {
        $className = explode("\\", Content::class);
        $className = array_pop($className);
        $path = str_replace("\\", "/", 
            Storage::path("Models/{$className}/{$this->content->id}/scene{$this->scene}.wav")
        );

        $name = explode("/", $path);
        $name = $name[count($name) -1];

        $fileContents = file_get_contents($path);

        $response = $this->callApi('POST', 'https://api.edenai.run/v2/audio/speech_to_text_async', [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . config("app.eden_ai_key"),
            ],
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $fileContents,
                    'filename' => $name,
                ],
                [
                    'name' => 'show_original_response',
                    'contents' => 'false',
                ],
                [
                    'name' => 'speakers',
                    'contents' => '1',
                ],
                [
                    'name' => 'profanity_filter',
                    'contents' => 'false',
                ],
                [
                    'name' => 'convert_to_wav',
                    'contents' => 'false',
                ],
                [
                    'name' => 'providers',
                    'contents' => 'gladia,openai',
                ],
                [
                    'name' => 'language',
                    'contents' => 'pl',
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
