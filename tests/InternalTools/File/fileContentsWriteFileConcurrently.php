<?php declare(strict_types = 1);

(function($fileName){
    ob_start();
    var_dump($file = fopen(__DIR__ . "/" . $fileName, "c"));
    var_dump(flock($file, LOCK_EX | LOCK_NB));
    var_dump(ftruncate($file, 0));
    var_dump(fwrite($file, random_bytes(random_int(0, 1000))));
    var_dump(flock($file, LOCK_UN));
    var_dump(fclose($file));
    $result = ob_get_clean();
    file_put_contents($fileName . ".result.txt", $result);
})($argv[1]);
