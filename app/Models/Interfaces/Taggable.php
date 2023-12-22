<?php

namespace App\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Taggable
{
    public function tags() : MorphToMany;
}
