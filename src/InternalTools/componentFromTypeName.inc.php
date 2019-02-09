<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;

/**
 * Creates a {@see PackageComponent} from a type name.
 *
 * Returns a {@see PackageComponent} in the quickest way possible, without performing any
 * validation (unless assertions are enabled).
 *
 * @param           String $typeName
 *
 * @return          PackageComponent|NULL
 */
function componentFromTypeName(String $typeName): ?PackageComponent{
    $identifiers = explode("\\", $typeName);

    if(isset($identifiers[2]) === FALSE){
        return NULL;
    }

    $vendorIdentifier = array_shift($identifiers);

    $packageIdentifier = array_shift($identifiers);

    $package = new Package($vendorIdentifier, $packageIdentifier);

    return new PackageComponent($package, $identifiers, ".php");
}
