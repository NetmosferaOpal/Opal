<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use Error;
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
        String $compileDirectory,
        Int $compileDirectoryPermissions,
        Int $compileFilePermissions
    ){
        if($this->_started){
            throw new Error("Already started");
        }
        $this->_started = TRUE;

        $this->_autoloader = function(String $typeName) use(
            $directories, $compileDirectory
        ){
            $component = componentFromTypeName($typeName);
            if($component === NULL) return NULL;
            $directory = $directories[$component->package->id] ?? NULL;
            if($directory === NULL) return NULL;
            $file = $compileDirectory . $component->absolutePath;
            (function($__OPAL_FILE__){ require $__OPAL_FILE__; })($file);
        };

        spl_autoload_register($this->_autoloader, TRUE, FALSE);

        $staticInclusionsFile = $compileDirectory . DS . "static-inclusions.php";

        (function($__OPAL_FILE__){ require $__OPAL_FILE__; })($staticInclusionsFile);
    }
}
