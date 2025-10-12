# API Documentation

This document describes all API endpoints available in the Webberdoo Installer Bundle.

## Base URL

All API endpoints are prefixed with `/install/api`

---

## Endpoints

### 1. System Requirements Check

**GET** `/install/api/system-check`

Checks system requirements including PHP version, extensions, and directory permissions.

#### Response

```json
{
  "success": true,
  "checks": {
    "php_version": {
      "name": "PHP Version",
      "required": "8.2.0 or higher",
      "current": "8.3.0",
      "status": true,
      "critical": true
    },
    "ext_pdo_mysql": {
      "name": "Pdo_mysql Extension",
      "required": "Required",
      "current": "Installed",
      "status": true,
      "critical": true
    }
    // ... more checks
  },
  "all_passed": true,
  "critical_failed": false,
  "can_proceed": true
}
```

---

### 2. Database Configuration

**POST** `/install/api/database-config`

Saves database configuration and tests connection.

#### Request Body

```json
{
  "host": "localhost",
  "port": 3306,
  "db_name": "my_database",
  "db_user": "root",
  "password": "secret"
}
```

#### Response Success

```json
{
  "success": true,
  "message": "Database configuration saved successfully"
}
```

#### Response Error

```json
{
  "success": false,
  "message": "Connection failed: Access denied for user 'root'@'localhost'"
}
```

#### Status Codes

- `200` - Configuration saved successfully
- `400` - Validation error or connection failed
- `500` - Server error

---

### 3. Install Database Tables

**POST** `/install/api/install-tables`

Creates database tables for all configured entities.

#### Request Body

No body required. Database credentials are read from saved configuration.

#### Response Success

```json
{
  "success": true,
  "message": "Database tables created successfully",
  "entities_installed": 18
}
```

#### Response Error

```json
{
  "success": false,
  "message": "Error creating tables: SQLSTATE[42S01]: Base table or view already exists"
}
```

#### Status Codes

- `200` - Tables created successfully
- `400` - Database configuration not found
- `500` - Schema creation error

---

### 4. Create Admin User

**POST** `/install/api/create-admin`

Creates the administrator user account.

#### Request Body

```json
{
  "email": "admin@example.com",
  "password": "SecurePassword123",
  "fullName": "John Doe"
}
```

#### Validation Rules

- `email`: Required, must be valid email format
- `password`: Required, minimum 6 characters
- `fullName`: Required, non-empty string

#### Response Success

```json
{
  "success": true,
  "message": "Admin user created successfully"
}
```

#### Response Error

```json
{
  "success": false,
  "message": "Admin user with this email already exists"
}
```

#### Status Codes

- `200` - User created successfully
- `400` - Validation error
- `409` - User already exists
- `500` - Server error

---

### 5. Save Application Configuration

**POST** `/install/api/app-config`

Saves application configuration and marks installation as complete.

#### Request Body

```json
{
  "base_url": "https://example.com",
  "base_path": "/",
  "CUSTOM_PARAM": "value"
}
```

#### Response Success

```json
{
  "success": true,
  "message": "Application configuration saved successfully",
  "config": {
    "app.base_url": "https://example.com",
    "app.base_path": "",
    "app.assets_base_url": "https://example.com/public",
    "CUSTOM_PARAM": "value"
  }
}
```

#### Response Error

```json
{
  "success": false,
  "message": "Error saving configuration: Permission denied"
}
```

#### Status Codes

- `200` - Configuration saved successfully
- `400` - Missing required fields
- `500` - Server error

---

### 6. Installation Status

**GET** `/install/api/status`

Checks the current installation status.

#### Response

```json
{
  "success": true,
  "status": {
    "database_config": true,
    "database_tables": true,
    "admin_user": true,
    "app_config": true
  },
  "completed": true
}
```

#### Status Flags

- `database_config`: Database credentials configured and valid
- `database_tables`: Database tables created
- `admin_user`: Admin user exists
- `app_config`: Application configured and installation marker exists
- `completed`: All steps completed (all flags are true)

---

## Error Handling

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "message": "Human-readable error message"
}
```

### Common HTTP Status Codes

- `200` - Success
- `400` - Bad Request (validation error, missing data)
- `409` - Conflict (resource already exists)
- `500` - Internal Server Error

---

## Request/Response Format

### Content Type

All requests and responses use `application/json`.

### Headers

```
Content-Type: application/json
Accept: application/json
```

---

## Example Usage

### Using cURL

```bash
# System check
curl -X GET http://localhost:8000/install/api/system-check

# Database configuration
curl -X POST http://localhost:8000/install/api/database-config \
  -H "Content-Type: application/json" \
  -d '{
    "host": "localhost",
    "port": 3306,
    "db_name": "mydb",
    "db_user": "root",
    "password": "secret"
  }'

# Install tables
curl -X POST http://localhost:8000/install/api/install-tables

# Create admin
curl -X POST http://localhost:8000/install/api/create-admin \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "SecurePass123",
    "fullName": "Admin User"
  }'

# App config
curl -X POST http://localhost:8000/install/api/app-config \
  -H "Content-Type: application/json" \
  -d '{
    "base_url": "http://localhost:8000",
    "base_path": "/"
  }'

# Check status
curl -X GET http://localhost:8000/install/api/status
```

### Using JavaScript (Axios)

```javascript
import axios from 'axios';

// System check
const systemCheck = await axios.get('/install/api/system-check');

// Database config
const dbConfig = await axios.post('/install/api/database-config', {
  host: 'localhost',
  port: 3306,
  db_name: 'mydb',
  db_user: 'root',
  password: 'secret'
});

// Install tables
const install = await axios.post('/install/api/install-tables');

// Create admin
const admin = await axios.post('/install/api/create-admin', {
  email: 'admin@example.com',
  password: 'SecurePass123',
  fullName: 'Admin User'
});

// App config
const config = await axios.post('/install/api/app-config', {
  base_url: 'http://localhost:8000',
  base_path: '/'
});

// Status
const status = await axios.get('/install/api/status');
```

---

## Rate Limiting

Currently, no rate limiting is applied by default. For production use, consider implementing rate limiting on these endpoints to prevent abuse.

See [Security Guide](SECURITY.md#api-endpoint-security) for recommendations.

---

## CORS

If you need to access the installer API from a different domain during development, configure CORS in your Symfony application:

```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    paths:
        '^/install/api':
            allow_origin: ['*']
            allow_methods: ['GET', 'POST']
            allow_headers: ['Content-Type']
```

**Note:** Only enable CORS if absolutely necessary and restrict origins in production.
