<?php

namespace Webberdoo\InstallerBundle\Service;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Yaml\Yaml;

class SmtpConfigWriter
{
    private array $adminConfig;
    private array $dbConfig;
    private string $projectDir;
    private UserEntityDetector $userEntityDetector;

    public function __construct(
        array $adminConfig,
        array $dbConfig,
        string $projectDir,
        UserEntityDetector $userEntityDetector
    ) {
        $this->adminConfig = $adminConfig;
        $this->dbConfig = $dbConfig;
        $this->projectDir = $projectDir;
        $this->userEntityDetector = $userEntityDetector;
    }

    public function saveSmtpConfig(array $dbCredentials, array $smtpData, ?string $adminEmail = null): array
    {
        try {
            // If skip flag is set, just mark as completed
            if (!empty($smtpData['skip'])) {
                return [
                    'success' => true,
                    'message' => 'SMTP configuration skipped',
                    'skipped' => true
                ];
            }

            // Auto-detect User entity if not configured
            $entityClass = $this->adminConfig['entity_class'] ?? $this->userEntityDetector->detectUserEntity();

            if (!$entityClass) {
                return [
                    'success' => false,
                    'message' => 'No User entity found. Make sure your User entity implements UserInterface and is listed in installer.entities.'
                ];
            }

            if (!class_exists($entityClass)) {
                return [
                    'success' => false,
                    'message' => "User entity class not found: {$entityClass}"
                ];
            }

            // Auto-detect fields
            $fields = $this->userEntityDetector->detectFields($entityClass);

            // Create standalone EntityManager
            $em = $this->createEntityManager($dbCredentials);

            // Find admin user (most recent user if no email provided)
            $user = $this->findAdminUser($em, $entityClass, $fields, $adminEmail);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Admin user not found. Please create an admin user first.'
                ];
            }

            // Update user with SMTP details
            $this->setSmtpProperties($user, $smtpData, $fields);

            // Persist changes
            $em->persist($user);
            $em->flush();

            // Also save to app config for easy access
            $this->saveToAppConfig($smtpData);

            return [
                'success' => true,
                'message' => 'SMTP configuration saved successfully'
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error saving SMTP configuration: ' . $e->getMessage()
            ];
        }
    }

    private function findAdminUser(EntityManager $em, string $entityClass, array $fields, ?string $email): ?object
    {
        $emailField = $fields['email'] ?? 'email';

        if ($email) {
            // Find by specific email
            return $em->createQueryBuilder()
                ->select('u')
                ->from($entityClass, 'u')
                ->where("u.{$emailField} = :email")
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult();
        }

        // Get the most recent user (assuming ID is auto-increment)
        return $em->createQueryBuilder()
            ->select('u')
            ->from($entityClass, 'u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function setSmtpProperties(object $user, array $data, array $fields): void
    {
        $smtpFieldMappings = [
            'smtpHost' => 'smtpHost',
            'smtpPort' => 'smtpPort',
            'smtpUsername' => 'smtpUsername',
            'smtpPassword' => 'smtpPassword',
            'smtpEncryption' => 'smtpEncryption',
            'smtpFromEmail' => 'smtpFromEmail',
            'smtpFromName' => 'smtpFromName',
        ];

        foreach ($smtpFieldMappings as $dataKey => $fieldKey) {
            if (isset($data[$dataKey]) && !empty($data[$dataKey])) {
                // Check if the field exists in the detected fields
                if (isset($fields[$fieldKey])) {
                    $setter = 'set' . ucfirst($fields[$fieldKey]);
                    if (method_exists($user, $setter)) {
                        $user->$setter($data[$dataKey]);
                    }
                } else {
                    // Try direct setter with the field name
                    $setter = 'set' . ucfirst($fieldKey);
                    if (method_exists($user, $setter)) {
                        $user->$setter($data[$dataKey]);
                    }
                }
            }
        }
    }

    private function saveToAppConfig(array $smtpData): void
    {
        $configPath = $this->projectDir . '/config/smtp.yaml';
        
        $config = [
            'parameters' => [
                'mailer_transport' => 'smtp',
                'mailer_host' => $smtpData['smtpHost'] ?? '',
                'mailer_port' => (int) ($smtpData['smtpPort'] ?? 587),
                'mailer_user' => $smtpData['smtpUsername'] ?? '',
                'mailer_password' => $smtpData['smtpPassword'] ?? '',
                'mailer_encryption' => $smtpData['smtpEncryption'] ?? 'tls',
                'mailer_from_email' => $smtpData['smtpFromEmail'] ?? '',
                'mailer_from_name' => $smtpData['smtpFromName'] ?? '',
            ]
        ];

        $yaml = Yaml::dump($config, 4, 2);
        file_put_contents($configPath, $yaml);
    }

    private function createEntityManager(array $dbCredentials): EntityManager
    {
        $entityClass = $this->adminConfig['entity_class'] ?? $this->userEntityDetector->detectUserEntity();
        $reflection = new \ReflectionClass($entityClass);
        $entityPath = dirname($reflection->getFileName());

        $ormConfig = ORMSetup::createAttributeMetadataConfiguration([$entityPath], false);
        $ormConfig->setNamingStrategy(
            new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy(\CASE_LOWER, true)
        );

        $proxyDir = $this->projectDir . '/var/doctrine/proxies';
        if (!is_dir($proxyDir)) {
            mkdir($proxyDir, 0775, true);
        }
        $ormConfig->setProxyDir($proxyDir);

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
        return new EntityManager($conn, $ormConfig);
    }
}
