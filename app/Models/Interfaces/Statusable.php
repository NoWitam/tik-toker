<?php

namespace App\Models\Interfaces;

interface Statusable
{
    public function getChangesOnError() : array;
}
