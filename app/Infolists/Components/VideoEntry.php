<?php

namespace App\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Entry;

class VideoEntry extends Entry
{
    public Closure|string $src;
    protected string $view = 'infolists.components.video-entry';


    public function setSrc(Closure|string $src): self
    {
        $this->src = $src;
        return $this;
    }
}
