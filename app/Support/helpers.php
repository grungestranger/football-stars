<?php

use Illuminate\Database\Eloquent\Model;

if (! function_exists('lockForUpdate')) {
    /**
     * Execute a Closure within locking one row for update.
     *
     * @param  Closure $callback
     * @param  Model[] $models
     */
    function lockForUpdate(Closure $callback, ...$models)
    {
        $model = array_first($models);

        $keys = array_map(function (Model $value) {
            return $value->getKey();
        }, $models);

        DB::transaction(function () use ($model, $keys, $callback) {
            $model->whereIn($model->getKeyName(), $keys)->lockForUpdate()->get();

            $callback();
        });
    }
}
