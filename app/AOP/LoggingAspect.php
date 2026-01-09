<?php

namespace App\AOP;

use Illuminate\Support\Facades\Log;

class LoggingAspect
{
    public static function logBefore($class, $method, $args)
    {
        $cleanedArgs = collect($args)->map(function ($arg) {
            return ($arg instanceof \Illuminate\Database\Eloquent\Model)
                ? get_class($arg) . ':' . $arg->getKey()
                : $arg;
        });

        Log::info("Entering $class::$method", ['args' => $cleanedArgs]);
    }

    public static function logAfter($class, $method, $result, $time)
    {
        Log::info("Exiting $class::$method", [
            'result' => $result,
            'execution_time_ms' => $time
        ]);
    }
}
