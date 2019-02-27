<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\PackageDirectory;

function pathBelongsToPackageDirectory(String $path, PackageDirectory $directory): Bool{
    return
        substr($path, 0, $directory->pathLength) === $directory->path &&
        ($path[$directory->pathLength] === "/" || $path[$directory->pathLength] === "\\");
}
