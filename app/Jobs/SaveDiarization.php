<?php

namespace App\Jobs;

use App\Exceptions\ExceptionWithData;
use App\Jobs\Interfaces\Statusable;
use App\Models\Content;
use App\Models\Interfaces\Actionable;
use App\Exceptions\ReleaseActionableJobException;
use App\Models\Enums\ContentStatus;
use App\Services\ContentService;
use Illuminate\Support\Facades\Storage;

class SaveDiarization extends ActionableJob implements Statusable
{
    public $timeout = 300;
    public $api = "edenai";

    public function __construct(
        public Content $content,
        public int $scene,
        public string $diarizationId
    ){}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Zapisz napisy do audio';
    }

    
    public function run(): array
    {        
        $triesBeforeFail = 12;

        $result = $this->getDiarization($this->diarizationId);
        if($result->results->gladia->final_status != "succeeded"){
            throw new ReleaseActionableJobException(now()->addSeconds(30));
        }

        $this->action->increment('cost', $result->results->gladia->cost + $result->results->openai->cost);

        $diarization = $result->results->gladia->diarization->entries;

        if(count($diarization) == 0){
            if($this->action->attempts >= ($triesBeforeFail * 2) * ($this->action->retries + 1)) {
                throw new ExceptionWithData("Przekroczo limit prób. Ponów akcje", (array)$result);
            }
            dump("Try again {$this->diarizationId} by {$this->action->attempts} time in {$this->action->retries} retry");
            CreateDiarization::dispatch($this->content, $this->scene)->onQueue($this->queue)->setAction($this->action);
            $this->delete();
            return [];
        }

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

        $className = explode("\\", Content::class);
        $className = array_pop($className);
        Storage::put("Models/{$className}/{$this->content->id}/scene{$this->scene}.json", json_encode($parts));

        $this->action->increment('cost', $result->results->gladia->cost + $result->results->openai->cost);

        ContentService::manageProcess($this->content, $this->scene, "audio", $this->queue);

        return [
            "info" => "Audio pomyślnie zapisane",
            "data" => [
                "numer" => $this->scene,
                "jobId" => $this->diarizationId,
                "tekst" => $this->content->script['details'][$this->scene]['content'],
                "napisy" => $parts
            ]
        ];
    }

    public function getChangesOnError() : array
    {
        return [
            'status' => ContentStatus::ERROR
        ];
    }

    private function getDiarization($jobId)
    {
        $response = $this->callApi('GET', "https://api.edenai.run/v2/audio/speech_to_text_async/{$jobId}?response_as_dict=true&show_original_response=false", [
            'headers' => [
              'accept' => 'application/json',
              'authorization' => 'Bearer ' . config("app.eden_ai_key"),
            ],
        ]);

        return json_decode($response->getBody());
    }
}
