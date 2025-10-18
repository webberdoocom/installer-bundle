# Changelog

All notable changes to the Webberdoo Installer Bundle will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-12

### Added
- Initial release of Webberdoo Installer Bundle
- Web-based installation wizard with 5 steps
- React + Tailwind CSS v4 frontend
- System requirements checker
- Database configuration with connection testing
- Automatic schema installation for configured entities
- Admin user creation with password strength indicator
- Application configuration step
- Installation status tracking and resume capability
- Configurable entity support
- Dynamic admin user field mapping
- Safe schema updates (create-only, no drops)
- Installation completion marker
- Comprehensive documentation
- Quick start guide
- Example configuration files

### Features
- **Modern UI**: Clean, responsive interface built with React and Tailwind CSS v4
- **Step Navigation**: Progress indicator with step validation
- **Error Handling**: Comprehensive error messages and validation
- **Configurable**: Highly customizable via YAML configuration
- **Standalone**: Works without existing Symfony configuration during installation
- **Safe**: Create-only schema updates, password hashing, validation
- **Developer Friendly**: Clear documentation and examples

### Security
- Password hashing using Symfony's PasswordHasher
- Email validation
- Database connection testing before saving credentials
- Installation marker to prevent reinstallation
- No sensitive data in frontend code

### Requirements
- PHP 8.2 or higher
- Symfony 7.0 or higher
- Node.js 18+ (for building assets)
- MySQL 8.0+ or MariaDB 10.6+
- Required PHP extensions: ctype, iconv, pcre, session, simplexml, tokenizer, pdo_mysql, mbstring, json, intl
- Recommended PHP extensions: curl, zip, gd

---

## [2.2.3] - 2025-01-17

### Fixed
- **Critical:** Built assets now included in repository for proper Composer distribution
- Fixed login redirect issue on completion screen (was redirecting to `http://login/`)
- Login redirect now works correctly with subfolders and all domain configurations
- Added debug logging to help troubleshoot redirect issues

### Changed
- Updated `.gitignore` to track `src/Resources/public/` directory
- Updated README to clarify that assets are pre-built and included

---

## [2.2.2] - 2025-01-17

### Added
- **Optional SMTP Configuration Step**: New step 5 in the installer for configuring email settings
- SMTP fields support in user entity (smtpHost, smtpPort, smtpUsername, smtpPassword, smtpEncryption, smtpFromEmail, smtpFromName)
- `SmtpConfigWriter` service to handle SMTP configuration persistence
- SMTP configuration saved to both user entity and `config/smtp.yaml`
- "Skip for Now" option for SMTP configuration
- Auto-detection of SMTP fields in user entities
- Smart validation for SMTP form (only validates when fields are filled)

### Changed
- Updated step count from 5 to 6 steps
- Enhanced `UserEntityDetector` to detect SMTP-related fields
- Updated `InstallationStatusChecker` to track SMTP configuration status
- Modified frontend step progression to include SMTP step after admin user creation

### Improved
- Better user experience with optional configuration steps
- Flexible SMTP field naming (supports camelCase and snake_case variations)

---

## [2.3.0] - 2025-01-17

### Changed
- **Breaking:** Template namespace updated to use `@Installer` instead of hardcoded paths
- Improved Twig namespace registration via `prepend()` in Extension
- Better compatibility with Composer path repositories for local development
- Enhanced bundle auto-loading support

### Fixed
- Fixed template loading issues when using path repositories
- Proper Twig paths configuration for bundle templates

### Improved
- Better development workflow with local path repository support
- Clearer documentation on local development setup

---

## [2.3.1] - 2025-01-18

### Fixed
- **Critical:** Fixed template path resolution issue by correcting `getPath()` method in `InstallerBundle`
- Template loading now works correctly on production servers
- Resolved "Unable to find template install/install.html.twig" error

---

## [Unreleased]

### Planned Features
- PostgreSQL support
- SQLite support
- Multi-language support
- Custom theme configuration
- Email testing step
- Database backup before installation
- Migration from existing installations
