<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use Error;
use Netmosfera\Opal\Loaders\Loader;
use function spl_object_id;

class OpalBuilder
{
    /** @var Int */ private $_started;
    /** @var PackagePath[] */ private $_paths;
    /** @var Closure[] */ private $_preprocessors;
    /** @var Loader */ private $_loader;

    public function __construct(Loader $loader){
        $this->_started = FALSE;
        $this->_paths = [];
        $this->_preprocessors = [];
        $this->_loader = $loader;
    }

    public function addPackage(PackagePath $packagePath){
        assert(!$this->_started, new Error("Cannot modify Opal after it is started"));
        $key = $packagePath->package->id;
        assert(!isset($this->_paths[$key]), new Error("This package exists already"));
        $this->_paths[$key] = $packagePath;
    }

    public function addPreprocessor(Closure $preprocessor){
        assert(!$this->_started, new Error("Cannot modify Opal after it is started"));
        $identifier = spl_object_id($preprocessor);
        $this->_preprocessors[$identifier] = $preprocessor;
    }

    public function start(
        Path $compileDirectory,
        Int $compileDirectoryPermissions = 0755,
        Int $compileFilePermissions = 0644
    ){
        assert(!$this->_started, new Error("Opal is already running"));
        $this->_started = TRUE;

        $this->_loader->start(
            $this->_paths, $this->_preprocessors,
            $compileDirectory, $compileDirectoryPermissions, $compileFilePermissions
        );
    }
}
