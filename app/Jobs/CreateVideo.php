<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\Interfaces\Actionable;
use App\Services\ContentService;
use App\Services\Stories;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CreateVideo extends ActionableJob
{

    public $timeout = 300;

    /**
     * Create a new job instance.
     */
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
        return 'Utwórz scene';
    }


    public function run() : array
    {
        $file = $this->content->script;

        $tmpPath = $this->getFolderName() . "/tmp";
        if(!Storage::exists($tmpPath)) {
            Storage::makeDirectory($tmpPath); //creates directory
        }

        $path = Storage::path(str_replace("\\", "/", $this->getFolderName()));
   
        $stories = new Stories([
            "width" => 720,
            "height" => 1280,
            "duration" => $this->getAudioTime($path . "/scene{$this->scene}.wav")
        ]);

        $stories->addImage([
            "path" => $path . "/scene{$this->scene}.png",
            "scale" => 0.7,
            "top" => 282
        ]);

        $stories->addText([
            "text" => $file['title'],
            "path" => Storage::path("fonts/Podkova-Bold.ttf"),
            "size" => 42,
            "left" => 42,
            "width" => 636,
            "align" => "center",
            "top" => 97,
            "color" => "#ffffff",
        ]);

        $stories->addMusic($path . "/scene{$this->scene}.wav");

        $diarization = Storage::get($this->getFolderName() . "/scene{$this->scene}.json");
        $diarization = json_decode($diarization);

        foreach($diarization as $entry)
        {
            $stories->addText([
                "text" => $entry->text,
                "path" => Storage::path("fonts/Podkova-Regular.ttf"),
                "size" => 64,
                "left" => 42,
                "width" => 636,
                "align" => "center",
                "top" => 600 - floor(strlen($entry->text)/15) * 32,
                "color" => "#ffc83e",
                "start" => $entry->start,
                "end" => $entry->end,
                "shadow" => [
                    "color" => "#000000",
                    "left" => 4,
                    "top" => 4
                ]
            ]);
        }

        $hash = $stories->generate(str_replace("\\", "/", Storage::path($tmpPath)));

        Storage::move($tmpPath. "/" . $hash, $this->getFolderName() . "/scene{$this->scene}.mp4");
        Storage::delete(Storage::allFiles($tmpPath));

        ContentService::manageProcess($this->content, $this->scene, "video", $this->queue);

        return [
            "info" => "Scene utworzono pomyślnie",
            "data" => [
                "numer" => $this->scene,
                "długość" => $this->getAudioTime($path . "/scene{$this->scene}.wav")
            ]
        ];
    }

    private function getFolderName()
    {
        $className = explode("\\", Content::class);
        $className = array_pop($className);
        return "Models/{$className}/{$this->content->id}";
    }

    private function getAudioTime($path)
    {
        $time = exec("ffmpeg -i " . escapeshellarg($path) . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
        list($hms, $milli) = explode('.', $time);
        list($hours, $minutes, $seconds) = explode(':', $hms);
        return ($hours * 3600) + ($minutes * 60) + $seconds + 1;
    }
}
