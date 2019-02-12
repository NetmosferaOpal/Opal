<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;
use Closure;
use const LOCK_UN;
use function assert;
use function clearstatcache;
use function dirname;
use function fclose;
use function fflush;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function mkdir;
use function umask;

/**
 * @TODOC
 *
 * @param           String $path
 * @param           String $contents
 * @param           Int $directoryMode
 * @param           Float $maxTimeInSeconds
 * @param           Float $retryDelayInSeconds
 * @param           Closure|NULL $afterCreateDirectoryAttempt
 * @param           Closure|NULL $afterOpenAttempt
 * @param           Closure|NULL $afterLockAttempt
 * @param           Closure|NULL $afterWriteAttempt
 * @returns         Bool
 */
function fileWrite(
    String $path,
    String $contents,
    Int $directoryMode,
    Float $maxTimeInSeconds,
    Float $retryDelayInSeconds,
    ?Closure $afterCreateDirectoryAttempt = NULL,
    ?Closure $afterOpenAttempt = NULL,
    ?Closure $afterLockAttempt = NULL,
    ?Closure $afterWriteAttempt = NULL
): Bool{
    assert(isAbsolutePath($path));
    $directory = dirname($path);
    return retryWithinTimeLimit(function() use(
        &$directory, &$directoryMode, &$path, &$contents, &$afterCreateDirectoryAttempt,
        &$afterOpenAttempt, &$afterLockAttempt, &$afterWriteAttempt
    ){
        $saveUMask = umask(0);
        @mkdir($directory, $directoryMode, TRUE);
        umask($saveUMask);
        clearstatcache(FALSE, $directory);
        if($afterCreateDirectoryAttempt !== NULL) $afterCreateDirectoryAttempt();

        try{
            $handle = fopen($path, "c");
            if($afterOpenAttempt !== NULL) $afterOpenAttempt($handle !== FALSE);
            if($handle === FALSE) return FALSE;
            // @TODO this should also set the permissions to the file using umask

            $lockAcquired = flock($handle, LOCK_EX | LOCK_NB);
            if($afterLockAttempt !== NULL) $afterLockAttempt($lockAcquired);
            if($lockAcquired === FALSE) return FALSE;

            ftruncate($handle, 0);
            fwrite($handle, $contents);
            fflush($handle);
            if($afterWriteAttempt !== NULL) $afterWriteAttempt();

            return TRUE;
        }finally{
            if($handle !== FALSE) fclose($handle);
        }
    }, $maxTimeInSeconds, $retryDelayInSeconds);
}
