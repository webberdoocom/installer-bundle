<?php

namespace Webberdoo\InstallerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class InstallationStatusChecker
{
    private DatabaseConfigWriter $dbConfigWriter;
    private AppConfigWriter $appConfigWriter;
    private array $adminConfig;
    private ?EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(
        DatabaseConfigWriter $dbConfigWriter,
        AppConfigWriter $appConfigWriter,
        array $adminConfig,
        string $projectDir,
        ?EntityManagerInterface $em = null
    ) {
        $this->dbConfigWriter = $dbConfigWriter;
        $this->appConfigWriter = $appConfigWriter;
        $this->adminConfig = $adminConfig;
        $this->projectDir = $projectDir;
        $this->em = $em;
    }

    public function getStatus(): array
    {
        $status = [
            'database_config' => false,
            'database_tables' => false,
            'admin_user' => false,
            'smtp_config' => false,
            'app_config' => false
        ];

        // Check database configuration
        $dbConfig = $this->dbConfigWriter->readConfig();
        if ($dbConfig && $this->dbConfigWriter->validateConfig($dbConfig)) {
            $status['database_config'] = true;
        }

        // Check database tables and admin user (only if EntityManager is available)
        if ($status['database_config'] && $this->em !== null) {
            try {
                $userEntityClass = $this->adminConfig['entity_class'];
                $userRepo = $this->em->getRepository($userEntityClass);
                
                // Try to count users - this will fail if table doesn't exist
                $userRepo->createQueryBuilder('u')
                    ->select('COUNT(u.id)')
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $status['database_tables'] = true;

                // Check if admin user exists
                $rolesField = $this->adminConfig['roles_field'];
                $adminCount = $userRepo->createQueryBuilder('u')
                    ->select('COUNT(u.id)')
                    ->where("u.{$rolesField} LIKE :role")
                    ->setParameter('role', '%ROLE_ADMIN%')
                    ->getQuery()
                    ->getSingleScalarResult();

                if ($adminCount > 0) {
                    $status['admin_user'] = true;
                }

            } catch (\Exception $e) {
                // Database connection failed or tables don't exist
                $status['database_tables'] = false;
                $status['admin_user'] = false;
            }
        }

        // Check SMTP config (optional - if admin_user is set, consider SMTP complete)
        if ($status['admin_user']) {
            // Check if smtp.yaml exists or mark as complete by default
            $smtpConfigPath = $this->projectDir . '/config/smtp.yaml';
            if (file_exists($smtpConfigPath)) {
                $status['smtp_config'] = true;
            } else {
                // SMTP is optional, so we mark it as complete if skipped
                // This allows the installer to proceed
                $status['smtp_config'] = true;
            }
        }

        // Check app config (only if all previous steps are complete)
        if ($this->appConfigWriter->isInstalled() &&
            $status['database_config'] &&
            $status['database_tables'] &&
            $status['admin_user']) {
            $status['app_config'] = true;
        }

        $completed = $status['database_config'] &&
                    $status['database_tables'] &&
                    $status['admin_user'] &&
                    $status['smtp_config'] &&
                    $status['app_config'];

        return [
            'status' => $status,
            'completed' => $completed
        ];
    }
}
