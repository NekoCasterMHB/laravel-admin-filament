<?php

namespace Encore\Admin\Traits;

trait DefaultDatetimeFormat
{
    /**
     * Serialize datetime as `Y-m-d H:i:s` (the original laravel-admin
     * behavior before Laravel 7 switched to ISO-8601).
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
