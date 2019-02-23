<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

/**
 * Returns `TRUE` if the given path is an absolute normalized path.
 *
 * @param           String $path
 *
 * @returns         Bool
 */
function isNormalizedPath(String $path): Bool{
    return preg_match('@^
        ([a-zA-Z]:)?
        (?:[/\\\\][^/\\\\]+)*
    $@xsD', $path) === 1;
}
