<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function dirReadRecursive(String $directory){
    $directory = rtrim($directory, "\\/");
    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($directory, $flags);
    $flattenedDirectoryIterator = new RecursiveIteratorIterator($directoryIterator);
    $files = [];
    foreach($flattenedDirectoryIterator as $fileInfo){
        /** @var SplFileInfo $fileInfo */
        $files[] = $fileInfo->getPathname();
    }
    return $files;
}


