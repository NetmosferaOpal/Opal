<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

use Closure;
use Error;
use Netmosfera\Opal\PackageDirectory;
use const DIRECTORY_SEPARATOR as DS;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;
use function spl_autoload_register;
use function spl_autoload_unregister;

class StaticLoader implements Loader
{
    private const NOT_STARTED = 0;
    private const STARTED = 1;
    private const ENDED = 2;

    /** @var Int */ private $_state;
    /** @var PackageDirectory[] */ private $_directories;
    /** @var String */ private $_compileDirectory;
    /** @var Closure|NULL */ private $_autoloader;

    public function __construct(Array $directories, String $compileDirectory){
        $this->_state = self::NOT_STARTED;
        $this->_directories = $directories;
        $this->_compileDirectory = $compileDirectory;
        $this->_autoloader = NULL;
    }

    public function start(){
        if($this->_state !== self::NOT_STARTED) throw new Error("Not NOT_STARTED");
        $this->_state = self::STARTED;

        $this->_autoloader = function(String $typeName){
            $component = componentFromTypeName($typeName);
            if($component === NULL) return NULL;
            $directory = $this->_directories[$component->package->id] ?? NULL;
            if($directory === NULL) return NULL;
            require $this->_compileDirectory . $component->absolutePath; // @TODO clean scope
        };

        spl_autoload_register($this->_autoloader, TRUE, FALSE);

        require $this->_compileDirectory . DS . "static-inclusions.php";
    }

    public function end(){
        if($this->_state !== self::STARTED) throw new Error("Not STARTED");
        spl_autoload_unregister($this->_autoloader);
        $this->_state = self::ENDED;
    }
}
