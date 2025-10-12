<?php

namespace Webberdoo\InstallerBundle\Service;

use Symfony\Component\Yaml\Yaml;

class DatabaseConfigWriter
{
    private array $dbConfig;

    public function __construct(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
    }

    public function writeConfig(array $credentials): void
    {
        $configPath = $this->dbConfig['config_path'];
        
        // Ensure directory exists
        $dir = dirname($configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = [
            'parameters' => [
                'dbname' => $credentials['db_name'],
                'host' => $credentials['host'],
                'port' => (int) $credentials['port'],
                'user' => $credentials['db_user'],
                'password' => $credentials['password']
            ]
        ];

        $yaml = Yaml::dump($config);
        file_put_contents($configPath, $yaml);
    }

    public function readConfig(): ?array
    {
        $configPath = $this->dbConfig['config_path'];
        
        if (!file_exists($configPath)) {
            return null;
        }

        $config = Yaml::parseFile($configPath);
        return $config['parameters'] ?? null;
    }

    public function validateConfig(array $db): bool
    {
        $required = ['dbname', 'host', 'port', 'user'];
        
        foreach ($required as $field) {
            if (!isset($db[$field]) || $db[$field] === '') {
                return false;
            }
        }

        // Password must exist but can be empty
        if (!isset($db['password'])) {
            return false;
        }

        return true;
    }

    public function testConnection(array $credentials): array
    {
        try {
            // Convert Doctrine driver to PDO driver
            $pdoDriver = $this->convertToPdoDriver($this->dbConfig['driver']);
            
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $pdoDriver,
                $credentials['host'],
                (int) $credentials['port'],
                $credentials['db_name'],
                $this->dbConfig['charset']
            );

            $pdo = new \PDO(
                $dsn,
                $credentials['db_user'],
                $credentials['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Convert Doctrine driver name to PDO driver name
     */
    private function convertToPdoDriver(string $doctrineDriver): string
    {
        return match($doctrineDriver) {
            'pdo_mysql' => 'mysql',
            'pdo_pgsql' => 'pgsql',
            'pdo_sqlite' => 'sqlite',
            default => $doctrineDriver,
        };
    }
}
