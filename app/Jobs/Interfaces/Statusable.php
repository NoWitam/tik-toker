<?php

namespace App\Jobs\Interfaces;

interface Statusable
{
    public function getChangesOnError() : array;
}
