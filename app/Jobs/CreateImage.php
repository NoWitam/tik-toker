<?php

namespace App\Jobs;

use App\Exceptions\ExceptionWithData;
use App\Jobs\Interfaces\Statusable;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use App\Services\ContentService;
use Illuminate\Support\Facades\Storage;

class CreateImage extends ActionableJob implements Statusable
{
    public $timeout = 300;
    public string $image;
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
        return 'Utwórz zdjęcie';
    }

    
    public function run(): array
    {
        $triesBeforeFail = 15;
        $this->image = $this->content->script['details'][$this->scene]['image'];

        $file = $this->getImage();

        if($file->status != "success"){
            if($this->action->attempts >= $triesBeforeFail * ($this->action->retries + 1)) {
                throw new ExceptionWithData("Przekroczo limit prób. Ponów akcje", [
                    "opis obrazka" => $this->image,
                    "error" => $file->error->message
                ]);
            }

            CreateImage::dispatch($this->content, $this->scene)->onQueue($this->queue)->setAction($this->action);
            $this->delete();
            return [];
        }

        $this->action->increment('cost', $file->cost);
        
        $className = explode("\\", Content::class);
        $className = array_pop($className);
        Storage::put("Models/{$className}/{$this->content->id}/scene{$this->scene}.png", file_get_contents(
            $file->items[0]->image_resource_url
        ));

        ContentService::manageProcess($this->content, $this->scene, "image", $this->queue);

        return [
            "info" => "Obrazek utworzono pomyślnie",
            "data" => [
                "numer" => $this->scene,
                "opis obrazka" => $this->image
            ]
        ];
    }

    public function getChangesOnError() : array
    {
        return [
            'status' => ContentStatus::ERROR
        ];
    }

    private function getImage()
    {
       $response = $this->callApi('POST', 'https://api.edenai.run/v2/image/generation', [
            'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . config("app.eden_ai_key"),
            'content-type' => 'application/json',
            ],
            'body' => json_encode([
                "response_as_dict" => true,
                "attributes_as_list" => false,
                "show_original_response" => false,
                "settings" => ["openai" => "dall-e-3"],
                "resolution" => "1024x1024",
                "num_images" => 1,
                "providers" => "openai",
                "text" => $this->image
            ])
        ]);

        $data = json_decode($response->getBody()->__toString())->openai;

        return $data;
    }
}
