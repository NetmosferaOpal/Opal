<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

use Closure;
use Netmosfera\Opal\Path;
use const DIRECTORY_SEPARATOR as DS;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;
use function spl_autoload_register;

class StaticLoader implements Loader
{
    /** @var Bool */ private $_started;
    /** @var Closure|NULL */ private $_autoloader;

    public function __construct(){
        $this->_started = FALSE;
        $this->_autoloader = NULL;
    }

    public function start(
        Array $directories,
        Array $preprocessors,
        Path $compileDirectory,
        Int $compileDirectoryPermissions,
        Int $compileFilePermissions
    ){
        assert(!$this->_started);
        $this->_started = TRUE;

        $this->_autoloader = function(String $typeName) use(
            $directories, $compileDirectory
        ){
            $component = componentFromTypeName($typeName);
            if($component === NULL){
                return NULL;
            }

            $directory = $directories[$component->package->id] ?? NULL;
            if($directory === NULL){
                return NULL;
            }

            (static function($__OPAL_FILE__){
                require $__OPAL_FILE__;
            })($compileDirectory->path . $component->absolutePath);
        };

        spl_autoload_register($this->_autoloader, TRUE, FALSE);

        (static function($__OPAL_FILE__){
            require $__OPAL_FILE__;
        })($compileDirectory->path . DS . "static-inclusions.php");
    }
}
