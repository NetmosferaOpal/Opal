<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use function strlen;

/**
 * A path name in the local filesystem.
 */
class Path
{
    /** @var String */ public $string;

    /** @var Int */ public $length;

    public function __construct(String $path){
        if(preg_match('@^   ([a-zA-Z]:)?   (?:[/\\\\][^/\\\\]+)*   $@xsD', $path) !== 1){
            throw new PathException();
        }

        $this->string = $path;
        $this->length = strlen($path);
    }

    public function isIn(Path $path): Bool{
        $length = $path->length;
        $separator = $this->string[$length] ?? NULL;
        return
            ($separator === "/" || $separator === "\\") &&
            substr($this->string, 0, $length) === $path->string;
    }

    public function __toString(): String{
        return $this->string;
    }
}
