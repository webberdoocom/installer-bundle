<?php

namespace Webberdoo\InstallerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class InstallerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set parameters for services to use
        $container->setParameter('installer.entities', $config['entities']);
        $container->setParameter('installer.admin_user', $config['admin_user']);
        $container->setParameter('installer.database', $config['database']);
        $container->setParameter('installer.requirements', $config['requirements']);
        $container->setParameter('installer.app_config', $config['app_config']);
        $container->setParameter('installer.install_marker_path', $config['install_marker_path']);
        $container->setParameter('installer.route_prefix', $config['route_prefix']);

        // Load services
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'installer';
    }
}
