<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

/**
 * @TODOC
 *
 * @param           String $path
 *
 * @param           String $phpSource
 *
 * @param           Int $directoryMode
 *
 * @param           Float $maxTimeInSeconds
 *
 * @param           Float $retryDelayInSeconds
 *
 * @param           Bool $requireIt
 *
 * @returns         Bool
 */
function requireNewPHPFile(
    String $path,
    String $phpSource,
    Int $directoryMode,
    Float $maxTimeInSeconds,
    Float $retryDelayInSeconds,
    Bool $requireIt
): Bool{
    assert(isAbsolutePath($path));

    $directory = dirname($path);

    return retryWithinTimeLimit(function() use(
        $directory,
        $directoryMode,
        $path,
        $phpSource,
        $requireIt
    ){
        $saveUMask = umask(0);
        @mkdir($directory, $directoryMode, TRUE);
        @umask($saveUMask);
        $file = @fopen($path, "c");
        if($file === FALSE){ return FALSE; }
        $lockAcquired = flock($file, LOCK_EX | LOCK_NB);
        if($lockAcquired === FALSE){ return FALSE; }
        ftruncate($file, 0);
        fwrite($file, $phpSource);
        fflush($file);
        if($requireIt){
            (function($__OPAL_FILE__){
                require $__OPAL_FILE__;
            })($path);
        }
        flock($file, LOCK_UN);
        fclose($file);
        return TRUE;
    }, $maxTimeInSeconds, $retryDelayInSeconds);
}
