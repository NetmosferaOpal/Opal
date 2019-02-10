<?php declare(strict_types = 1);

ini_set("xdebug.overload_var_dump", "0");

(function($fileName){
    $file = fopen(__DIR__ . "/" . $fileName, "c");
    echo flock($file, LOCK_SH | LOCK_NB, $wouldBlock) ? "TRUE" : "FALSE", "\n";
    echo $wouldBlock === 1 ? "TRUE" : "FALSE", "\n";
    flock($file, LOCK_UN);
    fclose($file);
})($argv[1]);
