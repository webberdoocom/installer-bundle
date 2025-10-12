<?php

namespace Webberdoo\InstallerBundle\Service;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Yaml\Yaml;

class SchemaInstaller
{
    private array $entities;
    private array $dbConfig;
    private string $projectDir;

    public function __construct(array $entities, array $dbConfig, string $projectDir)
    {
        $this->entities = $entities;
        $this->dbConfig = $dbConfig;
        $this->projectDir = $projectDir;
    }

    public function install(array $dbCredentials): array
    {
        try {
            // Build entity paths from entity classes
            $entityPaths = $this->getEntityPaths();
            
            // Create ORM configuration
            $ormConfig = ORMSetup::createAttributeMetadataConfiguration($entityPaths, false);
            
            // Set naming strategy (camelCase to snake_case)
            $ormConfig->setNamingStrategy(
                new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy(\CASE_LOWER, true)
            );

            // Set proxy directory
            $proxyDir = $this->projectDir . '/var/doctrine/proxies';
            if (!is_dir($proxyDir)) {
                mkdir($proxyDir, 0775, true);
            }
            $ormConfig->setProxyDir($proxyDir);

            // Create connection
            $connectionParams = [
                'driver'   => $this->dbConfig['driver'],
                'host'     => $dbCredentials['host'],
                'port'     => (int) $dbCredentials['port'],
                'dbname'   => $dbCredentials['dbname'],
                'user'     => $dbCredentials['user'],
                'password' => $dbCredentials['password'],
                'charset'  => $this->dbConfig['charset'],
            ];

            $conn = DriverManager::getConnection($connectionParams);
            $em = new EntityManager($conn, $ormConfig);

            // Verify connection
            $conn->connect();

            // Get metadata for all configured entities
            $metadata = [];
            foreach ($this->entities as $entityClass) {
                if (!class_exists($entityClass)) {
                    throw new \RuntimeException("Entity class not found: {$entityClass}");
                }
                $metadata[] = $em->getClassMetadata($entityClass);
            }

            if (empty($metadata)) {
                return [
                    'success' => false,
                    'message' => 'No entities configured. Please configure entities in installer.yaml'
                ];
            }

            // Create/update schema
            $schemaTool = new SchemaTool($em);
            $schemaTool->updateSchema($metadata, true); // Safe mode - only create, don't drop

            return [
                'success' => true,
                'message' => 'Database tables created successfully',
                'entities_installed' => count($metadata)
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error creating tables: ' . $e->getMessage()
            ];
        }
    }

    private function getEntityPaths(): array
    {
        $paths = [];
        
        foreach ($this->entities as $entityClass) {
            try {
                $reflection = new \ReflectionClass($entityClass);
                $entityPath = dirname($reflection->getFileName());
                
                if (!in_array($entityPath, $paths)) {
                    $paths[] = $entityPath;
                }
            } catch (\ReflectionException $e) {
                // Skip invalid classes
                continue;
            }
        }

        // Fallback to standard src/Entity directory
        if (empty($paths)) {
            $paths[] = $this->projectDir . '/src/Entity';
        }

        return $paths;
    }
}
