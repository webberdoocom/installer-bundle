<?php

namespace Webberdoo\InstallerBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class InstallerBundle extends AbstractBundle
{
    public function loadRoutes(ContainerConfigurator $configurator): void
    {
        $configurator->import(__DIR__ . '/Controller/', 'attribute');
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
