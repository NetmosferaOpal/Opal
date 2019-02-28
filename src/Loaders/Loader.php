<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

use Netmosfera\Opal\Path;

interface Loader
{
    public function start(
        Array $directories,
        Array $preprocessors,
        Path $compileDirectory,
        Int $compileDirectoryPermissions,
        Int $compileFilePermissions
    );
}
