<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Format
{
    /**
     * Timestamps in the automation tables are written by the Python service in
     * the DB server's local time (PKT), so in production they are shown as-is.
     * A local dev box reading a UTC mirror gets them shifted to Karachi time.
     */
    public static function dateTime(?CarbonInterface $value): string
    {
        if (! $value) {
            return '—';
        }

        if (app()->environment('local')) {
            $value = $value->copy()->timezone('Asia/Karachi');
        }

        return $value->format('F j, Y g:i A');
    }
}
