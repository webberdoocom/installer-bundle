<?php

namespace Webberdoo\InstallerBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Webberdoo\InstallerBundle\Service\SystemRequirementsChecker;

class SystemRequirementsCheckerTest extends TestCase
{
    private SystemRequirementsChecker $checker;

    protected function setUp(): void
    {
        $requirements = [
            'php_version' => '8.2.0',
            'php_extensions' => ['pdo_mysql', 'mbstring', 'json'],
            'recommended_extensions' => ['curl', 'zip']
        ];

        $this->checker = new SystemRequirementsChecker(
            $requirements,
            sys_get_temp_dir()
        );
    }

    public function testCheckReturnsArray(): void
    {
        $result = $this->checker->check();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('checks', $result);
        $this->assertArrayHasKey('all_passed', $result);
        $this->assertArrayHasKey('critical_failed', $result);
        $this->assertArrayHasKey('can_proceed', $result);
    }

    public function testPhpVersionCheck(): void
    {
        $result = $this->checker->check();

        $this->assertArrayHasKey('php_version', $result['checks']);
        
        $phpCheck = $result['checks']['php_version'];
        $this->assertEquals('PHP Version', $phpCheck['name']);
        $this->assertTrue($phpCheck['critical']);
        $this->assertIsBool($phpCheck['status']);
    }

    public function testRequiredExtensionsCheck(): void
    {
        $result = $this->checker->check();

        // Check PDO MySQL extension
        $this->assertArrayHasKey('ext_pdo_mysql', $result['checks']);
        
        $pdoCheck = $result['checks']['ext_pdo_mysql'];
        $this->assertEquals('Pdo_mysql Extension', $pdoCheck['name']);
        $this->assertTrue($pdoCheck['critical']);
        
        // Extension should be loaded in test environment
        $this->assertTrue($pdoCheck['status']);
    }

    public function testRecommendedExtensionsCheck(): void
    {
        $result = $this->checker->check();

        // Check cURL extension (recommended)
        $this->assertArrayHasKey('ext_rec_curl', $result['checks']);
        
        $curlCheck = $result['checks']['ext_rec_curl'];
        $this->assertEquals('Curl Extension', $curlCheck['name']);
        $this->assertFalse($curlCheck['critical']);
    }

    public function testDirectoryPermissions(): void
    {
        $result = $this->checker->check();

        // var directory check
        $this->assertArrayHasKey('var_writable', $result['checks']);
        
        $varCheck = $result['checks']['var_writable'];
        $this->assertEquals('Var Directory Writable', $varCheck['name']);
        $this->assertTrue($varCheck['critical']);
    }

    public function testCanProceedWhenCriticalChecksFail(): void
    {
        // Create checker with impossible PHP version requirement
        $strictRequirements = [
            'php_version' => '99.0.0',
            'php_extensions' => [],
            'recommended_extensions' => []
        ];

        $strictChecker = new SystemRequirementsChecker(
            $strictRequirements,
            sys_get_temp_dir()
        );

        $result = $strictChecker->check();

        $this->assertTrue($result['critical_failed']);
        $this->assertFalse($result['can_proceed']);
    }

    public function testAllPassedWhenRequirementsMet(): void
    {
        $result = $this->checker->check();

        // In a proper test environment, all required extensions should be loaded
        if ($result['all_passed']) {
            $this->assertFalse($result['critical_failed']);
            $this->assertTrue($result['can_proceed']);
        }
    }
}
