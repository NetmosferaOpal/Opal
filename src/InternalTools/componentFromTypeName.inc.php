<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\Component;
use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;

/**
 * Creates a {@see PackageComponent} from a type name.
 *
 * Returns a {@see PackageComponent} in the quickest way possible, without performing any
 * validation (unless assertions are enabled). Returns `NULL` if the provided type is not
 * in the format `VendorName\PackageName\*`.
 */
function componentFromTypeName(String $typeName): ?PackageComponent{
    $pieces = explode("\\", $typeName);

    if(isset($pieces[2]) === FALSE){
        return NULL;
    }

    $vendorIdentifier = new Identifier($pieces[0]);
    $packageIdentifier = new Identifier($pieces[1]);

    $identifiers = [];
    for($max = count($pieces), $offset = 2; $offset < $max; $offset++){
        $identifiers[] = new Identifier($pieces[$offset]);
    }

    $package = new Package($vendorIdentifier, $packageIdentifier);

    return new PackageComponent($package, new Component($identifiers), ".php");
}
