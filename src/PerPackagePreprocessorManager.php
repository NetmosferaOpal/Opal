<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;

class PerPackagePreprocessorManager
{
    private $_packages;

    private $_packageData;

    private $_actualPreprocessor;

    private $_preprocessor;

    public function __construct(Closure $actualPreprocessor){
        $this->_packages = [];

        $this->_packageData = [];

        $this->_actualPreprocessor = $actualPreprocessor;

        $this->_preprocessor = function(PackageComponent $component, array $nodes): array{
            if(isset($this->_packages[$component->package->id]) === FALSE){
                return $nodes;
            }
            return ($this->_actualPreprocessor)($component, $nodes);
        };
    }

    public function enablePreprocessorForPackage(
        String $vendorIdentifier,
        String $packageIdentifier,
        $data = NULL
    ){
        $package = new Package($vendorIdentifier, $packageIdentifier);
        $this->_packages[$package->id] = $package;
        $this->_packageData[$package->id] = $data;
    }

    public function dataOfPackage(Package $package){
        return $this->_packageData[$package->id];
    }

    /**
     * Returns the preprocessor that filters per package.
     *
     * @return          Closure
     */
    public function filteringPreprocessor(): Closure{
        return $this->_preprocessor;
    }
}
