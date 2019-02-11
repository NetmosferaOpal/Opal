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
 * @param           Closure|NULL $afterOpen
 *
 * @param           Closure|NULL $afterLock
 *
 * @param           Closure|NULL $afterRead
 *
 * @returns         String|NULL
 */
function fileRead(
    String $path,
    Float $secondsLimit,
    Float $secondsDelayBetweenTries,
    ?Closure $afterOpen = NULL,
    ?Closure $afterLock = NULL,
    ?Closure $afterRead = NULL
): ?String{
    assert(isAbsolutePath($path));

    $contents = NULL;

    retryWithinTimeLimit(function() use(
        &$path, &$contents, &$afterOpen, &$afterLock, &$afterRead
    ){
        $handle = @fopen($path, "r");
        if($afterOpen !== NULL) $afterOpen($handle !== FALSE);
        if($handle === FALSE) return FALSE;

        $lockAcquired = @flock($handle, LOCK_SH | LOCK_NB);
        if($afterLock !== NULL) $afterLock($lockAcquired);
        if($lockAcquired === FALSE){ @fclose($handle); return FALSE; }

        clearstatcache(FALSE, $path);
        $fileSize = filesize($path);
        $contents = $fileSize === 0 ? "" : fread($handle, $fileSize);
        if($afterRead !== NULL) $afterRead($contents);

        @flock($handle, LOCK_UN);
        @fclose($handle);
        return TRUE;
    }, $secondsLimit, $secondsDelayBetweenTries);

    return $contents;
}
