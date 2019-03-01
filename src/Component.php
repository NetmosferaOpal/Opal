<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use TypeError;

/**
 * Name of a component in a package.
 *
 * Represents a fully qualified name excluded of the `VendorName\PackageName\` prefix.
 */
class Component
{
    /** @var Identifier[] */ public $array;

    public function __construct(Array $identifiers){
        foreach($identifiers as $identifier){
            if(!$identifier instanceof Identifier){
                throw new TypeError();
            }
        }

        if(count($identifiers) === 0){
            throw new ComponentException("count(\$identifiers) must be >= 1");
        }

        $this->array = $identifiers;
    }

    public function path(): String{
        $path = "";
        foreach($this->array as $identifier){
            $path .= "/" . $identifier->string;
        }
        return $path;
    }
}
