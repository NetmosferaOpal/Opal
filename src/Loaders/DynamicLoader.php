<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

use Closure;
use Error;
use Netmosfera\Opal\Path;
use function Netmosfera\Opal\Files\emptyDirectory;
use function Netmosfera\Opal\Files\lockDirectory;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;
use function Netmosfera\Opal\InternalTools\preprocessComponent;
use function Netmosfera\Opal\InternalTools\preprocessStaticComponents;
use function spl_autoload_register;

class DynamicLoader implements Loader
{
    private const NOT_STARTED = 0;

    private const STARTED = 1;

    private const ENDED = 2;

    /** @var Int */ private $_state;

    /** @var Path|NULL */ private $_compileDirectory;

    /** @var Closure|NULL */ private $_autoloader;

    /** @var Resource|NULL */ private $_lock;

    public function __construct(){
        $this->_state = self::NOT_STARTED;
        $this->_compileDirectory = NULL;
        $this->_autoloader = NULL;
        $this->_lock = NULL;
    }

    public function start(
        Array $directories,
        Array $preprocessors,
        Path $compileDirectory,
        Int $compileDirectoryPermissions,
        Int $compileFilePermissions
    ){
        if($this->_state !== self::NOT_STARTED) throw new Error("Not NOT_STARTED");

        $this->_lock = lockDirectory($compileDirectory, $compileDirectoryPermissions); // @TODO this doesn't throw anymore

        $this->_state = self::STARTED;

        $this->_compileDirectory = $compileDirectory;

        $this->_autoloader = function(String $typeName) use(
            $directories, $preprocessors,
            $compileDirectoryPermissions, $compileFilePermissions
        ){
            $component = componentFromTypeName($typeName);
            if($component === NULL){
                return NULL;
            }

            $directory = $directories[$component->package->id] ?? NULL;
            if($directory === NULL){
                return NULL;
            }

            preprocessComponent(
                $directory, $component, $preprocessors, $this->_compileDirectory,
                TRUE, $compileDirectoryPermissions, $compileFilePermissions
            );
        };

        spl_autoload_register($this->_autoloader, TRUE, FALSE);

        preprocessStaticComponents(
            $directories, $preprocessors, $compileDirectory, TRUE,
            $compileDirectoryPermissions, $compileFilePermissions
        );
    }

    public function __destruct(){
        if($this->_state === self::STARTED){
            emptyDirectory($this->_compileDirectory);
            fclose($this->_lock);
            $this->_state = self::ENDED;
        }
    }
}
