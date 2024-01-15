<?php

namespace App\Services;

use App\Models\Knowledge;
use App\Models\Source;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\OperationTimedOut;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

class SourceService
{
    public static function test(Source $source)
    {
        $data = ['knowledge' => []];

        Log::info("Go to url - " . $source->url);
        $urls = self::getUrls($source, $source->url);
        Log::info("End go to url - " . $source->url);

        $data['next'] = $urls['next'];

        foreach($urls['knowledge'] as $url)
        {
            Log::info("Go to url - " . $url);
            $data['knowledge'][$url] = static::getKnowledge($source, $url);
            Log::info("End go to url - " . $url);
        }

        return $data;
    }

    public static function get(Source $source)
    {
        if(!Cache::has('source_parse.' . $source->id)) {
            $url = $source->url;

            $newKnowledgeUrls = [];
            $lastUrls = ['for start loop'];
    
            while(count($lastUrls) != 0) {
    
                Log::info("Go to url - " . $url);
                $data = self::getUrls($source, $url);
                Log::info("End go to url - " . $url);
    
                $lastUrls = $data['knowledge'];
    
                $existingUrls = Knowledge::whereIn('unique', $lastUrls)->get()->pluck('unique')->toArray();
    
                $lastUrls = array_diff($lastUrls, $existingUrls);
    
                $url = $data['next'];
                $newKnowledgeUrls = array_merge($lastUrls, $newKnowledgeUrls); 
    
                if($url == null) {
                    break;
                }
            }
    
            Cache::put('source_parse.' . $source->id, $newKnowledgeUrls, 2 * 60 * 60);
            Cache::put('source_parse_all.' . $source->id, $newKnowledgeUrls, 2.5 * 60 * 60);
            $allNewKnowledgeUrls = $newKnowledgeUrls;
        } else {
            $newKnowledgeUrls = Cache::get('source_parse.' . $source->id);
            $allNewKnowledgeUrls = Cache::get('source_parse_all.' . $source->id);
        }

        foreach($newKnowledgeUrls as $newKnowledgeUrl)
        {
            Log::info("Go to url - " . $newKnowledgeUrl);
            $knowledge = self::getKnowledge($source, $newKnowledgeUrl);
            Log::info("End go to url - " . $newKnowledgeUrl);

            $model = Knowledge::create([     
                'name' => $knowledge['name'],
                'content' => $knowledge['content'],
                'unique' => $newKnowledgeUrl
            ]);
            $model->tags()->sync($source->tags);

            $newKnowledgeUrls = array_diff($newKnowledgeUrls, [$newKnowledgeUrl]);
            Cache::put('source_parse.' . $source->id, $newKnowledgeUrls, 2 * 60 * 60);
        }

        Cache::forget('source_parse.' . $source->id);
        Cache::forget('source_parse_all.' . $source->id);

        return [
            "info" => "Zanaleziono " . count($allNewKnowledgeUrls) . " nowych danych",
            "urls" => $allNewKnowledgeUrls
        ];
    }


    private static function getUrls(Source $source, $url)
    {
        $browserFactory = new BrowserFactory(config('app.chromium'));

        $browser = $browserFactory->createBrowser([
          'headless' => true, // disable headless mode
          'sendSyncDefaultTimeout' => 20000,
          'connectionDelay' => 500
        ]);

        $urls = [];
        $next = "";

        try {
            $page = $browser->createPage();
            self::goToUrl($page, $url);
            $dom = HtmlDomParser::str_get_html($page->getHtml());

            eval($source->eval_knowledge_url);
            eval($source->eval_next);

        } finally {
            $browser->close();
        }
        
        return [
            'next' => $next,
            'knowledge' => $urls
        ];
    }

    private static function getKnowledge(Source $source, $url)
    {
        $browserFactory = new BrowserFactory(config('app.chromium'));

        $browser = $browserFactory->createBrowser([
          'headless' => true, // disable headless mode
          'sendSyncDefaultTimeout' => 20000,
          'connectionDelay' => 500
        ]);

        $name = "";
        $content = "";

        try {
            $page = $browser->createPage();
            self::goToUrl($page, $url);
            $dom = HtmlDomParser::str_get_html($page->getHtml());

            eval($source->eval_knowledge_name);
            eval($source->eval_knowledge_content);


        } finally {
            $browser->close();
        }


        return [
            'name' => $name,
            'content' => $content
        ];
    }

    private static function goToUrl($page, $url, $tries = 3, $timeout = 30000, $sleep = 5)
    {  
        if($tries == 0) {
            throw new \Exception("Time out - " . $url);
        } else {
            try {
                $page->navigate($url)->waitForNavigation(timeout: $timeout);
            } catch(OperationTimedOut $e) {
                sleep($sleep);
                self::goToUrl($page, $url, --$tries, $timeout, $sleep);
            }
        }
    }

}
