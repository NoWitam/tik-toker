<?php

namespace App\Services;

use App\Models\Source;
use HeadlessChromium\BrowserFactory;

class SourceService
{
    public static function test(Source $source)
    {
        $data = ['knowledge' => []];

        $urls = self::getUrls($source);

        $data['next'] = $urls['next'];

        foreach($urls['knowledge'] as $url)
        {
            $data['knowledge'][$url] = static::getKnowledge($url, $source);
        }

    }


    private static function getUrls(Source $source)
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
            $page->navigate($source->url)->waitForNavigation();

            $dom = $page->dom();

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

    private static function getKnowledge($url, Source $source)
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
            $page->navigate($url)->waitForNavigation();

            $dom = $page->dom();

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

}
