<?php

namespace Webberdoo\InstallerBundle\Service;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserCreator
{
    private array $adminConfig;
    private array $dbConfig;
    private string $projectDir;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        array $adminConfig,
        array $dbConfig,
        string $projectDir,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->adminConfig = $adminConfig;
        $this->dbConfig = $dbConfig;
        $this->projectDir = $projectDir;
        $this->passwordHasher = $passwordHasher;
    }

    public function createAdmin(array $dbCredentials, array $adminData): array
    {
        try {
            $entityClass = $this->adminConfig['entity_class'];

            if (!class_exists($entityClass)) {
                return [
                    'success' => false,
                    'message' => "User entity class not found: {$entityClass}"
                ];
            }

            // Validate admin data
            $validation = $this->validateAdminData($adminData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create standalone EntityManager
            $em = $this->createEntityManager($dbCredentials);

            // Check if admin already exists
            $emailField = $this->adminConfig['email_field'];
            $exists = $em->createQueryBuilder()
                ->select('u')
                ->from($entityClass, 'u')
                ->where("u.{$emailField} = :email")
                ->setParameter('email', $adminData['email'])
                ->getQuery()
                ->getOneOrNullResult();

            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Admin user with this email already exists'
                ];
            }

            // Create admin user using dynamic setters
            $user = new $entityClass();
            $this->setUserProperties($user, $adminData);

            // Persist
            try {
                $em->persist($user);
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                return [
                    'success' => false,
                    'message' => 'Admin user already exists (unique constraint)'
                ];
            }

            return [
                'success' => true,
                'message' => 'Admin user created successfully'
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ];
        }
    }

    private function validateAdminData(array $data): array
    {
        if (empty($data['email'])) {
            return ['valid' => false, 'message' => 'Email is required'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        if (empty($data['password'])) {
            return ['valid' => false, 'message' => 'Password is required'];
        }

        if (empty($data['fullName'])) {
            return ['valid' => false, 'message' => 'Full name is required'];
        }

        return ['valid' => true];
    }

    private function setUserProperties(object $user, array $data): void
    {
        // Email
        $emailSetter = 'set' . ucfirst($this->adminConfig['email_field']);
        if (method_exists($user, $emailSetter)) {
            $user->$emailSetter(strtolower(trim($data['email'])));
        }

        // Password (hashed)
        $passwordSetter = 'set' . ucfirst($this->adminConfig['password_field']);
        if (method_exists($user, $passwordSetter)) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->$passwordSetter($hashedPassword);
        }

        // Full Name
        $fullNameSetter = 'set' . ucfirst($this->adminConfig['full_name_field']);
        if (method_exists($user, $fullNameSetter)) {
            $user->$fullNameSetter(trim($data['fullName']));
        }

        // Roles
        $rolesSetter = 'set' . ucfirst($this->adminConfig['roles_field']);
        if (method_exists($user, $rolesSetter)) {
            $user->$rolesSetter($this->adminConfig['admin_roles']);
        }

        // Is Active
        $isActiveSetter = 'set' . ucfirst($this->adminConfig['is_active_field']);
        if (method_exists($user, $isActiveSetter)) {
            $user->$isActiveSetter(true);
        }
    }

    private function createEntityManager(array $dbCredentials): EntityManager
    {
        $reflection = new \ReflectionClass($this->adminConfig['entity_class']);
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
