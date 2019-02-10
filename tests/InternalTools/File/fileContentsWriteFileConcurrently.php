<?php declare(strict_types = 1);

ini_set("xdebug.overload_var_dump", "0");

(function($fileName){
    var_dump($file = fopen(__DIR__ . "/" . $fileName, "c"));
    var_dump(flock($file, LOCK_EX | LOCK_NB));
    var_dump(ftruncate($file, 0));
    var_dump(fwrite($file, random_bytes(random_int(0, 1000))));
    var_dump(flock($file, LOCK_UN));
    var_dump(fclose($file));
})($argv[1]);
