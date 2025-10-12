<?php

namespace Webberdoo\InstallerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('installer');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // Entities to install
                ->arrayNode('entities')
                    ->info('List of entity classes to create tables for')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                
                // Admin user configuration
                ->arrayNode('admin_user')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_class')
                            ->info('The User entity class')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('email_field')
                            ->info('The email property name')
                            ->defaultValue('email')
                        ->end()
                        ->scalarNode('password_field')
                            ->info('The password property name')
                            ->defaultValue('password')
                        ->end()
                        ->scalarNode('roles_field')
                            ->info('The roles property name')
                            ->defaultValue('roles')
                        ->end()
                        ->scalarNode('full_name_field')
                            ->info('The full name property name')
                            ->defaultValue('fullName')
                        ->end()
                        ->scalarNode('is_active_field')
                            ->info('The is active property name')
                            ->defaultValue('isActive')
                        ->end()
                        ->arrayNode('admin_roles')
                            ->info('Roles to assign to the admin user')
                            ->scalarPrototype()->end()
                            ->defaultValue(['ROLE_ADMIN'])
                        ->end()
                    ->end()
                ->end()
                
                // Database configuration
                ->arrayNode('database')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('config_path')
                            ->info('Path to write database configuration')
                            ->defaultValue('%kernel.project_dir%/config/db.yaml')
                        ->end()
                        ->scalarNode('driver')
                            ->info('Database driver (mysql, pgsql, sqlite)')
                            ->defaultValue('mysql')
                        ->end()
                        ->scalarNode('charset')
                            ->info('Database charset')
                            ->defaultValue('utf8mb4')
                        ->end()
                    ->end()
                ->end()
                
                // System requirements
                ->arrayNode('requirements')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('php_version')
                            ->info('Minimum PHP version required')
                            ->defaultValue('8.2.0')
                        ->end()
                        ->arrayNode('php_extensions')
                            ->info('Required PHP extensions')
                            ->scalarPrototype()->end()
                            ->defaultValue([
                                'ctype', 'iconv', 'pcre', 'session', 'simplexml', 
                                'tokenizer', 'pdo_mysql', 'mbstring', 'json'
                            ])
                        ->end()
                        ->arrayNode('recommended_extensions')
                            ->info('Recommended PHP extensions')
                            ->scalarPrototype()->end()
                            ->defaultValue(['curl', 'zip', 'gd'])
                        ->end()
                    ->end()
                ->end()
                
                // Application configuration
                ->arrayNode('app_config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('config_path')
                            ->info('Path to write app configuration')
                            ->defaultValue('%kernel.project_dir%/config/app_config.yaml')
                        ->end()
                        ->arrayNode('parameters')
                            ->info('Additional parameters to configure during installation')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('label')->end()
                                    ->scalarNode('type')->defaultValue('text')->end()
                                    ->booleanNode('required')->defaultFalse()->end()
                                    ->scalarNode('default')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                
                // Installation marker
                ->scalarNode('install_marker_path')
                    ->info('Path to installation completion marker file')
                    ->defaultValue('%kernel.project_dir%/var/install_completed')
                ->end()
                
                // Routes prefix
                ->scalarNode('route_prefix')
                    ->info('URL prefix for installer routes')
                    ->defaultValue('/install')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
