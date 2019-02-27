<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\PackagePath;

function pathBelongsToPackagePath(String $path, PackagePath $directory): Bool{
    return
        substr($path, 0, $directory->pathLength) === $directory->path &&
        ($path[$directory->pathLength] === "/" || $path[$directory->pathLength] === "\\");
}
