<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use Error;
use Netmosfera\Opal\Loaders\Loader;
use function spl_object_hash;

class OpalBuilder
{
    /** @var Int */ private $_started;
    /** @var PackageDirectory[] */ private $_directories;
    /** @var Closure[] */ private $_preprocessors;
    /** @var Loader */ private $_loader;

    public function __construct(Loader $loader){
        $this->_started = FALSE;
        $this->_directories = [];
        $this->_preprocessors = [];
        $this->_loader = $loader;
    }

    public function addPackage(
        String $vendorName,
        String $packageName,
        String $pathToPackage
    ){
        if($this->_started) throw new Error("Cannot modify Opal after it is started");
        $package = new Package($vendorName, $packageName);
        $directory = new PackageDirectory($package, $pathToPackage);
        $this->_directories[$directory->package->id] = $directory;
    }

    public function addPreprocessor(Closure $preprocessor){
        if($this->_started) throw new Error("Cannot modify Opal after it is started");
        $identifier = spl_object_hash($preprocessor);
        $this->_preprocessors[$identifier] = $preprocessor;
    }

    public function start(
        String $compileDirectory,
        Int $compileDirectoryPermissions = 0755,
        Int $compileFilePermissions = 0644
    ){
        if($this->_started) throw new Error("Opal is already running");
        $this->_started = TRUE;

        $this->_loader->start(
            $this->_directories, $this->_preprocessors,
            $compileDirectory, $compileDirectoryPermissions, $compileFilePermissions
        );
    }
}
