<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

function fileRequire($__OPAL_FILE__){
    assert(isAbsolutePath($__OPAL_FILE__);
    require $__OPAL_FILE__;
}
