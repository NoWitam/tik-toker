<?php

namespace App\Jobs;

use App\Models\Interfaces\Actionable;
use App\Models\Source;
use App\Services\SourceService;


class ParseSourceTest extends ActionableJob
{

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
        return 'test';
    }

    public function run(): array
    {
        $data = SourceService::test($this->source);

        return $data;
    }
}
