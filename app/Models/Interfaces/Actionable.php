<?php

namespace App\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Actionable
{
    public function actions() : MorphMany;
}
