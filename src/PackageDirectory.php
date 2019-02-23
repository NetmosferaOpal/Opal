<?php declare(strict_types = 1);

namespace Netmosfera\Opal;
use function Netmosfera\Opal\InternalTools\isNormalizedPath;

/**
 * Local directory in which a {@see Package} can be found.
 */
class PackageDirectory
{
    /**
     * @var        Package
     * Identifier for the package.
     */
    public $package;

    /**
     * @var         String
     * The directory in which this package resides, to be intended as ASCII based. The
     * encoding of the text (the high bytes) is not relevant as long the path is
     * accessible.
     */
    public $path;

    /**
     * @var         Int
     * Same as `strlen($this->path)`.
     */
    public $pathLength;

    /**
     * @param       Package $package
     * See {@see self::$package}.
     *
     * @param       String $path
     * See {@see self::$path}.
     */
    public function __construct(Package $package, String $path){
        assert(isNormalizedPath($path));
        $this->package = $package;
        $this->path = $path;
        $this->pathLength = strlen($path);
    }
}
