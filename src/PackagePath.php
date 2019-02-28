<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use function Netmosfera\Opal\Files\isNormalizedPath;

/**
 * Local directory in which a {@see Package} can be found.
 */
class PackagePath
{
    /** @var Package */ public $package;

    /**
     * The directory in which this package resides, ASCII based. The encoding of the text
     * in the high bytes is not relevant as long the path is accessible.
     */
    /** @var String */ public $path;

    /**
     * Same as `strlen($this->path)`.
     */
    /** @var Int */ public $pathLength;

    public function __construct(Package $package, String $path){
        assert(isNormalizedPath($path));
        $this->package = $package;
        $this->path = $path;
        $this->pathLength = strlen($path);
    }
}
