<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Loaders;

interface Loader
{
    public function start();
    public function end();
}
