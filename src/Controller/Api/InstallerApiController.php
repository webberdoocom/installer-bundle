<?php

namespace Webberdoo\InstallerBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Webberdoo\InstallerBundle\Service\SystemRequirementsChecker;
use Webberdoo\InstallerBundle\Service\DatabaseConfigWriter;
use Webberdoo\InstallerBundle\Service\SchemaInstaller;
use Webberdoo\InstallerBundle\Service\AdminUserCreator;
use Webberdoo\InstallerBundle\Service\AppConfigWriter;
use Webberdoo\InstallerBundle\Service\InstallationStatusChecker;

#[Route('/install/api', name: 'installer_api_')]
class InstallerApiController extends AbstractController
{
    /**
     * Step 1: System Requirements Check
     */
    #[Route('/system-check', name: 'system_check', methods: ['GET'])]
    public function systemCheck(SystemRequirementsChecker $checker): JsonResponse
    {
        try {
            $result = $checker->check();
            return new JsonResponse(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error checking system requirements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Save Database Configuration
     */
    #[Route('/database-config', name: 'database_config', methods: ['POST'])]
    public function databaseConfig(
        Request $request,
        DatabaseConfigWriter $writer
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], 400);
            }

            // Validate required fields (password can be empty for localhost)
            $required = ['db_name', 'host', 'port', 'db_user'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    return new JsonResponse([
                        'success' => false,
                        'message' => "Field {$field} is required"
                    ], 400);
                }
            }
            
            // Password must be present but can be empty
            if (!isset($data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Field password is required'
                ], 400);
            }

            // Test connection first
            $connectionTest = $writer->testConnection($data);
            if (!$connectionTest['success']) {
                return new JsonResponse($connectionTest, 400);
            }

            // Write configuration
            $writer->writeConfig($data);

            return new JsonResponse([
                'success' => true,
                'message' => 'Database configuration saved successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error saving database configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 3: Install Database Tables
     */
    #[Route('/install-tables', name: 'install_tables', methods: ['POST'])]
    public function installTables(
        DatabaseConfigWriter $dbWriter,
        SchemaInstaller $installer
    ): JsonResponse {
        try {
            // Read database configuration
            $dbConfig = $dbWriter->readConfig();

            if (!$dbConfig || !$dbWriter->validateConfig($dbConfig)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Database configuration not found. Complete Step 2 first.'
                ], 400);
            }

            // Install schema
            $result = $installer->install($dbConfig);

            return new JsonResponse($result, $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error installing tables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 4: Create Admin User
     */
    #[Route('/create-admin', name: 'create_admin', methods: ['POST'])]
    public function createAdmin(
        Request $request,
        DatabaseConfigWriter $dbWriter,
        AdminUserCreator $creator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], 400);
            }

            // Read database configuration
            $dbConfig = $dbWriter->readConfig();

            if (!$dbConfig || !$dbWriter->validateConfig($dbConfig)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Database configuration not found. Complete Step 2 first.'
                ], 400);
            }

            // Create admin user
            $result = $creator->createAdmin($dbConfig, $data);

            return new JsonResponse($result, $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 5: Save Application Configuration
     */
    #[Route('/app-config', name: 'app_config', methods: ['POST'])]
    public function appConfig(
        Request $request,
        AppConfigWriter $writer
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], 400);
            }

            // Write configuration
            $result = $writer->writeConfig($data);

            return new JsonResponse($result, $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error saving app configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Installation Status
     */
    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(InstallationStatusChecker $statusChecker): JsonResponse
    {
        try {
            $result = $statusChecker->getStatus();
            return new JsonResponse(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error checking installation status: ' . $e->getMessage()
            ], 500);
        }
    }
}
