<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

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
    /** @var Path */ public $path;

    public function __construct(Package $package, Path $path){
        $this->package = $package;
        $this->path = $path;
    }
}
