# Webberdoo Installer Bundle

A complete, reusable Symfony installer bundle with a modern React + Tailwind CSS v4 frontend. This bundle provides a web-based installation wizard for Symfony applications with **automatic User entity detection**, configurable entities, database setup, and admin user creation.

## Features

✅ **Modern UI** - React with Tailwind CSS v4  
✅ **Auto-Detection** - Automatically detects User entity and field names  
✅ **Minimal Configuration** - Just list your entities, everything else is automatic  
✅ **Step-by-step Installation** - System requirements, database, tables, admin user, SMTP (optional), app config  
✅ **Optional SMTP Configuration** - Configure email settings during installation or skip for later  
✅ **Safe Schema Installation** - Only creates tables, never drops existing ones  
✅ **Dynamic Entity Support** - Add any Doctrine entities to install  
✅ **Auto .env Update** - Automatically updates DATABASE_URL in your .env file  
✅ **Standalone Installation** - Works without existing Symfony configuration  
✅ **Installation Status Tracking** - Resume interrupted installations  

---

## Installation

### 1. Install via Composer

```bash
composer require webberdoocom/installer-bundle
```

### 2. Register the Bundle

Add to `config/bundles.php`:

```php
return [
    // ... other bundles
    Webberdoo\InstallerBundle\InstallerBundle::class => ['all' => true],
];
```

### 3. Register Routes

Add to `config/routes.yaml`:

```yaml
installer:
    resource:
        path: ../vendor/webberdoocom/installer-bundle/src/Controller/
        namespace: Webberdoo\InstallerBundle\Controller
    type: attribute
```

**Important:** The route prefix `/install` is already defined in the controller attributes, so don't add a prefix here.

### 4. Configure the Bundle (Minimal Setup)

Create `config/packages/installer.yaml` with **just your entities**:

```yaml
installer:
    entities:
        - App\Entity\User
        # Add more entities as needed:
        # - App\Entity\Post
        # - App\Entity\Category
```

**That's it!** The bundle will automatically:
- ✅ Detect your User entity (must implement `UserInterface`)
- ✅ Auto-detect field names (email, password, roles, etc.)
- ✅ Use sensible defaults for database driver, paths, and requirements
- ✅ Update your `.env` file with `DATABASE_URL`

### 4b. Optional Advanced Configuration

If you need to customize, here's the full configuration with defaults:

```yaml
installer:
    entities:
        - App\Entity\User
    
    # Optional: Only needed if you want to change defaults
    database:
        driver: pdo_mysql          # pdo_mysql, pdo_pgsql, pdo_sqlite
        charset: utf8mb4
    
    # Optional: Customize PHP requirements
    requirements:
        php_version: '8.2.0'
        php_extensions:
            - ctype
            - iconv
            - pcre
            - session
            - simplexml
            - tokenizer
            - pdo_mysql
            - mbstring
            - json
    
    # Optional: Add custom application parameters
    app_config:
        parameters:
            - name: APP_NAME
              label: Application Name
              type: text
              required: true
```

### 5. Install Assets to Public Directory

**Note:** The frontend assets are **pre-built and included** in the package. You don't need to build them yourself unless you're modifying the source code.

```bash
php bin/console assets:install --symlink
```

---

## Usage

### Access the Installer

Navigate to: `http://your-app.com/install`

### Installation Steps

1. **System Requirements Check**
   - Verifies PHP version
   - Checks required and recommended PHP extensions
   - Validates directory permissions

2. **Database Configuration**
   - Enter database credentials
   - Tests connection before saving
   - Automatically updates `.env` file with `DATABASE_URL`
   - Writes config to `config/db.yaml`

3. **Install Tables**
   - Creates all configured entity tables
   - Sets up indexes and foreign keys
   - Safe mode - only creates, never drops

4. **Create Admin User**
   - Create administrator account
   - Password strength indicator
   - Email validation

5. **SMTP Configuration (Optional)**
   - Configure email/SMTP settings
   - Saved to both user entity and `config/smtp.yaml`
   - Can be skipped and configured later

6. **Application Configuration**
   - Set base URL and path
   - Configure custom parameters
   - Creates installation marker

---

## Configuration Examples

### Minimal Configuration (Recommended)

The simplest setup - just list your entities:

```yaml
installer:
    entities:
        - App\Entity\User
        - App\Entity\Post
        - App\Entity\Comment
```

The bundle automatically:
- Finds your User entity (implements `UserInterface`)
- Detects fields: `email`, `password`, `roles`, `fullName`, `isActive`
- Uses `pdo_mysql` driver with `utf8mb4` charset
- Sets sensible PHP version and extension requirements

### Adding Custom Application Parameters

```yaml
installer:
    entities:
        - App\Entity\User
    
    app_config:
        parameters:
            - name: OPENAI_API_KEY
              label: OpenAI API Key
              type: text
              required: false
              default: ''
            - name: MAIL_FROM
              label: Email From Address
              type: email
              required: true
```

### PostgreSQL Database

```yaml
installer:
    entities:
        - App\Entity\User
    
    database:
        driver: pdo_pgsql
        charset: utf8
```

### Manually Specify User Entity (Advanced)

Only needed if auto-detection doesn't work or you have multiple User entities:

```yaml
installer:
    entities:
        - App\Entity\User
        - App\Entity\Customer
    
    admin_user:
        entity_class: App\Entity\User  # Explicitly specify which one
        admin_roles:
            - ROLE_ADMIN
            - ROLE_SUPER_ADMIN
```

---

## How Auto-Detection Works

### User Entity Detection

The bundle automatically finds your User entity by:
1. Scanning all entities listed in your configuration
2. Finding the one that implements `Symfony\Component\Security\Core\User\UserInterface`
3. No manual configuration needed!

### Field Name Detection

The bundle intelligently detects field names using common naming patterns:

| Field Type | Detected Names |
|-----------|----------------|
| **Email** | `email`, `username`, `user`, `login` |
| **Password** | `password` |
| **Roles** | `roles` |
| **Full Name** | `fullName`, `full_name`, `name`, `displayname` |
| **Active Status** | `isActive`, `is_active`, `active`, `enabled`, `status` |
| **SMTP Host** | `smtpHost`, `smtp_host`, `mailHost`, `mail_host` |
| **SMTP Port** | `smtpPort`, `smtp_port`, `mailPort`, `mail_port` |
| **SMTP Username** | `smtpUsername`, `smtp_username`, `smtpUser`, `smtp_user` |
| **SMTP Password** | `smtpPassword`, `smtp_password`, `smtpPass`, `smtp_pass` |
| **SMTP Encryption** | `smtpEncryption`, `smtp_encryption`, `smtpTls`, `smtp_tls` |
| **SMTP From Email** | `smtpFromEmail`, `smtp_from_email`, `fromEmail`, `from_email` |
| **SMTP From Name** | `smtpFromName`, `smtp_from_name`, `fromName`, `from_name` |

### Your User Entity

The installer works with any User entity structure:

```php
class User implements UserInterface
{
    private ?string $email = null;      // ✅ Detected as email field
    private ?string $password = null;   // ✅ Detected as password field
    private array $roles = [];          // ✅ Detected as roles field
    
    // Optional fields - will be set if they exist:
    private ?string $fullName = null;   // ✅ Auto-detected
    private bool $isActive = true;      // ✅ Auto-detected
    
    // SMTP fields (optional) - Auto-detected if present:
    private ?string $smtpHost = null;      // ✅ For email configuration
    private ?int $smtpPort = null;         // ✅ Auto-detected
    private ?string $smtpUsername = null;  // ✅ Auto-detected
    private ?string $smtpPassword = null;  // ✅ Auto-detected
    private ?string $smtpEncryption = null; // ✅ Auto-detected (tls/ssl)
    private ?string $smtpFromEmail = null; // ✅ Auto-detected
    private ?string $smtpFromName = null;  // ✅ Auto-detected
    
    // Standard getters/setters...
}
```

If a field doesn't exist or doesn't have a setter, the installer simply skips it - no errors!

---

## SMTP Configuration

### Overview

The installer includes an optional SMTP configuration step that allows users to set up email settings during installation. This feature is **completely optional** and can be skipped if you prefer to configure email settings later.

### How It Works

1. **During Installation** - Step 5 prompts for SMTP settings:
   - SMTP Host (e.g., `smtp.gmail.com`)
   - SMTP Port (default: `587`)
   - SMTP Username
   - SMTP Password
   - Encryption (TLS/SSL/None)
   - From Email (optional)
   - From Name (optional)

2. **Storage** - SMTP settings are saved in two locations:
   - **User Entity** - If your User entity has SMTP fields, they're automatically populated
   - **Config File** - Settings are also saved to `config/smtp.yaml` for easy access

3. **Skip Option** - Users can click "Skip for Now" to bypass this step and configure SMTP later

### Setting Up Your User Entity for SMTP

To enable SMTP configuration during installation, add these optional fields to your User entity:

```php
<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    // Required fields...
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    // SMTP Configuration Fields (Optional)
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpHost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $smtpPort = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpUsername = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpPassword = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $smtpEncryption = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpFromEmail = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpFromName = null;

    // Getters and setters...
    public function getSmtpHost(): ?string { return $this->smtpHost; }
    public function setSmtpHost(?string $smtpHost): self { $this->smtpHost = $smtpHost; return $this; }
    
    public function getSmtpPort(): ?int { return $this->smtpPort; }
    public function setSmtpPort(?int $smtpPort): self { $this->smtpPort = $smtpPort; return $this; }
    
    public function getSmtpUsername(): ?string { return $this->smtpUsername; }
    public function setSmtpUsername(?string $smtpUsername): self { $this->smtpUsername = $smtpUsername; return $this; }
    
    public function getSmtpPassword(): ?string { return $this->smtpPassword; }
    public function setSmtpPassword(?string $smtpPassword): self { $this->smtpPassword = $smtpPassword; return $this; }
    
    public function getSmtpEncryption(): ?string { return $this->smtpEncryption; }
    public function setSmtpEncryption(?string $smtpEncryption): self { $this->smtpEncryption = $smtpEncryption; return $this; }
    
    public function getSmtpFromEmail(): ?string { return $this->smtpFromEmail; }
    public function setSmtpFromEmail(?string $smtpFromEmail): self { $this->smtpFromEmail = $smtpFromEmail; return $this; }
    
    public function getSmtpFromName(): ?string { return $this->smtpFromName; }
    public function setSmtpFromName(?string $smtpFromName): self { $this->smtpFromName = $smtpFromName; return $this; }
}
```

### Using SMTP Configuration

After installation, SMTP settings are available in two ways:

#### 1. From User Entity

Access SMTP settings from the authenticated user:

```php
$user = $this->getUser();
$smtpHost = $user->getSmtpHost();
$smtpPort = $user->getSmtpPort();
// Configure mailer with these settings...
```

#### 2. From Config File

The installer also creates `config/smtp.yaml`:

```yaml
parameters:
    mailer_transport: smtp
    mailer_host: smtp.gmail.com
    mailer_port: 587
    mailer_user: your-email@gmail.com
    mailer_password: your-app-password
    mailer_encryption: tls
    mailer_from_email: noreply@example.com
    mailer_from_name: My Application
```

You can use these parameters in your Symfony mailer configuration:

```yaml
# config/packages/mailer.yaml
framework:
    mailer:
        dsn: '%env(resolve:MAILER_DSN)%'

# Or use the parameters directly:
# framework:
#     mailer:
#         transports:
#             main: 
#                 smtp://%mailer_user%:%mailer_password%@%mailer_host%:%mailer_port%
```

### SMTP Configuration Without User Entity Fields

If you don't want SMTP fields in your User entity, that's fine! The installer will:
- Still show the SMTP configuration step
- Save settings to `config/smtp.yaml` only
- Skip trying to save to the User entity

The installer gracefully handles missing fields - no errors will occur.

---

## Development

### Build Assets for Development

```bash
cd assets
npm run dev
```

This starts Vite dev server on `http://localhost:3000`.

### Build for Production

```bash
cd assets
npm run build
```

---

## API Endpoints

The bundle exposes the following API endpoints:

- `GET /install/api/system-check` - Check system requirements
- `POST /install/api/database-config` - Save database configuration
- `POST /install/api/install-tables` - Create database tables
- `POST /install/api/create-admin` - Create admin user
- `POST /install/api/smtp-config` - Save SMTP configuration (optional)
- `POST /install/api/app-config` - Save app configuration
- `GET /install/api/status` - Get installation status

---

## Security

### After Installation

1. **Disable Installer After Installation** (Optional but recommended)
   
   Update your `config/routes.yaml` to conditionally load the installer:
   ```yaml
   # Only load installer if not yet completed
   installer:
       resource:
           path: ../vendor/webberdoocom/installer-bundle/src/Controller/
           namespace: Webberdoo\InstallerBundle\Controller
       type: attribute
       when: '@=!file_exists(parameter("kernel.project_dir") ~ "/var/install_completed")'
   ```
   
   This prevents access to the installer after installation is complete.

2. **Installation Marker**
   
   The bundle creates a marker file at `var/install_completed` to prevent reinstallation.
   Delete this file if you need to run the installer again.

---

## Customization

### Custom Installation Steps

Extend the bundle services to add custom installation logic:

```php
namespace App\Service;

use Webberdoo\InstallerBundle\Service\SchemaInstaller;

class CustomSchemaInstaller extends SchemaInstaller
{
    public function install(array $dbCredentials): array
    {
        $result = parent::install($dbCredentials);
        
        // Add custom logic here
        // e.g., seed default data
        
        return $result;
    }
}
```

### Custom UI

The React components are modular and can be extended or replaced:

```jsx
// assets/src/components/CustomSystemCheck.jsx
import SystemCheck from './SystemCheck';

function CustomSystemCheck(props) {
    return (
        <div className="custom-wrapper">
            <SystemCheck {...props} />
            {/* Add custom content */}
        </div>
    );
}
```

---

## Troubleshooting

### Assets Not Loading

```bash
# Clear Symfony cache
php bin/console cache:clear

# Reinstall assets
php bin/console assets:install --symlink
```

### Database Connection Fails

- Verify database credentials
- Ensure database exists
- Check MySQL/MariaDB service is running
- Verify user has proper permissions

### Installation Marker Issues

```bash
# Remove marker to reinstall
rm var/install_completed
```

---

## Requirements

- PHP 8.2 or higher
- Symfony 7.0 or higher
- Node.js 18+ (for building assets)
- MySQL 8.0+ or MariaDB 10.6+

---

## License

MIT License. See LICENSE file for details.

---

## Support

For issues and questions, please open an issue on GitHub or contact support@webberdoo.com.
