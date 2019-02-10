<?php declare(strict_types = 1);

(function($fileName) use(&$argv){
    $file = fopen(__DIR__ . "/" . $fileName, "c");
    flock($file, LOCK_EX | LOCK_NB);
    ftruncate($file, 0);
    fwrite($file, random_bytes(random_int(0, 1000)));
    flock($file, LOCK_UN);
    fclose($file);
})($argv[1]);
