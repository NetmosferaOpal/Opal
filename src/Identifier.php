<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

/**
 * A PHP identifier (i.e. a class or a function name).
 */
class Identifier
{
    /** @var String */ public $string;

    public function __construct(String $string){
        if(preg_match("@^[a-zA-Z_][a-zA-Z_0-9]*$@", $string) !== 1){
            throw new IdentifierException();
        }

        $this->string = $string;
    }

    public function __toString(): String{
        return $this->string;
    }
}
