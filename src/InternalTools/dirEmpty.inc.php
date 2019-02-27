<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function dirEmpty(String $directory): void{
    assert(isNormalizedPath($directory));

    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($directory, $flags);

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
