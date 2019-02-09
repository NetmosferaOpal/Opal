<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

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
     * encoding of the text is not relevant as long the path is accessible, however, it is
     * ASCII-based and it is trimmed off of any directory-separator suffix (`/` and `\`).
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
        $path = rtrim($path, "\\/");
        $this->package = $package;
        $this->path = $path;
        $this->pathLength = strlen($path);
    }
}
