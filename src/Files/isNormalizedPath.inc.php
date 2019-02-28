<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Files;

/**
 * Returns `TRUE` if the given path is an absolute normalized path.
 */
function isNormalizedPath(String $path): Bool{
    return preg_match('@^
        ([a-zA-Z]:)?
        (?:[/\\\\][^/\\\\]+)*
    $@xsD', $path) === 1;
}
