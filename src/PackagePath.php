<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

/**
 * Local directory in which a {@see Package} can be found.
 */
class PackagePath
{
    /** @var Package */ public $package;

    /** @var Path */ public $path;

    public function __construct(Package $package, Path $path){
        $this->package = $package;
        $this->path = $path;
    }
}
