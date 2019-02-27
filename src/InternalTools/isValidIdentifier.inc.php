<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

/**
 * Returns `TRUE` if the given string would make a valid PHP identifier.
 */
function isValidIdentifier(String $identifier): Bool{
    return preg_match("@^[a-zA-Z_][a-zA-Z_0-9]*$@", $identifier) === 1;
}
