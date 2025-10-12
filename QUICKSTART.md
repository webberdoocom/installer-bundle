# Quick Start Guide

Get your Symfony application installer up and running in 5 minutes.

## Step 1: Install the Bundle

```bash
composer require webberdoo/installer-bundle
```

## Step 2: Register the Bundle

The bundle should auto-register via Symfony Flex. If not, add to `config/bundles.php`:

```php
<?php

return [
    // ... other bundles
    Webberdoo\InstallerBundle\InstallerBundle::class => ['all' => true],
];
```

## Step 3: Create Configuration

Create `config/packages/installer.yaml`:

```yaml
installer:
    entities:
        - App\Entity\User
        # Add all your entities
    
    admin_user:
        entity_class: App\Entity\User
```

**Full configuration example:** See `vendor/webberdoo/installer-bundle/config/packages/installer.yaml.example`

## Step 4: Build Frontend Assets

```bash
cd vendor/webberdoo/installer-bundle/assets
npm install
npm run build
cd ../../../..
```

## Step 5: Install Assets

```bash
php bin/console assets:install --symlink
```

## Step 6: Run the Installer

Start your Symfony dev server:

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

Navigate to: **http://localhost:8000/install**

---

## Example User Entity

Your User entity should implement `UserInterface` and `PasswordAuthenticatedUserInterface`:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column]
    private bool $isActive = false;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear temporary sensitive data if any
    }
}
```

---

## Next Steps

After installation completes:

1. **Log in** with your admin credentials
2. **Configure Security** - Set up firewalls and access control in `config/packages/security.yaml`
3. **Disable Installer** - Add route condition to prevent reinstallation

### Disable Installer After Installation

Add to `config/routes.yaml`:

```yaml
# Only load installer if not installed
installer:
    resource: '@InstallerBundle/Resources/config/routes.yaml'
    when: '@=!file_exists(parameter("kernel.project_dir") ~ "/var/install_completed")'
```

---

## Troubleshooting

### "Class not found" Error

Make sure you've built the bundle assets:
```bash
cd vendor/webberdoo/installer-bundle/assets && npm run build
```

### Assets Not Loading

```bash
php bin/console cache:clear
php bin/console assets:install --symlink
```

### Database Connection Failed

- Verify database exists
- Check credentials
- Ensure MySQL/MariaDB is running

---

## Support

- 📖 **Full Documentation**: See README.md
- 🐛 **Issues**: Open an issue on GitHub
- 💬 **Questions**: support@webberdoo.com
