<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function Netmosfera\Opal\InternalTools\File\fileContents;
use function Netmosfera\Opal\InternalTools\File\isAbsolutePath;
use function Netmosfera\Opal\InternalTools\File\requireNewPHPFile;

function loader(){
    static $instance;

    if($instance !== NULL){
        return $instance;
    }

    $parser = NULL;
    $sourceToTree = function(String $source) use(&$parser): array{
        if($parser === NULL){
            $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        }
        return $parser->parse($source);
    };

    $stringifier = NULL;
    $treeToSource = function(array $tree) use(&$stringifier): String{
        if($stringifier === NULL){
            $stringifier = new Standard();
        }
        return $stringifier->prettyPrintFile($tree);
    };

    $readDirectoryDeep = function(String $directory){
        // directory iterator removes the last "/" and adds its own separator when
        // concatenating the new paths
        $directory = rtrim($directory, "\\/");
        $flags = RecursiveDirectoryIterator::SKIP_DOTS;
        $directoryIterator = new RecursiveDirectoryIterator($directory, $flags);
        $flattenedDirectoryIterator = new RecursiveIteratorIterator($directoryIterator);
        foreach($flattenedDirectoryIterator as $fileInfo){
            /** @var SplFileInfo $fileInfo */
            yield $fileInfo->getPathname();
        }
    };

    $readFile = function(String $path){
        return fileContents($path, 5.0, 0.0);
    };

    $importFile = function(String $__OPAL_FILE__){
        assert(isAbsolutePath($__OPAL_FILE__));
        require $__OPAL_FILE__; // only variable visible into the required file
    };

    $writeAndImportNewFile = function($file, $dirMode, $fileMode, $source, $doImportIt){
        return requireNewPHPFile($file, $source, $dirMode, 5.0, 0.0, $doImportIt);
    };

    $instance = new Loader(
        $sourceToTree,
        $treeToSource,
        $readDirectoryDeep,
        $readFile,
        $importFile,
        $writeAndImportNewFile
    );

    return $instance;
}
