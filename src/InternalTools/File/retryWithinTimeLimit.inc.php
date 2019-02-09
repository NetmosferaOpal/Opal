<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use Closure;
use Error;

/**
 * @TODOC
 *
 * @param           Closure $function
 *
 * @param           Float $secondsLimit
 * Limit the execution time in seconds.
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
        throw new Error("Limit must be non negative");
    }

    if($secondsDelayBetweenTries < 0.0){
        throw new Error("Delay must be non negative");
    }

    $startTime = microtime(TRUE);

    while(TRUE){
        $callSucceeded = $function();

        if($callSucceeded){
            return TRUE;
        }else{
            $elapsedTime = microtime(TRUE) - $startTime;
            $nextAttemptInSecondsRelativeToStartTime = $elapsedTime + $secondsDelayBetweenTries;
            if($nextAttemptInSecondsRelativeToStartTime > $secondsLimit){
                return FALSE;
            }else{
                usleep((Int)($secondsDelayBetweenTries * 1000000));
            }
        }
    }
}
