<?php

namespace Webberdoo\InstallerBundle\Service;

use Symfony\Component\Yaml\Yaml;

class AppConfigWriter
{
    private array $appConfig;
    private string $installMarkerPath;

    public function __construct(array $appConfig, string $installMarkerPath)
    {
        $this->appConfig = $appConfig;
        $this->installMarkerPath = $installMarkerPath;
    }

    public function writeConfig(array $configData): array
    {
        try {
            $configPath = $this->appConfig['config_path'];
            
            // Ensure directory exists
            $dir = dirname($configPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Build configuration
            $config = ['parameters' => []];

            // Standard parameters
            if (isset($configData['base_url'])) {
                $config['parameters']['app.base_url'] = rtrim($configData['base_url'], '/');
            }

            if (isset($configData['base_path'])) {
                $basePath = $configData['base_path'] === '/' ? '' : rtrim($configData['base_path'], '/');
                $config['parameters']['app.base_path'] = $basePath;
            }

            if (isset($configData['base_url'])) {
                $config['parameters']['app.assets_base_url'] = '%app.base_url%/public';
            }

            // Additional custom parameters from bundle configuration
            foreach ($this->appConfig['parameters'] as $param) {
                $paramName = $param['name'];
                if (isset($configData[$paramName])) {
                    $config['parameters'][$paramName] = $configData[$paramName];
                } elseif (isset($param['default'])) {
                    $config['parameters'][$paramName] = $param['default'];
                }
            }

            // Write YAML file
            $yamlContent = Yaml::dump($config, 4, 2);
            file_put_contents($configPath, $yamlContent);

            // Update services.yaml to import app_config.yaml if needed
            $this->updateServicesYaml();

            // Create installation marker
            $this->createInstallMarker();

            return [
                'success' => true,
                'message' => 'Application configuration saved successfully',
                'config' => $config['parameters']
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error saving configuration: ' . $e->getMessage()
            ];
        }
    }

    private function updateServicesYaml(): void
    {
        $servicesPath = dirname($this->appConfig['config_path']) . '/services.yaml';
        
        if (!file_exists($servicesPath)) {
            return;
        }

        $servicesContent = file_get_contents($servicesPath);
        
        // Check if import already exists
        if (strpos($servicesContent, 'app_config.yaml') !== false) {
            return;
        }

        // Add import at the beginning
        $importLine = "# Import application configuration\nimports:\n    - { resource: app_config.yaml }\n\n";
        $servicesContent = $importLine . $servicesContent;
        
        file_put_contents($servicesPath, $servicesContent);
    }

    private function createInstallMarker(): void
    {
        $markerPath = $this->installMarkerPath;
        $dir = dirname($markerPath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = sprintf(
            "Installation completed: %s\nInstaller Bundle: Webberdoo\\InstallerBundle\n",
            date('Y-m-d H:i:s')
        );
        
        file_put_contents($markerPath, $content);
    }

    public function isInstalled(): bool
    {
        return file_exists($this->installMarkerPath);
    }
}
