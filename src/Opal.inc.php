<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Error;
use function defined;

function Opal(){
    static $instance;

    if($instance !== NULL){
        return $instance;
    }

    if(!defined("NETMOSFERA_OPAL_LOADER_STATIC")){
        throw new Error(
            "The `Bool` constant `\NETMOSFERA_OPAL_LOADER_STATIC` is not defined"
        );
    }

    $instance = new Loader(NETMOSFERA_OPAL_LOADER_STATIC);

    return $instance;
}
