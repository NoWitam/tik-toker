<?php

namespace App\Exceptions;

use Illuminate\Support\Carbon;
use Throwable;

class ReleaseActionableJobException extends \Exception
{
    private Carbon $time;
    public function __construct(Carbon $time, $code = 0, Throwable $previous = null)
    {
        $this->time = $time;
        $readyAt = $time->format('H:i:s');
        parent::__construct("Zadanie dostępne o {$readyAt}", $code, $previous);
    }

    public function getTime(): Carbon
    {
        return $this->time;
    }
}