<?php

namespace App\Jobs;

use App\Models\Interfaces\Actionable;
use App\Models\Source;
use App\Services\SourceService;


class ParseSource extends ActionableJob
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
        return 'Wydobyj wiedze';
    }

    public function run(): array
    {
        return SourceService::get($this->source);
    }
}
