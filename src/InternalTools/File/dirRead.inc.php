<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function dirRead(String $directory){
    // directory iterator removes the last "/" and adds its own separator when
    // concatenating the new paths
    // @TODO must sort alphabetically
    $directory = rtrim($directory, "\\/");
    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($directory, $flags);
    $flattenedDirectoryIterator = new RecursiveIteratorIterator($directoryIterator);
    foreach($flattenedDirectoryIterator as $fileInfo){
        /** @var SplFileInfo $fileInfo */
        yield $fileInfo->getPathname();
    }
};
