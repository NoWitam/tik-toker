<?php

namespace App\Jobs;

use App\Jobs\Interfaces\Statusable;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ContactVideos extends ActionableJob implements Statusable
{
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content,
    ){}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Scal sceny';
    }


    public function run() : array
    {
        $file = $this->content->script;

        $path = str_replace("\\", "/", Storage::path($this->getFolderName()));

        $txt = "";
        foreach($file['details'] as $i => $detail)
        {
            $txt .= "file scene" . $i . ".mp4 \n";
        }

        Storage::put($this->getFolderName() . "/video.txt", $txt);
        Storage::delete($this->getFolderName() . "/video.mp4");

        exec("ffmpeg -f concat -i {$path}/video.txt -c copy {$path}/video.mp4");

        $this->content->update([
            'status' => ContentStatus::CREATED
        ]);

        Cache::forget("content_{$this->content->id}_completed_process");
        
        $this->content->archivalScript = $this->content->script;

        return [
            "info" => "Sceny scalone pomyślnie",
        ];
    }

    public function getChangesOnError() : array
    {
        return [
            'status' => ContentStatus::ERROR
        ];
    }

    private function getFolderName()
    {
        $className = explode("\\", Content::class);
        $className = array_pop($className);
        return "Models/{$className}/{$this->content->id}";
    }
}
