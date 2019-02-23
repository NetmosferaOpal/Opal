<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

use Closure;
use Error;
use Netmosfera\Opal\PackageDirectory;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;
use function Netmosfera\Opal\InternalTools\dirEmpty;
use function Netmosfera\Opal\InternalTools\dirLock;
use function Netmosfera\Opal\InternalTools\preprocessComponent;
use function Netmosfera\Opal\InternalTools\preprocessStaticComponents;
use function spl_autoload_register;
use function spl_autoload_unregister;

class DynamicLoader implements Loader
{
    private const NOT_STARTED = 0;
    private const STARTED = 1;
    private const ENDED = 2;

    /** @var Int */ private $_state;
    /** @var PackageDirectory[] */ private $_directories;
    /** @var Closure[] */ private $_preprocessors;
    /** @var String */ private $_compileDirectory;
    /** @var Int|NULL */ private $_compileDirectoryPermissions;
    /** @var Int|NULL */ private $_compileFilePermissions;
    /** @var Closure|NULL */ private $_autoloader;
    /** @var Resource|NULL */ private $_lockHandle;

    public function __construct(
        Array $directories,
        Array $preprocessors,
        String $compileDirectory,
        ?Int $compileDirectoryPermissions,
        ?Int $compileFilePermissions
    ){
        $this->_state = self::NOT_STARTED;
        $this->_directories = $directories;
        $this->_preprocessors = $preprocessors;
        $this->_compileDirectory = $compileDirectory;
        $this->_compileDirectoryPermissions = $compileDirectoryPermissions;
        $this->_compileFilePermissions = $compileFilePermissions;
        $this->_autoloader = NULL;
        $this->_lockHandle = NULL;
    }

    public function start(){
        if($this->_state !== self::NOT_STARTED) throw new Error("Not NOT_STARTED");

        $this->_lockHandle = dirLock($this->_compileDirectory);

        $this->_state = self::STARTED;

        $this->_autoloader = function(String $typeName){
            $component = componentFromTypeName($typeName);
            if($component === NULL) return NULL;
            $directory = $this->_directories[$component->package->id] ?? NULL;
            if($directory === NULL) return NULL;
            preprocessComponent(
                $directory, $component, $this->_preprocessors, $this->_compileDirectory,
                TRUE, $this->_compileDirectoryPermissions, $this->_compileFilePermissions
            );
        };

        spl_autoload_register($this->_autoloader, TRUE, FALSE);

        preprocessStaticComponents(
            $this->_directories, $this->_preprocessors, $this->_compileDirectory, TRUE,
            $this->_compileDirectoryPermissions, $this->_compileFilePermissions
        );
    }

    public function end(){
        if($this->_state !== self::STARTED) throw new Error("Not STARTED");
        spl_autoload_unregister($this->_autoloader);
        dirEmpty($this->_compileDirectory);
        fclose($this->_lockHandle);
        $this->_state = self::ENDED;
    }

    public function __destruct(){
        if($this->_state === self::STARTED) $this->end();
    }
}
