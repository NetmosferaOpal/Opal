<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function dirEmpty(String $directory){
    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($directory, $flags);

    $flags = RecursiveIteratorIterator::CHILD_FIRST;
    $flattenedDirectoryIterator = new RecursiveIteratorIterator($directoryIterator, $flags);

    foreach($flattenedDirectoryIterator as $fileInfo){
        /** @var SplFileInfo $fileInfo */
        $file = $fileInfo->getPathname();
        is_dir($file) ? rmdir($file) : unlink($file);
    }
}
