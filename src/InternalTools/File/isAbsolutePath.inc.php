<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

/**
 * Returns `TRUE` if the given path is absolute.
 *
 * @param           String $path
 *
 * @returns         Bool
 */
function isAbsolutePath(String $path): Bool{
    return preg_match("@^(?:
        \/|
        [a-zA-Z]:\/|
        [a-zA-Z]:\\\
    )@x", $path) === 1;
}
