<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait Uuids
{
    public static function bootUuids()
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::orderedUuid();
        });
    }
}