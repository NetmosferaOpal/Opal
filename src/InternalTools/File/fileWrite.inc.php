<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;
use Closure;

/**
 * @TODOC
 *
 * @param           String $path
 * `String`
 *
 * @param           String $contents
 * `String`
 *
 * @param           Int $directoryMode
 * `Int`
 *
 * @param           Float $maxTimeInSeconds
 * `Float`
 *
 * @param           Float $retryDelayInSeconds
 * `Float`
 *
 * @param           Closure|NULL $callback
 * `Closure0` Called after the file has been written, but while it is still locked.
 *
 * @returns         Bool
 * `Bool`
 */
function fileWrite(
    String $path,
    String $contents,
    Int $directoryMode,
    Float $maxTimeInSeconds,
    Float $retryDelayInSeconds,
    ?Closure $callback = NULL
): Bool{
    assert(isAbsolutePath($path));
    $directory = dirname($path);
    return retryWithinTimeLimit(function() use(
        &$directory, &$directoryMode, &$path, &$contents, &$callback
    ){
        $saveUMask = umask(0);
        @mkdir($directory, $directoryMode, TRUE);
        @umask($saveUMask);
        $file = @fopen($path, "c");
        if($file === FALSE) return FALSE;
        // @TODO this should also set the permissions to the file using umask
        $lockAcquired = flock($file, LOCK_EX | LOCK_NB);
        if($lockAcquired === FALSE) return FALSE;
        ftruncate($file, 0);
        fwrite($file, $contents);
        fflush($file);
        if($callback !== NULL) $callback();
        flock($file, LOCK_UN);
        fclose($file);
        return TRUE;
    }, $maxTimeInSeconds, $retryDelayInSeconds);
}
