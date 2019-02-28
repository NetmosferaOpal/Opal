<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Error;
use function strlen;

class Path
{
    /** @var String */ public $path;

    /** @var Int */ public $length;

    public function __construct(String $path){
        if(preg_match('@^   ([a-zA-Z]:)?   (?:[/\\\\][^/\\\\]+)*   $@xsD', $path) !== 1){
            throw new Error();
        }

        $this->path = $path;
        $this->length = strlen($path);
    }

    public function isIn(Path $path): Bool{
        $length = $path->length;
        return
            substr($this->path, 0, $length) === $path->path &&
            ($this->path[$length] === "/" || $this->path[$length] === "\\");  // @TODO is or is in()
    }

    public function __toString(): String{
        return $this->path;
    }
}
