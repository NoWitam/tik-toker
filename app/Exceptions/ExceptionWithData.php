<?php

namespace App\Exceptions;

use Illuminate\Support\Carbon;
use Throwable;

class ExceptionWithData extends \Exception
{
    private array $data;

    public function __construct(string $message, array $data = [], $code = 0, Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData(): array
    {
        return $this->data;
    }
}