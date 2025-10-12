# Contributing to Webberdoo Installer Bundle

Thank you for considering contributing to the Webberdoo Installer Bundle! This document provides guidelines for contributing to this project.

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- Git

### Setting Up Development Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/webberdoo/installer-bundle.git
   cd installer-bundle
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   cd assets
   npm install
   ```

4. **Start development server**
   ```bash
   npm run dev
   ```

## Project Structure

```
installer/
├── src/
│   ├── Controller/          # Symfony controllers
│   ├── Service/            # Business logic services
│   ├── DependencyInjection/ # Bundle configuration
│   └── Resources/
│       ├── config/         # Service and route configuration
│       ├── views/          # Twig templates
│       └── public/         # Built assets (generated)
├── assets/
│   └── src/
│       ├── components/     # React components
│       ├── App.jsx         # Main React app
│       └── app.css         # Tailwind styles
├── config/                 # Example configurations
└── tests/                  # Unit and integration tests
```

## Development Workflow

### Making Changes

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow PSR-12 coding standards for PHP
   - Use ESLint rules for JavaScript/React
   - Write tests for new features

3. **Test your changes**
   ```bash
   # PHP tests
   composer test
   
   # Build frontend
   cd assets && npm run build
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: description of your changes"
   ```

### Commit Message Convention

We follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting, etc.)
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

### Code Style

#### PHP
- Follow PSR-12 coding standards
- Use type hints
- Document public methods with PHPDoc
- Keep methods focused and small

#### JavaScript/React
- Use functional components with hooks
- Keep components small and focused
- Use meaningful variable names
- Add comments for complex logic

#### CSS/Tailwind
- Use Tailwind utility classes
- Define custom components in `@layer components`
- Follow mobile-first responsive design

## Testing

### Running Tests

```bash
# PHP unit tests
composer test

# PHP code style check
composer cs-check

# PHP code style fix
composer cs-fix
```

### Writing Tests

- Write unit tests for services
- Write integration tests for controllers
- Test edge cases and error scenarios

## Pull Request Process

1. **Update documentation** if needed
2. **Add/update tests** for your changes
3. **Ensure all tests pass**
4. **Update CHANGELOG.md** with your changes
5. **Create a pull request** with a clear description

### Pull Request Checklist

- [ ] Code follows project style guidelines
- [ ] Tests added/updated and passing
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] No console errors or warnings
- [ ] Tested in development environment

## Adding New Features

### Adding a New Installation Step

1. Create React component in `assets/src/components/`
2. Add API endpoint in `src/Controller/Api/InstallerApiController.php`
3. Add service if needed in `src/Service/`
4. Update `App.jsx` to include the new step
5. Update documentation

### Adding Configuration Options

1. Update `src/DependencyInjection/Configuration.php`
2. Update `config/packages/installer.yaml.example`
3. Update services to use new configuration
4. Update README.md with new options

## Reporting Issues

### Bug Reports

Include:
- Description of the bug
- Steps to reproduce
- Expected behavior
- Actual behavior
- Environment details (PHP version, OS, etc.)
- Screenshots if applicable

### Feature Requests

Include:
- Description of the feature
- Use case / problem it solves
- Proposed implementation (optional)

## Questions?

Feel free to open an issue for questions or reach out to support@webberdoo.com

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
