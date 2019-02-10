<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use Closure;
use const LOCK_SH;
use function clearstatcache;
use function filesize;
use function fread;

/**
 * Attempts to read a file within some time limit.
 *
 * @param           String $path
 *
 * @param           Float $secondsLimit
 *
 * @param           Float $secondsDelayBetweenTries
 *
 * @param           Closure|NULL $alsoDoWhileLocked
 *
 * @returns         String|NULL
 */
function fileRead(
    String $path,
    Float $secondsLimit,
    Float $secondsDelayBetweenTries,
    ?Closure $alsoDoWhileLocked = NULL
): ?String{
    assert(isAbsolutePath($path));

    $contents = NULL;

    retryWithinTimeLimit(function() use(&$path, &$contents, &$alsoDoWhileLocked){
        $file = @fopen($path, "r");
        if($file === FALSE){ return FALSE; }
        $lockAcquired = @flock($file, LOCK_SH | LOCK_NB);
        if($lockAcquired === FALSE){ return FALSE; }
        clearstatcache(FALSE, $path);
        $fileSize = filesize($path);
        $contents = @fread($file, $fileSize);
        if($alsoDoWhileLocked !== NULL){ $alsoDoWhileLocked($contents); }
        @flock($file, LOCK_UN);
        @fclose($file);
        return TRUE;
    }, $secondsLimit, $secondsDelayBetweenTries);

    return $contents;
}
