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
    private UserEntityDetector $userEntityDetector;

    public function __construct(
        array $adminConfig,
        array $dbConfig,
        string $projectDir,
        UserPasswordHasherInterface $passwordHasher,
        UserEntityDetector $userEntityDetector
    ) {
        $this->adminConfig = $adminConfig;
        $this->dbConfig = $dbConfig;
        $this->projectDir = $projectDir;
        $this->passwordHasher = $passwordHasher;
        $this->userEntityDetector = $userEntityDetector;
    }

    public function createAdmin(array $dbCredentials, array $adminData): array
    {
        try {
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
            $emailField = $fields['email'] ?? 'email';
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
            $this->setUserProperties($user, $adminData, $fields);

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

        // Full name is optional
        return ['valid' => true];
    }

    private function setUserProperties(object $user, array $data, array $fields): void
    {
        // Email
        if ($fields['email']) {
            $emailSetter = 'set' . ucfirst($fields['email']);
            if (method_exists($user, $emailSetter)) {
                $user->$emailSetter(strtolower(trim($data['email'])));
            }
        }

        // Password (hashed)
        if ($fields['password']) {
            $passwordSetter = 'set' . ucfirst($fields['password']);
            if (method_exists($user, $passwordSetter)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
                $user->$passwordSetter($hashedPassword);
            }
        }

        // Full Name (optional)
        if ($fields['fullName'] && !empty($data['fullName'])) {
            $fullNameSetter = 'set' . ucfirst($fields['fullName']);
            if (method_exists($user, $fullNameSetter)) {
                $user->$fullNameSetter(trim($data['fullName']));
            }
        }

        // Roles
        if ($fields['roles']) {
            $rolesSetter = 'set' . ucfirst($fields['roles']);
            if (method_exists($user, $rolesSetter)) {
                $user->$rolesSetter($this->adminConfig['admin_roles']);
            }
        }

        // Is Active (optional)
        if ($fields['isActive']) {
            $isActiveSetter = 'set' . ucfirst($fields['isActive']);
            if (method_exists($user, $isActiveSetter)) {
                $user->$isActiveSetter(true);
            }
        }
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
