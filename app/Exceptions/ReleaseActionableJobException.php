<?php

namespace App\Exceptions;

use Illuminate\Support\Carbon;
use Throwable;

class ReleaseActionableJobException extends \Exception
{
    private int $time;
    public function __construct(int $time, $code = 0, Throwable $previous = null)
    {
        $this->time = $time;
        $readyAt = Carbon::createFromTimestamp($time)->format('H:i:s');
        parent::__construct("API dostępne o {$readyAt}", $code, $previous);
    }

    public function getTime(): int
    {
        return $this->time;
    }
}