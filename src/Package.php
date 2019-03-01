<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

/**
 * Identifier for a package; defines the vendor name and the package's own name.
 */
class Package
{
    /** @var Identifier */ public $vendor;

    /** @var Identifier */ public $name;

    /** @var String */ public $id;

    public function __construct(Identifier $vendor, Identifier $name){
        $this->vendor = $vendor;
        $this->name = $name;
        $this->id = $vendor->string . ";" . $name->string;
    }
}
