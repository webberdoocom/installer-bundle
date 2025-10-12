# Customization Guide

This guide shows you how to customize the Webberdoo Installer Bundle to fit your specific needs.

## Table of Contents

1. [Customizing UI/Styling](#customizing-ui-styling)
2. [Adding Custom Steps](#adding-custom-steps)
3. [Custom Services](#custom-services)
4. [Custom Validation](#custom-validation)
5. [Theming](#theming)

---

## Customizing UI/Styling

### Modify Tailwind Configuration

Create a custom Tailwind config in your project:

```javascript
// assets/tailwind.config.custom.js
export default {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        }
      }
    }
  }
}
```

### Override Component Styles

Create custom CSS in your project:

```css
/* assets/custom-installer.css */
@import "tailwindcss";

@layer components {
  .btn-primary {
    @apply bg-purple-600 hover:bg-purple-700;
  }
  
  .card {
    @apply shadow-2xl border-2 border-purple-100;
  }
}
```

### Customize React Components

Extend or wrap existing components:

```jsx
// assets/src/components/CustomSystemCheck.jsx
import React from 'react';
import SystemCheck from '@webberdoo/installer-bundle/assets/src/components/SystemCheck';

function CustomSystemCheck(props) {
  return (
    <div className="custom-wrapper">
      <div className="mb-4 p-4 bg-blue-100 rounded">
        <h3 className="font-bold">Important Notice</h3>
        <p>Please ensure your server meets all requirements.</p>
      </div>
      
      <SystemCheck {...props} />
    </div>
  );
}

export default CustomSystemCheck;
```

---

## Adding Custom Steps

### Step 1: Create React Component

```jsx
// assets/src/components/CustomStep.jsx
import React, { useState } from 'react';
import axios from 'axios';

function CustomStep({ onNext, onBack }) {
  const [formData, setFormData] = useState({
    customField: ''
  });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      await axios.post('/install/api/custom-step', formData);
      onNext();
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <h2 className="text-2xl font-bold mb-6">Custom Step</h2>
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          value={formData.customField}
          onChange={(e) => setFormData({ customField: e.target.value })}
          className="input"
        />
        <button type="submit" className="btn btn-primary">
          Continue
        </button>
      </form>
    </div>
  );
}

export default CustomStep;
```

### Step 2: Add to Main App

```jsx
// assets/src/App.jsx
import CustomStep from './components/CustomStep';

const STEPS = [
  { id: 1, name: 'System Requirements', key: 'system_check' },
  { id: 2, name: 'Database Configuration', key: 'database_config' },
  { id: 3, name: 'Custom Step', key: 'custom_step' }, // New step
  // ... other steps
];

function App() {
  // ... existing code
  
  return (
    // ... existing JSX
    {currentStep === 3 && (
      <CustomStep onNext={handleNext} onBack={handleBack} />
    )}
  );
}
```

### Step 3: Create API Endpoint

```php
<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CustomInstallerController extends AbstractController
{
    #[Route('/install/api/custom-step', name: 'install_custom_step', methods: ['POST'])]
    public function customStep(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Process custom data
        // ...
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Custom step completed'
        ]);
    }
}
```

---

## Custom Services

### Extending Existing Services

```php
<?php

namespace App\Service;

use Webberdoo\InstallerBundle\Service\SchemaInstaller as BaseSchemaInstaller;

class CustomSchemaInstaller extends BaseSchemaInstaller
{
    public function install(array $dbCredentials): array
    {
        // Call parent method
        $result = parent::install($dbCredentials);
        
        if ($result['success']) {
            // Add custom logic
            $this->seedDefaultData();
        }
        
        return $result;
    }
    
    private function seedDefaultData(): void
    {
        // Seed categories, settings, etc.
    }
}
```

### Register Custom Service

```yaml
# config/services.yaml
services:
    App\Service\CustomSchemaInstaller:
        decorates: Webberdoo\InstallerBundle\Service\SchemaInstaller
        arguments:
            $entities: '%installer.entities%'
            $dbConfig: '%installer.database%'
            $projectDir: '%kernel.project_dir%'
```

---

## Custom Validation

### Custom System Requirements

```php
<?php

namespace App\Service;

use Webberdoo\InstallerBundle\Service\SystemRequirementsChecker as BaseChecker;

class CustomRequirementsChecker extends BaseChecker
{
    public function check(): array
    {
        $checks = parent::check();
        
        // Add custom checks
        $checks['custom_extension'] = [
            'name' => 'Custom Extension',
            'required' => 'Required for custom feature',
            'current' => extension_loaded('custom') ? 'Installed' : 'Not installed',
            'status' => extension_loaded('custom'),
            'critical' => true
        ];
        
        // Check custom directory exists
        $customDir = $this->projectDir . '/custom';
        $checks['custom_directory'] = [
            'name' => 'Custom Directory',
            'required' => 'Required',
            'current' => is_dir($customDir) ? 'Exists' : 'Missing',
            'status' => is_dir($customDir),
            'critical' => false
        ];
        
        return $checks;
    }
}
```

---

## Theming

### Create Custom Theme

```css
/* assets/src/themes/dark-theme.css */
@import "tailwindcss";

@theme {
  --color-primary: #8b5cf6;
  --color-primary-dark: #7c3aed;
  --color-background: #1f2937;
  --color-surface: #374151;
  --color-text: #f9fafb;
}

body {
  @apply bg-gray-800 text-gray-100;
}

.card {
  @apply bg-gray-700 text-white;
}

.btn-primary {
  @apply bg-purple-600 hover:bg-purple-700;
}
```

### Apply Theme

```jsx
// assets/src/main.jsx
import './themes/dark-theme.css'; // Import custom theme
import App from './App';
```

---

## Custom Configuration Parameters

### Add Custom Parameters to Config

```yaml
# config/packages/installer.yaml
installer:
    app_config:
        parameters:
            - name: APP_NAME
              label: Application Name
              type: text
              required: true
              default: 'My Application'
            
            - name: APP_TIMEZONE
              label: Application Timezone
              type: select
              required: true
              default: 'UTC'
              options:
                  - UTC
                  - America/New_York
                  - Europe/London
            
            - name: ENABLE_FEATURES
              label: Enable Beta Features
              type: checkbox
              required: false
              default: false
```

### Handle in Frontend

```jsx
// assets/src/components/AppConfig.jsx
function AppConfig({ onNext, onBack }) {
  const [formData, setFormData] = useState({
    base_url: window.location.origin,
    base_path: '/',
    APP_NAME: '',
    APP_TIMEZONE: 'UTC',
    ENABLE_FEATURES: false
  });
  
  // ... rest of component
}
```

---

## Custom Logos and Branding

### Add Logo to Header

```jsx
// assets/src/App.jsx
function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="bg-white shadow">
        <div className="max-w-6xl mx-auto px-4 py-6 flex items-center">
          <img src="/images/logo.png" alt="Logo" className="h-12 mr-4" />
          <div>
            <h1 className="text-3xl font-bold text-gray-900">
              My App Installer
            </h1>
            <p className="mt-2 text-gray-600">
              Welcome to the installation wizard
            </p>
          </div>
        </div>
      </div>
      {/* Rest of app */}
    </div>
  );
}
```

---

## Event Hooks

### Listen to Installation Events

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class InstallationCompleteListener
{
    public function __invoke(InstallationCompleteEvent $event): void
    {
        // Send notification
        // Create default data
        // Configure additional services
    }
}
```

---

## Tips

1. **Always test** customizations in development first
2. **Keep backup** of original files
3. **Document** your customizations
4. **Version control** your changes
5. **Check compatibility** after bundle updates

---

## Need Help?

- Check the main README.md for general documentation
- See QUICKSTART.md for basic setup
- Open an issue on GitHub for bug reports
- Email support@webberdoo.com for questions
