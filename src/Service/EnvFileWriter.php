<?php

namespace Webberdoo\InstallerBundle\Service;

class EnvFileWriter
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Update DATABASE_URL in .env file
     */
    public function updateDatabaseUrl(array $credentials): bool
    {
        $envFile = $this->projectDir . '/.env';
        
        if (!file_exists($envFile)) {
            return false;
        }

        // Build DATABASE_URL
        $databaseUrl = sprintf(
            'mysql://%s:%s@%s:%d/%s?serverVersion=8.0',
            $credentials['db_user'],
            $credentials['password'],
            $credentials['host'],
            $credentials['port'],
            $credentials['db_name']
        );

        // Read current .env content
        $content = file_get_contents($envFile);
        
        // Check if DATABASE_URL exists
        if (preg_match('/^DATABASE_URL=/m', $content)) {
            // Replace existing DATABASE_URL
            $content = preg_replace(
                '/^DATABASE_URL=.*/m',
                'DATABASE_URL="' . $databaseUrl . '"',
                $content
            );
        } else {
            // Add DATABASE_URL if not present
            $content .= "\n# Database Configuration (added by installer)\n";
            $content .= 'DATABASE_URL="' . $databaseUrl . '"' . "\n";
        }

        // Write back to .env
        return file_put_contents($envFile, $content) !== false;
    }

    /**
     * Check if .env file is writable
     */
    public function isEnvWritable(): bool
    {
        $envFile = $this->projectDir . '/.env';
        return file_exists($envFile) && is_writable($envFile);
    }
}
