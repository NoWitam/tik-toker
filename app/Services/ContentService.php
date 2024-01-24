<?php

namespace App\Services;

use App\Jobs\ContactVideos;
use App\Jobs\CreateAudio;
use App\Jobs\CreateImage;
use App\Jobs\CreateVideo;
use App\Models\Account;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Series;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ContentService
{
    public static function generateVideo(Content $content)
    {
        $completedProcesses = self::getCompletedProcess($content);

        if($content->archivalScript == null) {
            $completedVideos = 0;
            foreach($completedProcesses as $scene => $processes)
            {
                if(!$processes["image"]) {
                    CreateImage::dispatch($content, $scene)->onQueue(auth()->check() ? 'user' : 'content_create');
                }

                if(!$processes["audio"]) {
                    CreateAudio::dispatch($content, $scene)->onQueue(auth()->check() ? 'user' : 'content_create');
                }

                if($processes["image"] AND $processes["audio"] AND !$processes["video"]) {
                    CreateVideo::dispatch($content, $scene)->onQueue(auth()->check() ? 'user' : 'content_create');
                }

                if($processes["video"]) {
                    $completedVideos++;
                }
            }

            if($completedVideos == count($completedProcesses)) {
                ContactVideos::dispatch($content)->onQueue(auth()->check() ? 'user' : 'content_create');
            }
        } else {

            foreach($content->script['details'] as $i => $scene)
            {
                $archivalScene = $content->archivalScript['details'][$i];

                if($scene['image'] != $archivalScene['image']) {
                    CreateImage::dispatch($content, $i)->onQueue(auth()->check() ? 'user' : 'content_create');
                } else {
                    $completedProcesses[$i]["image"] = true;
                }
                
                if($scene['content'] != $archivalScene['content']) {
                    CreateAudio::dispatch($content, $i)->onQueue(auth()->check() ? 'user' : 'content_create');
                } else {
                    $completedProcesses[$i]["audio"] = true;
                }

                $completedProcesses[$i]["video"] = ($completedProcesses[$i]["image"] AND $completedProcesses[$i]["audio"]) ? true : false;
            } 
        }

        Cache::put("content_{$content->id}_completed_process", $completedProcesses);
        
        foreach($completedProcesses as $process)
        {
            if(!$process["video"]) {
                $content->update([
                    'status' => ContentStatus::IN_PROCESS
                ]);
                break;
            }
        }
    }

    public static function generateVideoForWaitingContent()
    {
        $contents = Content::where('status', ContentStatus::WAITING)
            ->whereNotNull('script')
            ->whereBetween('publication_time', [
                Carbon::now()->startOfDay()->subDays(3),
                Carbon::now()->endOfDay()->subDays(3)
            ])->get();

        foreach($contents as $content)
        {
            self::generateVideo($content);
        }
    }

    public static function clearErrorContent()
    {
        $contents = Content::where('status', ContentStatus::ERROR)
                        ->where('publication_time', '=<', Carbon::now()->startOfDay())
                        ->get();

        foreach($contents as $content)
        {
            $content->delete();
        }
    }

    public static function manageProcess(Content $content, int $scene, string $action, string $queue)
    {
        $completedProcesses = self::getCompletedProcess($content);
        $completedProcesses[$scene][$action] = true;
        Cache::put("content_{$content->id}_completed_process", $completedProcesses);

        if($action == "video") {
            foreach($completedProcesses as $process)
            {
                if(!$process["video"]) {
                    return;
                }
            }

            ContactVideos::dispatch($content)->onQueue($queue);
            return;
        }

        if($action == "image") {
            if($completedProcesses[$scene]["audio"]) {
                CreateVideo::dispatch($content, $scene)->onQueue($queue);
            }

            return;
        }

        if($action == "audio") {
            if($completedProcesses[$scene]["image"]) {
                CreateVideo::dispatch($content, $scene)->onQueue($queue);
            }
                        
            return;
        }
    }

    public static function createContentForNextWeek()
    {
        $series = Series::with(['publications', 'contents'])->get();

        foreach($series as $serie)
        {
            $howManyNeed = count($serie->publications);

            if($howManyNeed) {
                $knowledges = SeriesService::getAvailableKnowledgeBuilder($serie)->select(['id', 'name'])->withCount(['contents' => function(Builder $q) use ($serie) {
                    $q->where('series_id', $serie->id);
                }])->orderBy('contents_count')
                    ->inRandomOrder()
                    ->limit($howManyNeed)
                    ->get();
                    
                foreach($knowledges as $index => $knowledge)
                {
                    $publication = $serie->publications[$index];

                    Content::create([
                        'name' => $knowledge->name,
                        'series_id' => $serie->id,
                        'knowledge_id' => $knowledge->id,
                        'publication_time' => Carbon::now()->endOfWeek()->addDays($publication->day)->setTimeFromTimeString($publication->time)
                    ]);
                }
            }
        }
    }

    private static function getCompletedProcess(Content $content)
    {
        return Cache::get("content_{$content->id}_completed_process", array_fill(0, count($content->script['details']), 
            [
                "image" => false,
                "audio" => false,
                "video" => false
            ]
        ));
    }
}
