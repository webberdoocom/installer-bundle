<?php

namespace Webberdoo\InstallerBundle\Service;

class SystemRequirementsChecker
{
    private array $requirements;
    private string $projectDir;

    public function __construct(array $requirements, string $projectDir)
    {
        $this->requirements = $requirements;
        $this->projectDir = $projectDir;
    }

    public function check(): array
    {
        $checks = [];

        // PHP Version
        $checks['php_version'] = [
            'name' => 'PHP Version',
            'required' => $this->requirements['php_version'] . ' or higher',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, $this->requirements['php_version'], '>='),
            'critical' => true
        ];

        // Required Extensions
        foreach ($this->requirements['php_extensions'] as $extension) {
            $checks['ext_' . $extension] = [
                'name' => ucfirst($extension) . ' Extension',
                'required' => 'Required',
                'current' => extension_loaded($extension) ? 'Installed' : 'Not installed',
                'status' => extension_loaded($extension),
                'critical' => true
            ];
        }

        // Recommended Extensions
        foreach ($this->requirements['recommended_extensions'] as $extension) {
            $checks['ext_rec_' . $extension] = [
                'name' => ucfirst($extension) . ' Extension',
                'required' => 'Recommended',
                'current' => extension_loaded($extension) ? 'Installed' : 'Not installed',
                'status' => extension_loaded($extension),
                'critical' => false
            ];
        }

        // Directory Permissions
        $checks['config_writable'] = [
            'name' => 'Config Directory Writable',
            'required' => 'Required',
            'current' => is_writable($this->projectDir . '/config') ? 'Writable' : 'Not writable',
            'status' => is_writable($this->projectDir . '/config'),
            'critical' => true
        ];

        $checks['var_writable'] = [
            'name' => 'Var Directory Writable',
            'required' => 'Required',
            'current' => is_writable($this->projectDir . '/var') ? 'Writable' : 'Not writable',
            'status' => is_writable($this->projectDir . '/var'),
            'critical' => true
        ];

        // Calculate overall status
        $allPassed = true;
        $criticalFailed = false;

        foreach ($checks as $check) {
            if (!$check['status']) {
                $allPassed = false;
                if ($check['critical']) {
                    $criticalFailed = true;
                }
            }
        }

        return [
            'checks' => $checks,
            'all_passed' => $allPassed,
            'critical_failed' => $criticalFailed,
            'can_proceed' => !$criticalFailed
        ];
    }
}
