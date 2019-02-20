<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use Error;
use Netmosfera\Opal\Loaders\DynamicLoader;
use Netmosfera\Opal\Loaders\Loader;
use Netmosfera\Opal\Loaders\StaticLoader;
use function spl_object_hash;

class OpalBuilder
{
    private const NOT_STARTED = 0;
    private const STARTED = 1;
    private const ENDED = 2;

    private $_state;
    /** @var Bool */ private $_static;
    /** @var PackageDirectory[] */ private $_directories;
    /** @var Closure[] */ private $_preprocessors;
    /** @var Loader */ private $_loader;

    public function __construct(Bool $static){
        $this->_state = self::NOT_STARTED;
        $this->_static = $static;
        $this->_directories = [];
        $this->_preprocessors = [];
        $this->_compileDirectory = NULL;
        $this->_compileDirectoryPermissions = NULL;
        $this->_compileFilePermissions = NULL;
    }

    public function addPackage(
        String $vendorName,
        String $packageName,
        String $pathToPackage
    ){
        if($this->_state !== self::NOT_STARTED) throw new Error();
        $package = new Package($vendorName, $packageName);
        $directory = new PackageDirectory($package, $pathToPackage);
        $this->_directories[$directory->package->id] = $directory;
    }

    public function addPreprocessor(Closure $preprocessor){
        if($this->_state !== self::NOT_STARTED) throw new Error();
        $identifier = spl_object_hash($preprocessor);
        $this->_preprocessors[$identifier] = $preprocessor;
    }

    public function start(
        String $compileDirectory,
        Int $compileDirectoryPermissions = 0755,
        Int $compileFilePermissions = 0644
    ){
        if($this->_state !== self::NOT_STARTED) throw new Error();

        if($this->_static){
            $this->_loader = new StaticLoader($this->_directories, $compileDirectory);
        }else{
            $this->_loader = new DynamicLoader(
                $this->_directories, $this->_preprocessors, $compileDirectory,
                $compileDirectoryPermissions, $compileFilePermissions
            );
        }

        $this->_loader->start();

        $this->_state = self::STARTED;
    }

    public function end(){
        if($this->_state !== self::STARTED) throw new Error();
        $this->_loader->end();
        $this->_state = self::ENDED;
    }

    public function __destruct(){
        if($this->_state === self::STARTED) $this->end();
    }
}
