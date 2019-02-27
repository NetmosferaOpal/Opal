<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Closure;

/**
 * Executes a function until it succeeds.
 *
 * @param           Closure $function
 *
 * @param           Float $secondsLimit
 * Limits the execution time in seconds.
 *
 * @param           Float $secondsBeforeRetry
 * Pause between each `$function` execution attempt. If set to `0` `$function` will be
 * retried immediately after the previous attempt has failed.
 *
 * @return          Bool
 * Returns `TRUE` if the execution of `$function` succeeded within the allowed time limit,
 * or `FALSE` if the timeout was hit.
 */
function retryWithinTimeLimit(
    Closure $function,
    Float $secondsLimit,
    Float $secondsBeforeRetry = 0.1
): Bool{
    $secondsLimit = $secondsLimit >= 0.0 ? $secondsLimit : 0.0;
    $secondsBeforeRetry = $secondsBeforeRetry >= 0.0 ? $secondsBeforeRetry : 0.0;

    $startTime = microtime(TRUE);

    while($function() === FALSE){
        $elapsedTimeSoFar = microtime(TRUE) - $startTime;

        $nextElapsedTimePrediction = $elapsedTimeSoFar + $secondsBeforeRetry;

        $willExceedTimeLimit = $nextElapsedTimePrediction > $secondsLimit;
        if($willExceedTimeLimit){
            return FALSE;
        }

        usleep((Int)($secondsBeforeRetry * 1000000));
    }

    return TRUE;
}
