<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use Closure;
use Exception;

/**
 * @TODOC
 *
 * @param           Closure $function
 *
 * @param           Float $secondsLimit
 * Limits the execution time in seconds.
 *
 * @param           Float $secondsDelayBetweenTries
 * Pause between each `$function` execution attempt. If set to `0` `$function` will be
 * retried immediately after the previous attempt has failed.
 *
 * @return          Bool
 * Returns `TRUE` if the execution of `$function` succeeded within the allowed time limit,
 * or `FALSE` if the timeout was hit.
 *
 * @throws
 */
function retryWithinTimeLimit(
    Closure $function,
    Float $secondsLimit,
    Float $secondsDelayBetweenTries = 0.1
): Bool{
    if($secondsLimit < 0.0){
        throw new Exception("Limit must be non negative");
    }

    if($secondsDelayBetweenTries < 0.0){
        throw new Exception("Delay must be non negative");
    }

    $startTime = microtime(TRUE);

    while(TRUE){
        $operationSucceeded = $function();

        if($operationSucceeded){ return TRUE; }

        $elapsedTimeSoFar = microtime(TRUE) - $startTime;

        $nextElapsedTimePrediction = $elapsedTimeSoFar + $secondsDelayBetweenTries;

        $willExceedTimeLimit = $nextElapsedTimePrediction > $secondsLimit;

        if($willExceedTimeLimit){ return FALSE; }

        usleep((Int)($secondsDelayBetweenTries * 1000000));
    }
}
