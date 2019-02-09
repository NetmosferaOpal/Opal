<?php declare(strict_types = 1);

namespace Netmosfera\Opal;
use function Netmosfera\Opal\InternalTools\isValidIdentifier;

/**
 * Identifier for a package; defines the vendor name and the package's own name.
 */
class Package
{
    /**
     * @var         String
     * The vendor's name.
     */
    public $vendorIdentifier;

    /**
     * @var         String
     * The package's own name.
     */
    public $packageIdentifier;

    /**
     * @var         String
     * A string identifier for this package; used to uniquely identify this package.
     */
    public $id;

    /**
     * @param       String $vendorIdentifier
     * See {@see self::$vendorIdentifier}.
     *
     * @param       String $packageIdentifier
     * See {@see self::$packageIdentifier}.
     */
    public function __construct(String $vendorIdentifier, String $packageIdentifier){
        // Intentionally checked with assert(); this way the overhead
        // is reduced to the bare minimum in production.
        assert(isValidIdentifier($vendorIdentifier));
        assert(isValidIdentifier($packageIdentifier));

        $this->vendorIdentifier = $vendorIdentifier;
        $this->packageIdentifier = $packageIdentifier;
        $this->id = $vendorIdentifier . ";" . $packageIdentifier;
    }
}