# Installer Bundle - User Installation Workflow

## When a User Installs Your Bundle via Composer

Here's the complete step-by-step process after running:
```bash
composer require webberdoocom/installer-bundle
```

---

## Step-by-Step Installation

### 1. Register the Bundle

**File:** `config/bundles.php`

```php
return [
    // ... other bundles
    Webberdoo\InstallerBundle\InstallerBundle::class => ['all' => true],
];
```

---

### 2. Register Routes

**File:** `config/routes.yaml`

```yaml
installer:
    resource:
        path: ../vendor/webberdoocom/installer-bundle/src/Controller/
        namespace: Webberdoo\InstallerBundle\Controller
    type: attribute
```

⚠️ **Important:** Don't add a route prefix - it's already defined in the controller as `/install`

---

### 3. Configure the Bundle

**File:** `config/packages/installer.yaml`

**Minimal setup (recommended):**
```yaml
installer:
    entities:
        - App\Entity\User
        # Add more entities as needed:
        # - App\Entity\Post
        # - App\Entity\Category
```

That's all! The bundle auto-detects everything else.

---

### 4. Build Frontend Assets ⚠️ CRITICAL

Since built assets are NOT included in the Composer package, users MUST build them:

```bash
# Navigate to the bundle's assets directory
cd vendor/webberdoocom/installer-bundle/assets

# Install dependencies
npm install

# Build the React frontend
npm run build
```

**What this does:**
- Compiles React + Tailwind CSS
- Builds to `vendor/webberdoocom/installer-bundle/src/Resources/public/`
- Creates `css/app.css`, `js/app.js`, and `favicon.svg`

---

### 5. Install Assets to Public Directory

```bash
# Back to project root
cd ../../../..

# Install bundle assets to public/bundles/
php bin/console assets:install --symlink
```

**What this does:**
- Creates symlink: `public/bundles/installer/` → `vendor/.../src/Resources/public/`
- Or copies files if `--symlink` is omitted

**Webhost note:** If your host doesn't support symlinks, use:
```bash
php bin/console assets:install public
```
(without `--symlink` flag - will copy files instead)

---

### 6. Access the Installer

Navigate to: `http://your-app.com/install`

---

## Complete Command Sequence

For copy-paste convenience:

```bash
# After composer require webberdoocom/installer-bundle

# 1. Build assets
cd vendor/webberdoocom/installer-bundle/assets
npm install
npm run build
cd ../../../..

# 2. Install to public
php bin/console cache:clear
php bin/console assets:install --symlink

# 3. Access installer
# Visit: http://your-app.com/install
```

---

## Alternative: Automated Setup Script

You could provide users with a setup script:

**File:** `vendor/webberdoocom/installer-bundle/install.sh` (Linux/Mac)

```bash
#!/bin/bash
echo "🚀 Setting up Installer Bundle..."

cd assets
npm install
npm run build
cd ../../../..

php bin/console cache:clear
php bin/console assets:install --symlink

echo "✅ Done! Visit /install to start installation"
```

**File:** `vendor/webberdoocom/installer-bundle/install.bat` (Windows)

```batch
@echo off
echo Setting up Installer Bundle...

cd assets
call npm install
call npm run build
cd ..\..\..\..

php bin/console cache:clear
php bin/console assets:install --symlink

echo Done! Visit /install to start installation
pause
```

Then users just run:
```bash
# Linux/Mac
./vendor/webberdoocom/installer-bundle/install.sh

# Windows
vendor\webberdoocom\installer-bundle\install.bat
```

---

## Why Users Must Build Assets

Built assets are NOT included in the Git repository because:

✅ **Keeps repo size small**  
✅ **No merge conflicts on generated files**  
✅ **Users build for their specific environment**  
✅ **Standard Composer package practice**

---

## Troubleshooting for Users

### Issue: "Assets not loading" / MIME type errors

**Cause:** Assets weren't built or installed properly.

**Solution:**
```bash
# Verify assets exist
ls -la vendor/webberdoocom/installer-bundle/src/Resources/public/

# Should show:
# css/app.css
# js/app.js
# favicon.svg

# If missing, rebuild:
cd vendor/webberdoocom/installer-bundle/assets
npm run build
cd ../../../..

# Reinstall:
php bin/console assets:install --symlink
```

### Issue: "Symlinks not working on webhost"

**Solution:** Use hard copy instead:
```bash
php bin/console assets:install public
# (without --symlink)
```

---

## Production Deployment

For production servers:

```bash
# 1. Build locally or in CI/CD
cd vendor/webberdoocom/installer-bundle/assets
npm install
npm run build

# 2. Deploy entire public/bundles/ directory
rsync -av public/bundles/ user@server:/path/to/app/public/bundles/

# OR use assets:install without symlink
php bin/console assets:install public
```

---

## Summary for Users

**After `composer require webberdoocom/installer-bundle`:**

1. ✅ Register bundle in `config/bundles.php`
2. ✅ Add routes in `config/routes.yaml`
3. ✅ Create config in `config/packages/installer.yaml`
4. ⚠️ **Build assets** (npm run build in vendor/.../assets/)
5. ⚠️ **Install assets** (php bin/console assets:install)
6. 🎉 Visit `/install`

The two build steps (#4 and #5) are critical and MUST be documented clearly!
