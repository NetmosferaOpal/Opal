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

    /** @var PackageDirectory[] */
    private $_directories;

    /** @var Closure[] */
    private $_preprocessors;

    /** @var Closure */
    private $_sourceToTree;

    /** @var Closure */
    private $_treeToSource;

    /** @var Closure */
    private $_readDirectoryDeep;

    /** @var Closure */
    private $_readFile;

    /** @var Closure */
    private $_importFile;

    /** @var Closure */
    private $_writeAndImportNewFile;

    /** @var String|NULL */
    private $_cacheDirectory;

    /** @var Int|NULL */
    private $_cacheDirectoryMode;

    /** @var Int|NULL */
    private $_cacheFileMode;

    /** @var String */
    private $_cacheStaticInclusionsFilePath;

    public function __construct(
        Closure $sourceToTree,
        Closure $treeToSource,
        Closure $readDirectoryDeep,
        Closure $readFile,
        Closure $importFile,
        Closure $writeAndImportNewFile
    ){
        $this->_modifiable = TRUE;

        $this->_directories = [];

        $this->_preprocessors = [];

        $this->_sourceToTree = $sourceToTree;
        $this->_treeToSource = $treeToSource;
        $this->_readDirectoryDeep = $readDirectoryDeep;
        $this->_readFile = $readFile;
        $this->_importFile = $importFile;
        $this->_writeAndImportNewFile = $writeAndImportNewFile;

        $this->_cacheDirectory = NULL;
        $this->_cacheDirectoryMode = NULL;
        $this->_cacheFileMode = NULL;
        $this->_cacheStaticInclusionsFilePath = "/static-inclusions.php";
    }

    private function _ensureModifiable(){
        if($this->_modifiable === FALSE){
            throw new Error("The loader is already running");
        }
    }

    public function addPackage(
        String $vendorName,
        String $packageName,
        String $directoryPath
    ){
        $package = new Package($vendorName, $packageName);
        $directory = new PackageDirectory($package, $directoryPath);
        $this->_ensureModifiable();
        $this->_directories[$directory->package->id] = $directory;
    }

    public function addPreprocessor(Closure $preprocessor){
        $identifier = spl_object_hash($preprocessor);
        $this->_preprocessors[$identifier] = $preprocessor;
    }

    /**
     * Enables the loader in static mode.
     *
     * This is the zero-overhead mode. All resources are required straight out the
     * provided cache directory, and no attempt will be made to preprocess unavailable
     * ones. This is the preferred mode for production.
     *
     * Note that this is comparable in purpose and speed to _Composer_'s optimized
     * class-maps.
     *
     * @param       String $cacheDirectory
     */
    public function beginStatic(String $cacheDirectory){
        $this->_ensureModifiable();
        $this->_modifiable = FALSE;

        $this->_cacheDirectory = $cacheDirectory;

        spl_autoload_register(function(String $typeName){
            $component = componentFromTypeName($typeName);
            $directory = $this->_directories[$component->package->id] ?? NULL;
            if($directory === NULL){ return NULL; }
            $file = $this->_cacheDirectory . $component->absolutePath;
            ($this->_importFile)($file);
        }, TRUE, FALSE);

        ($this->_importFile)($cacheDirectory . $this->_cacheStaticInclusionsFilePath);
    }

    /**
     * Enables the loader in "live" mode.
     *
     * All files are looked up and preprocessed at every request, thus guaranteeing that
     * the result is produced by the latest available version of each PHP file. However,
     * doing this is also very slow, therefore this mode should be used only during
     * development.
     *
     * @param       String $cacheDirectory
     * @param       Int $cacheDirectoryMode
     * @param       Int $cacheFileMode
     */
    public function beginDynamic(
        String $cacheDirectory,
        Int $cacheDirectoryMode = 0755,
        Int $cacheFileMode = 0644
    ){
        $this->_ensureModifiable();
        $this->_modifiable = FALSE;

        $this->_cacheDirectory = $cacheDirectory;
        $this->_cacheDirectoryMode = $cacheDirectoryMode;
        $this->_cacheFileMode = $cacheFileMode;

        spl_autoload_register(function(String $typeName){
            $component = componentFromTypeName($typeName);
            if($component === NULL){ return NULL; }
            $directory = $this->_directories[$component->package->id] ?? NULL;
            if($directory === NULL){ return NULL; }
            $this->_preprocessComponent($directory, $component, TRUE);
        }, TRUE, FALSE);

        $this->_preprocessStaticallyLoadedComponents(TRUE);
    }

    /**
     * Preprocess and caches all files in the provided directories.
     *
     * This function is meant to be used by install scripts. After this is executed, it is
     * possible to enable the loader in static mode ({@see self::beginStatic()}) which
     * will offer the best performances.
     *
     * @param       String $cacheDirectory
     * @param       Int $cacheDirectoryMode
     * @param       Int $cacheFileMode
     */
    public function install(
        String $cacheDirectory,
        Int $cacheDirectoryMode = 0755,
        Int $cacheFileMode = 0644
    ){
        $this->_ensureModifiable();
        $this->_modifiable = FALSE;

        $this->_cacheDirectory = $cacheDirectory;
        $this->_cacheDirectoryMode = $cacheDirectoryMode;
        $this->_cacheFileMode = $cacheFileMode;

        foreach($this->_directories as $directory){
            foreach(($this->_readDirectoryDeep)($directory->path) as $file){
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
            foreach(($this->_readDirectoryDeep)($directory->path) as $file){
                $component = componentFromActualFile($directory, $file);
                if($component !== NULL && $component->extension === ".inc.php"){
                    $this->_preprocessComponent($directory, $component, $doImportThem);
                    $components[] = $component;
                }
            }
        }

        $staticInclusionsSource = "<?php\n\n";
        $staticInclusionsSource .= "// Generated by netmosfera/opal.\n";
        $staticInclusionsSource .= "// Do not edit this file manually!\n ";
        $staticInclusionsSource .= "\n";

        foreach($components as $component){
            $fileString = var_export($component->absolutePath, TRUE);
            $staticInclusionsSource .= "require __DIR__ . " . $fileString . ";\n";
        }

        $destinationFile = $this->_cacheDirectory . $this->_cacheStaticInclusionsFilePath;

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

        if($source === NULL){ throw new Error("Unable to read $originFile"); }

        if($this->_preprocessors !== []){
            $nodes = ($this->_sourceToTree)($source);

            foreach($this->_preprocessors as $preprocessor){
                $nodes = $preprocessor($component, $nodes);
            }

            $source = ($this->_treeToSource)($nodes);
        }

        $destinationFile = $this->_cacheDirectory . $component->absolutePath;

        $written = ($this->_writeAndImportNewFile)(
            $destinationFile,
            $this->_cacheDirectoryMode,
            $this->_cacheFileMode,
            $source,
            $doImportIt
        );

        if($written === FALSE){ throw new Error("Unable to write $destinationFile"); }
    }
}
