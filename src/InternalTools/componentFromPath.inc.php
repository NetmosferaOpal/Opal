<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\IdentifierException;
use Netmosfera\Opal\Component;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;

/**
 * Creates a {@see PackageComponent} from a local file path.
 *
 * The directory's path in `$directory` is taken out of `$path`. If `$path` has a
 * different directory prefix, the function will throw an error. The remaining path is
 * split in file-names and each one is checked to be a valid PHP identifier. Anything that
 * appears after the first `.` in the last identifier is collected in
 * {@see PackageComponent::$extension}. If all file-names in the path are valid PHP
 * identifiers a {@see PackageComponent} object is returned, otherwise `NULL`.
 */
function componentFromPath(PackagePath $packagePath, Path $path): ?PackageComponent{
    assert($path->isIn($packagePath->path));

    $relativePath = substr($path->string, $packagePath->path->length);

    $pathPieces = preg_split("@[\\\\/]+@", $relativePath);
    // Remove the first because the relative path starts with a
    // directory separator, therefore the first is empty
    $firstPathPiece = array_shift($pathPieces);
    assert($firstPathPiece === "");

    $fileName = $pathPieces[count($pathPieces) - 1];
    $fileNamePieces = explode(".", $fileName, 2);

    $componentIdentifier = $fileNamePieces[0];
    $extensions = $fileNamePieces[1] ?? NULL;
    $extensions = $extensions === NULL ? "" : "." . $extensions;

    $pathPieces[count($pathPieces) - 1] = $componentIdentifier;

    $identifiers = [];
    foreach($pathPieces as $pathPiece){
        try{
            $identifiers[] = new Identifier($pathPiece);
        }catch(IdentifierException $e){
            // Possibly a valid file but since it contains pieces that won't make
            // valid PHP identifiers, we can only ignore it.
            return NULL;
        }
    }

    return new PackageComponent(
        $packagePath->package, new Component($identifiers), $extensions
    );
}
