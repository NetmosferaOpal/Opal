<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use Error;
use function Netmosfera\Opal\InternalTools\componentFromActualFile;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;
use function spl_autoload_register;
use function spl_object_hash;
use function var_export;

class Loader
{
    /** @var Bool */
    private $_modifiable;

    /** @var Bool */
    private $_staticMode;

    /** @var PackageDirectory[] */
    private $_directories;

    /** @var Closure[] */
    private $_preprocessors;

    /** @var Closure */
    private $_sourceToNodes;

    /** @var Closure */
    private $_nodesToSource;

    /** @var Closure */
    private $_readDirectory;

    /** @var Closure */
    private $_readFile;

    /** @var Closure */
    private $_importFile;

    /** @var Closure */
    private $_writeAndImportFile;

    /** @var String|NULL */
    private $_compileDirectory;

    /** @var Int|NULL */
    private $_compileDirectoryPermissions;

    /** @var Int|NULL */
    private $_compileFilePermissions;

    /** @var String */
    private $_compileStaticImportsPath;

    public function __construct(
        Bool $staticMode,
        Closure $sourceToNodes,
        Closure $nodesToSource,
        Closure $readDirectory,
        Closure $readFile,
        Closure $importFile,
        Closure $writeAndImportFile
    ){
        $this->_modifiable = TRUE;

        $this->_staticMode = $staticMode;

        $this->_directories = [];

        $this->_preprocessors = [];

        $this->_sourceToNodes = $sourceToNodes;
        $this->_nodesToSource = $nodesToSource;
        $this->_readDirectory = $readDirectory;
        $this->_readFile = $readFile;
        $this->_importFile = $importFile;
        $this->_writeAndImportFile = $writeAndImportFile;

        $this->_compileDirectory = NULL;
        $this->_compileDirectoryPermissions = NULL;
        $this->_compileFilePermissions = NULL;
        $this->_compileStaticImportsPath = "/static-inclusions.php";
    }

    private function _ensureModifiable(){
        if(!$this->_modifiable) throw new Error("The loader is already running");
    }

    public function addPackage(
        String $vendorName,
        String $packageName,
        String $pathToPackage
    ){
        $package = new Package($vendorName, $packageName);
        $directory = new PackageDirectory($package, $pathToPackage);
        $this->_ensureModifiable();
        $this->_directories[$directory->package->id] = $directory;
    }

    public function addPreprocessor(Closure $preprocessor){
        $identifier = spl_object_hash($preprocessor);
        $this->_preprocessors[$identifier] = $preprocessor;
    }

    public function begin(
        String $compileDirectory,
        Int $compileDirectoryPermissions = 0755,
        Int $compileFilePermissions = 0644
    ){
        $this->_ensureModifiable();
        $this->_modifiable = FALSE;

        $this->_compileDirectory = $compileDirectory;
        $this->_compileDirectoryPermissions = $compileDirectoryPermissions;
        $this->_compileFilePermissions = $compileFilePermissions;

        spl_autoload_register(function(String $typeName){
            $component = componentFromTypeName($typeName);
            if($component === NULL) return NULL;
            $directory = $this->_directories[$component->package->id] ?? NULL;
            if($directory === NULL) return NULL;

            if($this->_staticMode){
                $file = $this->_compileDirectory . $component->absolutePath;
                ($this->_importFile)($file);
            }else{
                $this->_preprocessComponent($directory, $component, TRUE);
            }
        }, TRUE, FALSE);

        if($this->_staticMode){
            ($this->_importFile)($compileDirectory . $this->_compileStaticImportsPath);
        }else{
            $this->_preprocessStaticallyLoadedComponents(TRUE);
        }
    }

    /**
     * Preprocess and caches all files in the provided directories.
     *
     * This function is meant to be used by install scripts. After this is executed, it is
     * possible to enable the loader in static mode ({@see self::beginStatic()}) which
     * will offer the best performances.
     *
     * @param       String $compileDirectory
     * @param       Int $compileDirectoryPermissions
     * @param       Int $compileFilePermissions
     */
    public function install(
        String $compileDirectory,
        Int $compileDirectoryPermissions = 0755,
        Int $compileFilePermissions = 0644
    ){
        $this->_ensureModifiable();
        $this->_modifiable = FALSE;

        $this->_compileDirectory = $compileDirectory;
        $this->_compileDirectoryPermissions = $compileDirectoryPermissions;
        $this->_compileFilePermissions = $compileFilePermissions;

        foreach($this->_directories as $directory){
            foreach(($this->_readDirectory)($directory->path) as $file){
                $component = componentFromActualFile($directory, $file);
                if($component !== NULL && $component->extension === ".php"){
                    $this->_preprocessComponent($directory, $component, FALSE);
                }
            }
        }

        $this->_preprocessStaticallyLoadedComponents(FALSE);
    }

    private function _preprocessStaticallyLoadedComponents(Bool $doImportThem){
        $components = [];
        /** @var PackageComponent[] $components */

        foreach($this->_directories as $directory){
            foreach(($this->_readDirectory)($directory->path) as $file){
                $component = componentFromActualFile($directory, $file);
                if($component !== NULL && $component->extension === ".inc.php"){
                    $this->_preprocessComponent($directory, $component, $doImportThem);
                    $components[] = $component;
                }
            }
        }

        $staticInclusionsSource = "<?php\n\n";
        $staticInclusionsSource .= "// Generated by opal/opal.\n";
        $staticInclusionsSource .= "// Do not edit this file manually!\n ";
        $staticInclusionsSource .= "\n";

        foreach($components as $component){
            $fileString = var_export($component->absolutePath, TRUE);
            $staticInclusionsSource .= "require __DIR__ . " . $fileString . ";\n";
        }

        $destinationFile = $this->_compileDirectory . $this->_compileStaticImportsPath;

        file_put_contents($destinationFile, $staticInclusionsSource);
    }

    private function _preprocessComponent(
        PackageDirectory $directory,
        PackageComponent $component,
        Bool $doImportIt
    ){
        assert(isset($this->_directories[$directory->package->id]));

        $originFile = $directory->path . $component->relativeToPackagePath;

        $source = ($this->_readFile)($originFile);
        if($source === NULL) throw new Error("Unable to read $originFile");
        if($this->_preprocessors !== []){
            $nodes = ($this->_sourceToNodes)($source);
            foreach($this->_preprocessors as $preprocessor){
                $nodes = $preprocessor($component, $nodes);
            }
            $source = ($this->_nodesToSource)($nodes);
        }

        $destinationFile = $this->_compileDirectory . $component->absolutePath;

        $written = ($this->_writeAndImportFile)(
            $destinationFile,
            $this->_compileDirectoryPermissions,
            $this->_compileFilePermissions,
            $source,
            $doImportIt
        );

        if($written === FALSE) throw new Error("Unable to write $destinationFile");
    }
}
