<?php

namespace Webberdoo\InstallerBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;

class UserEntityDetector
{
    private array $entities;

    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * Auto-detect the User entity (implements UserInterface)
     */
    public function detectUserEntity(): ?string
    {
        foreach ($this->entities as $entityClass) {
            if (!class_exists($entityClass)) {
                continue;
            }

            $reflection = new \ReflectionClass($entityClass);
            if ($reflection->implementsInterface(UserInterface::class)) {
                return $entityClass;
            }
        }

        return null;
    }

    /**
     * Detect field names from the entity using reflection
     */
    public function detectFields(string $entityClass): array
    {
        if (!class_exists($entityClass)) {
            return [];
        }

        $reflection = new \ReflectionClass($entityClass);
        $fields = [
            'email' => null,
            'password' => null,
            'roles' => null,
            'fullName' => null,
            'isActive' => null,
        ];

        // Check for common property names
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            $lowerName = strtolower($name);

            // Email field
            if (in_array($lowerName, ['email', 'username', 'user', 'login'])) {
                $fields['email'] = $name;
            }

            // Password field
            if ($lowerName === 'password') {
                $fields['password'] = $name;
            }

            // Roles field
            if ($lowerName === 'roles') {
                $fields['roles'] = $name;
            }

            // Full name field
            if (in_array($lowerName, ['fullname', 'full_name', 'name', 'displayname'])) {
                $fields['fullName'] = $name;
            }

            // Active status field
            if (in_array($lowerName, ['isactive', 'is_active', 'active', 'enabled', 'status'])) {
                $fields['isActive'] = $name;
            }
        }

        return $fields;
    }

    /**
     * Check if a property exists and has a setter
     */
    public function hasProperty(string $entityClass, string $propertyName): bool
    {
        if (!class_exists($entityClass)) {
            return false;
        }

        $reflection = new \ReflectionClass($entityClass);
        
        // Check if property exists
        if (!$reflection->hasProperty($propertyName)) {
            return false;
        }

        // Check if setter method exists
        $setter = 'set' . ucfirst($propertyName);
        return $reflection->hasMethod($setter);
    }
}
