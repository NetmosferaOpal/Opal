<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

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
    /** @var Package */ public $package;

    /** @var Component */ public $name;

    /**
     * The extension of the file; this is usually ".php". For example files that cannot be
     * autoloaded are distinguished by files that can be autoloaded using a different
     * extension. This value includes all extensions, and it's always prefixed by a `.`
     * unless the file had no extension - in that case this property is set to a empty
     * string.
     */
    /** @var String */ public $extensions;

    /**
     * For example, if the component is `StarkIndustries/IronMan/Weapons/Minigun.php`
     * this property will be set to "/Weapons/Minigun.php".
     */
    /** @var String */ public $relativeToPackagePath;

    /**
     * For example, if the component is `StarkIndustries/IronMan/Weapons/Minigun.php`
     * this property will be set to "/StarkIndustries/IronMan/Weapons/Minigun.php".
     */
    /** @var String */ public $absolutePath;

    public function __construct(
        Package $package,
        Component $name,
        String $extensions = ".php"
    ){
        assert($extensions === "" || substr($extensions, 0, 1) === ".");

        $this->package = $package;
        $this->name = $name;
        $this->extensions = $extensions;

        $this->relativeToPackagePath .= $name->path() . $extensions;
        $this->absolutePath = "/" . $package->vendor . "/" . $package->name;
        $this->absolutePath .= $this->relativeToPackagePath;
    }
}
