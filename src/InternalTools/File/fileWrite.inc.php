<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;
use function clearstatcache;
use Closure;
use function file_exists;

/**
 * @TODOC
 *
 * @param           String $path
 * @param           String $contents
 * @param           Int $directoryMode
 * @param           Float $maxTimeInSeconds
 * @param           Float $retryDelayInSeconds
 * @param           Closure|NULL $afterCreateDirectory
 * @param           Closure|NULL $afterOpen
 * @param           Closure|NULL $afterLock
 * @param           Closure|NULL $afterWrite
 * @returns         Bool
 */
function fileWrite(
    String $path,
    String $contents,
    Int $directoryMode,
    Float $maxTimeInSeconds,
    Float $retryDelayInSeconds,
    ?Closure $afterCreateDirectory = NULL,
    ?Closure $afterOpen = NULL,
    ?Closure $afterLock = NULL,
    ?Closure $afterWrite = NULL
): Bool{
    assert(isAbsolutePath($path));
    $directory = dirname($path);
    return retryWithinTimeLimit(function() use(
        &$directory, &$directoryMode, &$path, &$contents,
        &$afterCreateDirectory, &$afterOpen, &$afterLock, &$afterWrite
    ){
        $saveUMask = umask(0);
        @mkdir($directory, $directoryMode, TRUE);
        @umask($saveUMask);
        clearstatcache(FALSE, $directory);
        if($afterCreateDirectory !== NULL) $afterCreateDirectory();

        $handle = @fopen($path, "c");
        if($afterOpen !== NULL) $afterOpen($handle !== FALSE);
        if($handle === FALSE) return FALSE;
        // @TODO this should also set the permissions to the file using umask

        $lockAcquired = flock($handle, LOCK_EX | LOCK_NB);
        if($afterLock !== NULL) $afterLock($lockAcquired);
        if($lockAcquired === FALSE){ fclose($handle); return FALSE; }

        ftruncate($handle, 0);
        fwrite($handle, $contents);
        fflush($handle);
        if($afterWrite !== NULL) $afterWrite();

        flock($handle, LOCK_UN);
        fclose($handle);
        return TRUE;
    }, $maxTimeInSeconds, $retryDelayInSeconds);
}
