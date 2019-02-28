<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Files;

use Netmosfera\Opal\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function readDirectory(Path $path): array{
    $flags = RecursiveDirectoryIterator::SKIP_DOTS;
    $directoryIterator = new RecursiveDirectoryIterator($path->path, $flags);

    $flattenedDirectoryIterator = new RecursiveIteratorIterator($directoryIterator);

    $files = [];
    foreach($flattenedDirectoryIterator as $fileInfo){
        /** @var SplFileInfo $fileInfo */
        $files[] = new Path($fileInfo->getPathname());
    }
    return $files;
}


