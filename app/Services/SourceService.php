<?php

namespace App\Services;

use App\Models\Source;
use HeadlessChromium\BrowserFactory;

class SourceService
{
    public static function test(Source $source)
    {
        $urls = self::getUrls($source);

        dd($urls);
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

        try {
            $page = $browser->createPage();
            $page->navigate($source->url)->waitForNavigation();

            $dom = $page->dom();
            eval($source->eval_knowledge_url);


        } finally {
            $browser->close();
        }


        return $urls;
    }

}
