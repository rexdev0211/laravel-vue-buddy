<?php

namespace App\Services;

use Log;

class Timer
{
    /**
     * Timers
     *
     * @var array
     */
    private static $timers = [];

    /**
     * Results
     *
     * @var array
     */
    private static $results = [];

    /**
     * Start timer
     *
     * @param String $name Name
     *
     * @return array
     */
    public static function start($name = null)
    {
        self::$timers[$name] = microtime(true);
    }

    /**
     * End timer
     *
     * @param string $name  Name
     *
     * @return array
     */
    public static function end($name = null)
    {
        if (isset(self::$timers[$name])) {
            self::$results[$name] = round(microtime(true) - self::$timers[$name], 4);
        } else {
            self::$results[$name] = 0;
        }

        $data = self::get($name);

        if ($name) {
            //Log::info('[TIMER] ' . $name . ': ' . $data);
        }

        return $data;
    }

    /**
     * Get timer
     *
     * @param string $name Name
     *
     * @return object|array
     */
    public static function get($name = null)
    {
        if ($name) {
            return isset(self::$results[$name]) ? self::$results[$name].'s' : '0s';
        } else {
            return (object) self::$results;
        }
    }
}
