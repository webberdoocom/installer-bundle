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

## [Unreleased]

### Planned Features
- PostgreSQL support
- SQLite support
- Multi-language support
- Custom theme configuration
- Email testing step
- Database backup before installation
- Migration from existing installations
