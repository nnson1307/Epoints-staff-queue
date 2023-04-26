<?php
namespace MyCore\Repository;

use Carbon\Carbon;

/**
 * Trait FormatStravaTime
 * @package MyCore\Repository
 * @author DaiDP
 * @since Jul, 2019
 */
trait FormatStravaTime
{
    /**
     * Format thời gian chạy
     *
     * @param $timestamp
     * @return string
     */
    protected function formatTime($timestamp)
    {
        $time = Carbon::createFromTimestamp($timestamp);
        $time->setTimezone('UTC');
        return $time->format($timestamp < 3600 ? 'i:s' : 'H:i:s');
    }
}