<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Str;

trait Uuids
{
    /**
     * On model creating, set the primary key to UUID
     */
    public static function bootUuids()
    {
        static::creating(function (Model $model) {
            $model->{$model->getKeyName()} = Str::orderedUuid()->toString();
        });
    }
}