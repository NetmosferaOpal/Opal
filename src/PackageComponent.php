<?php declare(strict_types = 1);

namespace Netmosfera\Opal;
use function Netmosfera\Opal\InternalTools\isValidIdentifiers;

/**
 * Component in a PHP Package.
 *
 * This class keeps the path to a file in a PHP package within the package itself; for
 * example `StarkIndustries/IronMan/Suit.php`.
 *
 * Objects of this class can also be used to reference actual PHP global symbols such as
 * classes, since PSR-4 requires file names to match PHP entities' namespace.
 *
 * All the identifiers are valid ASCII - other than being valid PHP identifiers. This
 * requirement is not enforced by any recognized recommendation, but it is essentially
 * mandatory for full portability across different filesystems.
 */
class PackageComponent
{
    /**
     * @var         Package
     * The package to which the component belongs.
     */
    public $package;

    /**
     * @var         String[]
     * The identifiers used to locate the component within the package.
     */
    public $identifiers;

    /**
     * @var         String
     * The extension of the file; this is usually ".php". For example files that cannot be
     * autoloaded are distinguished by files that can be autoloaded using a different
     * extension. This value includes all extensions, and it's always prefixed by a `.`
     * unless the file had no extension - in that case this property is set to a empty
     * string.
     */
    public $extension;

    /**
     * @var         String
     * For example, if the component is `StarkIndustries\IronMan\Weapons\Minigun.php`
     * this property will be set to "/Weapons/Minigun.php".
     */
    public $relativeToPackagePath;

    /**
     * @var         String
     * For example, if the component is `StarkIndustries\IronMan\Weapons\Minigun.php`
     * this property will be set to "StarkIndustries/IronMan/Weapons/Minigun.php".
     */
    public $absolutePath;

    /**
     * @param       Package $package
     * See {@see self::$package}.
     *
     * @param       String[] $identifiers
     * See {@see self::$identifiers}.
     *
     * @param       String $extension
     * See {@see self::$extension}.
     */
    public function __construct(
        Package $package,
        array $identifiers,
        String $extension = ".php"
    ){
        // Intentionally checked with assert(); this way the overhead
        // is reduced to the bare minimum in production.
        assert(isValidIdentifiers($identifiers));
        assert($extension === "" || substr($extension, 0, 1) === ".");

        $this->package = $package;
        $this->identifiers = $identifiers;
        $this->extension = $extension;

        $prefix = "/" . $package->vendorIdentifier . "/" . $package->packageIdentifier;

        $stringifiedIdentifiers = "/" . implode("/", $identifiers);

        $this->relativeToPackagePath = $stringifiedIdentifiers . $extension;

        $this->absolutePath = $prefix . $this->relativeToPackagePath;
    }

    public function __toString(): String{
        return $this->absolutePath;
    }
}