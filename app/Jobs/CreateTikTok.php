<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use BorodinVasiliy\Stories;

abstract class CreateTikTok implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content
        ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $this->createScenario();

        // $this->createImgsAndAudios();

        $this->createDiarizations();

        $this->createVideos();

        $this->concatVideos();
    }

    abstract protected function getChatContext();

    private function createScenario()
    {
        $response = $this->getScenario($this->content->knowledge);

        $file = json_decode($response->getBody()->__toString())->openai->generated_text;
        $file = str_replace(["```json", "```"], "", $file);

        Storage::put($this->content::class . "_" . $this->content->id. "/scenariusz.json", $file);
    }

    private function createImgsAndAudios()
    {
        $file = Storage::get($this->getFolderName() . "/scenariusz.json");
        $file = json_decode($file);


        $i = 1;
        foreach($file->details as $scene)
        {
            $status = "";
            $try = 0;

            while($status != "success" && $try < 15) {

                $response = $this->getImage($scene);
                $file = json_decode($response->getBody()->__toString())->openai;
                $status = $file->status;
                $try++;
            }

            if($status == "success") {
                Storage::put($this->getFolderName() . "/scena{$i}.png", file_get_contents(
                    $file->items[0]->image_resource_url
                ));
            }

            $response = $this->getSound($scene);

            $file = json_decode($response->getBody()->__toString())->elevenlabs;

            if($file->status == "success") {

                Storage::put($this->getFolderName() . "/scena{$i}.mp3", file_get_contents(
                    $file->audio_resource_url
                ));
            }

            $i++;
        }
    }

    private function createDiarizations()
    {
        $file = Storage::get($this->getFolderName() . "/scenariusz.json");
        $file = json_decode($file);

        $jobsId = [];

        foreach( range(1, count($file->details)) as $i)
        {
            $response = $this->loadDiarization(Storage::path($this->getFolderName() . "/scena{$i}.mp3"));
            $result = json_decode($response->getBody(), true);
            $jobsId[] = $result['public_id'];
        }

        $tries = 0;
        foreach($jobsId as $key => $id)
        {
            $status = "";

            while($tries < 100 AND $status != "finished")
            {
                $response = $this->getDiarization($id);
                $result = json_decode($response->getBody());
                $status = $result->status;
                $tries++;
                if($status != "finished"){
                    sleep(5);
                }
            }

            $diarization = $result->results->gladia->diarization->entries;
            $diarization[0]->start_time = 0;

            $parts = [];
            foreach($diarization as $sentence)
            {
                $parts[] = [
                    "text" => $sentence->segment,
                    "start" => round($sentence->start_time, 2, PHP_ROUND_HALF_DOWN),
                    "end" => round($sentence->end_time, 2, PHP_ROUND_HALF_UP),
                ];

            }

            $sceneNumber = $key + 1;
            Storage::put($this->content::class . "_" . $this->content->id. "/scena{$sceneNumber}.json", json_encode($parts));
        }

    }

    private function createVideos()
    {
        $file = Storage::get($this->getFolderName() . "/scenariusz.json");
        $file = json_decode($file);

        $tmpPath = $this->getFolderName() . "/tmp";
        if(!Storage::exists($tmpPath)) {
            Storage::makeDirectory($tmpPath); //creates directory
        }

        $i = 1;

        foreach($file->details as $scene)
        {
            $path = Storage::path(str_replace("\\", "/", $this->getFolderName()));

            $stories = new Stories([
                "width" => 720,
                "height" => 1280,
                "duration" => $this->getAudioTime($path . "/scena{$i}.mp3")
            ]);

            $stories->addImage([
                "path" => $path . "/scena{$i}.png",
                "scale" => 0.7,
                "top" => 282
            ]);

            $stories->addText([
                "text" => $file->title,
                "path" => Storage::path("fonts/Podkova-Bold.ttf"),
                "size" => 42,
                "left" => 42,
                "width" => 636,
                "align" => "center",
                "top" => 97,
                "color" => "#ffffff",
            ]);

            $stories->addMusic($path . "/scena{$i}.mp3");

            $diarization = Storage::get($this->getFolderName() . "/scena{$i}.json");
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

            Storage::move($tmpPath. "/" . $hash, $this->getFolderName() . "/scena{$i}.mp4");
            Storage::delete(Storage::allFiles($tmpPath));

            $i++;
        }
    }

    private function concatVideos()
    {
        $file = Storage::get($this->getFolderName() . "/scenariusz.json");
        $file = json_decode($file);

        $path = str_replace("\\", "/", Storage::path($this->getFolderName()));

        $i = 1;
        $txt = "";
        foreach($file->details as $detail)
        {
            $txt .= "file scena" . $i . ".mp4 \n";
            $i++;
        }

        Storage::put($this->getFolderName() . "/video.txt", $txt);

        exec("ffmpeg -f concat -i {$path}/video.txt -c copy {$path}/video.mp4");
    }
    private function getFolderName()
    {
        return $this->content::class . "_" . $this->content->id;
    }

    private function getScenario($knowledge)
    {
        $client = new \GuzzleHttp\Client();

        return $client->request('POST', 'https://api.edenai.run/v2/text/chat', [
          'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . config("app.eden_ai_key"),
            'content-type' => 'application/json',
          ],
          'body' => json_encode([
            "response_as_dict" => true,
            "attributes_as_list" => false,
            "show_original_response" => false,
            "temperature" => 1,
            "max_tokens" => 4000,
            "providers" => "openai",
            "settings" => ["openai" => "gpt-4-1106-preview"],
            "text" => $knowledge->name . "\n" . $knowledge->content,
            "chatbot_global_action" => $this->getChatContext(),
          ])
        ]);
    }

    private function getImage($scene)
    {
        $client = new \GuzzleHttp\Client();

        return $response = $client->request('POST', 'https://api.edenai.run/v2/image/generation', [
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
            "text" => $scene->image
            ])
        ]);
    }

    private function getSound($scene)
    {
        $client = new \GuzzleHttp\Client();

        return $response = $client->request('POST', 'https://api.edenai.run/v2/audio/text_to_speech', [
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
                "text" => $scene->content
            ])
        ]);
    }

    private function getAudioTime($path)
    {
        $time = exec("ffmpeg -i " . escapeshellarg($path) . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
        list($hms, $milli) = explode('.', $time);
        list($hours, $minutes, $seconds) = explode(':', $hms);
        return ($hours * 3600) + ($minutes * 60) + $seconds + 1;
    }

    private function loadDiarization($path)
    {
        $client = new \GuzzleHttp\Client();

        $path = str_replace("\\", "/", $path);

        $name = explode("/", $path);
        $name = $name[count($name) -1];

        $fileContents = file_get_contents($path);

        return $client->request('POST', 'https://api.edenai.run/v2/audio/speech_to_text_async', [
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
                    'contents' => 'true',
                ],
                [
                    'name' => 'providers',
                    'contents' => 'gladia',
                ],
                [
                    'name' => 'language',
                    'contents' => 'pl',
                ],
            ],
        ]);
    }

    private function getDiarization($id)
    {
        $client = new \GuzzleHttp\Client();

        return $client->request('GET', "https://api.edenai.run/v2/audio/speech_to_text_async/{$id}?response_as_dict=true&show_original_response=false", [
          'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . config("app.eden_ai_key"),
          ],
        ]);
    }

    private function toUtf8($string)
    {
        $specialChars = [
            '\u0105', # ą
            '\u0107', # ć
            '\u0119', # ę
            '\u0142', # ł
            '\u0144', # ń
            '\u00f3', # ó
            '\u015b', # ś
            '\u017a', # ź
            '\u017c', # ż
            '\u0104', # Ą
            '\u0106', # Ć
            '\u0118', # Ę
            '\u0141', # Ł
            '\u0143', # Ń
            '\u00d3', # Ó
            '\u015a', # Ś
            '\u0179', # Ż
            '\u017b', # Ż
        ];
        $polishHtmlCodes = [
            'ą', # ą
            'ć', # ć
            'ę', # ę
            'ł', # ł
            'ń', # ń
            'ó', # ó
            'ś', # ś
            'ź', # ź
            'ż', # ż
            'Ą', # Ą
            'Ć', # Ć
            'Ę', # Ę
            'Ł', # Ł
            'Ń', # Ń
            'Ó', # Ó
            'Ś', # Ś
            'Ź', # Ż
            'Ż', # Ż
        ];
        return str_replace($specialChars, $polishHtmlCodes, $string);
    }
}

