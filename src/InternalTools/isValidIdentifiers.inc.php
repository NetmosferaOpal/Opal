<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

/**
 * Returns `TRUE` if the array contains valid identifiers, and at least one.
 */
function isValidIdentifiers(Array $identifiers): Bool{
    if(count($identifiers) < 1){
        return FALSE;
    }

    foreach($identifiers as $identifier){
        if(isValidIdentifier($identifier) === FALSE){
            return FALSE;
        }
    }

    return TRUE;
}
