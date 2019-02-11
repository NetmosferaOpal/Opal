<?php declare(strict_types = 1);

(function($fileName){
    $file = fopen(__DIR__ . "/" . $fileName, "c");
    echo flock($file, LOCK_EX | LOCK_NB, $wouldBlock) ? "TRUE" : "FALSE", "\n";
    echo $wouldBlock === 1 ? "TRUE" : "FALSE", "\n";
    flock($file, LOCK_UN);
    fclose($file);
})($argv[1]);
