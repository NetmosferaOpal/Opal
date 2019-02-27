<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

interface Loader
{
    public function start(
        Array $directories,
        Array $preprocessors,
        String $compileDirectory,
        Int $compileDirectoryPermissions,
        Int $compileFilePermissions
    );
}
