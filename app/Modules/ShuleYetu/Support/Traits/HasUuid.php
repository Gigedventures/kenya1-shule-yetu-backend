<?php

namespace App\Modules\ShuleYetu\Support\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            $keyName = $model->getKeyName();
            if (!$model->getAttribute($keyName)) {
                $model->setAttribute($keyName, (string) Str::uuid());
            }
        });
    }
}
