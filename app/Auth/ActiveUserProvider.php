<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;

class ActiveUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null): Builder
    {
        return parent::newModelQuery($model)->where('ativo', true);
    }
}
