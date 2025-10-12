# Webberdoo Installer Bundle

A complete, reusable Symfony installer bundle with a modern React + Tailwind CSS v4 frontend. This bundle provides a web-based installation wizard for Symfony applications with configurable entities, database setup, and admin user creation.

## Features

✅ **Modern UI** - React with Tailwind CSS v4  
✅ **Step-by-step Installation** - System requirements, database, tables, admin user, app config  
✅ **Fully Configurable** - Configure entities, admin user fields, and custom parameters  
✅ **Safe Schema Installation** - Only creates tables, never drops existing ones  
✅ **Dynamic Entity Support** - Add any Doctrine entities to install  
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

**Note:** Routes are automatically loaded. No need to modify `config/routes.yaml`.

### 3. Configure the Bundle

Create `config/packages/installer.yaml`:

```yaml
installer:
    # Entities to install (required)
    entities:
        - App\Entity\User
        - App\Entity\Account
        - App\Entity\Transaction
        # Add all your entities here
    
    # Admin user configuration
    admin_user:
        entity_class: App\Entity\User
        email_field: email              # Property name for email
        password_field: password        # Property name for password
        roles_field: roles              # Property name for roles
        full_name_field: fullName       # Property name for full name
        is_active_field: isActive       # Property name for active status
        admin_roles:
            - ROLE_ADMIN
    
    # Database configuration
    database:
        config_path: '%kernel.project_dir%/config/db.yaml'
        driver: pdo_mysql
        charset: utf8mb4
    
    # System requirements
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
        recommended_extensions:
            - curl
            - zip
            - gd
    
    # Application configuration
    app_config:
        config_path: '%kernel.project_dir%/config/app_config.yaml'
        parameters: []  # Add custom parameters if needed
    
    # Installation marker
    install_marker_path: '%kernel.project_dir%/var/install_completed'
    
    # Route prefix
    route_prefix: '/install'
```

### 4. Build Frontend Assets

```bash
cd vendor/webberdoocom/installer-bundle/assets
npm install
npm run build
```

The assets will be built to `src/Resources/public/`.

### 5. Install Assets to Public Directory

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
   - Writes to `config/db.yaml`

3. **Install Tables**
   - Creates all configured entity tables
   - Sets up indexes and foreign keys
   - Safe mode - only creates, never drops

4. **Create Admin User**
   - Create administrator account
   - Password strength indicator
   - Email validation

5. **Application Configuration**
   - Set base URL and path
   - Configure custom parameters
   - Creates installation marker

---

## Configuration Examples

### Adding Custom Parameters

```yaml
installer:
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

### Custom User Entity

If your User entity uses different field names:

```yaml
installer:
    admin_user:
        entity_class: App\Entity\CustomUser
        email_field: emailAddress      # Instead of 'email'
        password_field: hashedPassword # Instead of 'password'
        roles_field: userRoles         # Instead of 'roles'
        full_name_field: name          # Instead of 'fullName'
        is_active_field: active        # Instead of 'isActive'
```

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
- `POST /install/api/app-config` - Save app configuration
- `GET /install/api/status` - Get installation status

---

## Security

### After Installation

1. **Remove Installer Route** (Optional but recommended)
   
   Add to `config/routes.yaml`:
   ```yaml
   # Disable installer after installation
   installer:
       resource: '@InstallerBundle/Resources/config/routes.yaml'
       when: '@=!file_exists(parameter("kernel.project_dir") ~ "/var/install_completed")'
   ```

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
