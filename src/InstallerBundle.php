<?php

namespace Webberdoo\InstallerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Webberdoo\InstallerBundle\DependencyInjection\InstallerExtension;

class InstallerBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Load services configuration
        $container->import('../Resources/config/services.yaml');
    }

    public function loadRoutes(ContainerConfigurator $configurator): void
    {
        $configurator->import(__DIR__ . '/Controller/', 'attribute');
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?InstallerExtension
    {
        return new InstallerExtension();
    }
}
