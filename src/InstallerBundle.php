<?php

namespace Webberdoo\InstallerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class InstallerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
