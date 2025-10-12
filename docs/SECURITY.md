# Security Guide

This document outlines security considerations when using the Webberdoo Installer Bundle.

## Installation Security

### 1. Protect the Installer Route

**After installation is complete**, the installer should be disabled to prevent unauthorized reinstallation.

#### Method 1: Route Condition (Recommended)

Add to `config/routes.yaml`:

```yaml
# Only allow installer if not already installed
installer:
    resource: '@InstallerBundle/Resources/config/routes.yaml'
    when: '@=!file_exists(parameter("kernel.project_dir") ~ "/var/install_completed")'
```

#### Method 2: Remove Bundle in Production

In `config/bundles.php`, only load the bundle in dev environment:

```php
<?php

return [
    // ... other bundles
    Webberdoo\InstallerBundle\InstallerBundle::class => ['dev' => true],
];
```

#### Method 3: Firewall Protection

Add to `config/packages/security.yaml`:

```yaml
security:
    firewalls:
        installer:
            pattern: ^/install
            security: false
            # Or require authentication:
            # provider: your_user_provider
            # http_basic: ~
```

### 2. Installation Marker File

The bundle creates a marker file at `var/install_completed` to prevent reinstallation.

**To reinstall** (e.g., in development):
```bash
rm var/install_completed
```

**Never commit** this file to version control. It's in `.gitignore` by default.

### 3. Database Credentials

Database credentials are stored in `config/db.yaml`.

**Security measures:**
- Add `config/db.yaml` to `.gitignore`
- Use environment variables in production
- Restrict file permissions: `chmod 600 config/db.yaml`

**Example using environment variables:**

```yaml
# config/db.yaml
parameters:
    dbname: '%env(DATABASE_NAME)%'
    host: '%env(DATABASE_HOST)%'
    port: '%env(DATABASE_PORT)%'
    user: '%env(DATABASE_USER)%'
    password: '%env(DATABASE_PASSWORD)%'
```

### 4. Admin Password Security

**During installation:**
- Minimum 6 characters required
- Password strength indicator shown
- Passwords are hashed using Symfony's PasswordHasher
- Uses bcrypt with cost factor 13 by default

**Recommendations:**
- Use strong passwords (12+ characters)
- Include uppercase, lowercase, numbers, and symbols
- Don't reuse passwords from other services

**Password hashing configuration** (`config/packages/security.yaml`):

```yaml
security:
    password_hashers:
        App\Entity\User:
            algorithm: bcrypt
            cost: 13
```

### 5. CSRF Protection

For production installations, consider adding CSRF tokens to forms.

**Enable CSRF in configuration:**

```yaml
# config/packages/framework.yaml
framework:
    csrf_protection: ~
```

Then update the installer forms to include CSRF tokens.

### 6. HTTPS in Production

**Always use HTTPS** in production environments.

Configure redirect in `config/routes.yaml`:

```yaml
installer:
    resource: '@InstallerBundle/Resources/config/routes.yaml'
    schemes: [https]
```

Or use `.htaccess` / nginx configuration for automatic HTTPS redirect.

### 7. File Permissions

Ensure proper file permissions:

```bash
# Writable by web server
chmod 755 config/
chmod 755 var/

# Sensitive config files
chmod 600 config/db.yaml
chmod 600 config/app_config.yaml
```

### 8. Input Validation

The installer validates all inputs:

**Email validation:**
- Checks valid email format
- Converts to lowercase
- Trims whitespace

**Database credentials:**
- Tests connection before saving
- Validates required fields
- Prevents SQL injection via PDO prepared statements

**Passwords:**
- Minimum length validation
- Confirmation matching
- Immediate hashing (never stored in plain text)

### 9. Error Handling

**Production error handling:**

Ensure `APP_ENV=prod` in production to:
- Hide detailed error messages
- Prevent stack trace exposure
- Log errors securely

```bash
# .env
APP_ENV=prod
APP_DEBUG=0
```

### 10. API Endpoint Security

**Current status:** Installer API endpoints are public during installation.

**After installation:** Disable or protect these endpoints using route conditions.

**Rate limiting** (optional):

Consider adding rate limiting to prevent brute force:

```yaml
# config/packages/framework.yaml
framework:
    rate_limiter:
        installer:
            policy: 'sliding_window'
            limit: 10
            interval: '1 minute'
```

Then apply to routes:

```php
#[Route('/install/api/create-admin', name: 'installer_api_create_admin')]
#[RateLimit(limiter: 'installer')]
public function createAdmin(Request $request): JsonResponse
```

---

## Security Checklist

### Before Going Live

- [ ] HTTPS enabled
- [ ] Installer route disabled/protected
- [ ] `APP_ENV=prod` and `APP_DEBUG=0`
- [ ] Database credentials in environment variables
- [ ] Sensitive config files not in version control
- [ ] File permissions properly set
- [ ] Strong admin password set
- [ ] CSRF protection enabled (if needed)
- [ ] Error logging configured
- [ ] Security headers configured

### After Installation

- [ ] Installation marker exists
- [ ] Installer route inaccessible
- [ ] Login page accessible
- [ ] Admin login works
- [ ] No console errors
- [ ] No sensitive data exposed

---

## Vulnerability Reporting

If you discover a security vulnerability, please email security@webberdoo.com.

**Do not** open a public issue for security vulnerabilities.

We will respond within 48 hours and work with you to address the issue.

---

## Regular Security Maintenance

1. **Keep dependencies updated:**
   ```bash
   composer update
   npm update
   ```

2. **Run security audits:**
   ```bash
   composer audit
   npm audit
   ```

3. **Monitor logs** for suspicious activity

4. **Backup database** regularly

5. **Test security measures** periodically

---

## Resources

- [Symfony Security Documentation](https://symfony.com/doc/current/security.html)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
