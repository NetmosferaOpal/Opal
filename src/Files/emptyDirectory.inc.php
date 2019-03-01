<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Files;

use Netmosfera\Opal\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function emptyDirectory(Path $path): void{

    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($path->string, $flags);

    $flags = RecursiveIteratorIterator::CHILD_FIRST;
    $flatDirectoryIterator = new RecursiveIteratorIterator($directoryIterator, $flags);

    foreach($flatDirectoryIterator as $fileInfo){
        /** @var SplFileInfo $fileInfo */
        if($fileInfo->getFilename() === "opal.lock"){
            continue;
        }

        $file = $fileInfo->getPathname();
        is_dir($file) ? rmdir($file) : unlink($file);
    }
}
