<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

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
