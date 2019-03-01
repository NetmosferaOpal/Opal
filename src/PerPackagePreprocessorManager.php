<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;

class PerPackagePreprocessorManager
{
    /** @var Package[] */ private $_packages;

    /** @var Mixed[] */ private $_packageData;

    /** @var Closure */ private $_actualPreprocessor;

    /** @var Closure */ private $_filteringPreprocessor;

    public function __construct(Closure $actualPreprocessor){
        $this->_packages = [];

        $this->_packageData = [];

        $this->_actualPreprocessor = $actualPreprocessor;

        $this->_filteringPreprocessor = function(
            PackageComponent $component, Array $nodes
        ): array{
            if(isset($this->_packages[$component->package->id]) === FALSE){
                return $nodes;
            }
            return ($this->_actualPreprocessor)($component, $nodes);
        };
    }

    /**
     * Enables the preprocessor for the specified package.
     */
    public function enablePreprocessorForPackage(Package $package, $miscData = NULL){
        $this->_packages[$package->id] = $package;
        $this->_packageData[$package->id] = $miscData;
    }

    /**
     * Returns the data that was associated to the package, or `NULL` if none.
     */
    public function dataOfPackage(Package $package){
        return $this->_packageData[$package->id] ?? NULL;
    }

    /**
     * Returns the preprocessor that will filter by package.
     */
    public function filteringPreprocessor(): Closure{
        return $this->_filteringPreprocessor;
    }
}
