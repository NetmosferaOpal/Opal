<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use const LOCK_SH;
use function clearstatcache;
use function filesize;
use function fread;

/**
 * Attempts to read a file within some time limit.
 *
 * @param           String $path
 *
 * @param           Float $maxTimeInSeconds
 *
 * @param           Float $retryDelayInSeconds
 *
 * @returns         String|NULL
 */
function fileContents(
    String $path,
    Float $maxTimeInSeconds,
    Float $retryDelayInSeconds
): ?String{
    assert(isAbsolutePath($path));

    $contents = NULL;

    retryWithinTimeLimit(function() use($path, &$contents){
        $file = @fopen($path, "r");
        if($file === FALSE){ return FALSE; }
        $lockAcquired = @flock($file, LOCK_SH | LOCK_NB);
        if($lockAcquired === FALSE){ return FALSE; }
        clearstatcache(FALSE, $path);
        $contents = @fread($file, filesize($path));
        @flock($file, LOCK_UN);
        @fclose($file);
        return TRUE;
    }, $maxTimeInSeconds, $retryDelayInSeconds);

    return $contents;
}
