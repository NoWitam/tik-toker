<?php

namespace App\Jobs;

use App\Models\Interfaces\Actionable;
use App\Models\Source;
use App\Services\SourceService;


class ParseSourceTest extends ActionableJob
{
    public $timeout = 1200;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source
    ){

    }

    public function getActionable() : Actionable
    {
        return $this->source;
    }

    public function getType() : string
    {
        return 'Test';
    }

    public function run(): array
    {
        $data = SourceService::test($this->source);

        return [
            "info" => "Zanaleziono " . count($data['knowledge']) . " danych",
            "next_page" => $data['next'],
            "urls" => array_keys($data['knowledge'])
        ];
    }
}
