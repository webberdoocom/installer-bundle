# Installer Bundle - Asset Loading Error Diagnosis

## The Errors You're Seeing

```
❌ CSS: MIME type 'text/html' instead of 'text/css'
❌ JS: 500 Internal Server Error  
❌ favicon.svg: 500 Internal Server Error
```

## Root Cause: Assets Not Built

Your bundle's static assets (CSS, JS, SVG) **haven't been built yet**. Here's what's happening:

### Current State
```
installer-bundle/
├── public/              ← Vite builds here
│   ├── css/            ← EMPTY (0 items) ❌
│   ├── js/             ← EMPTY (0 items) ❌
│   └── .vite/          ← EMPTY (0 items) ❌
│
└── src/Resources/public/  ← Symfony expects assets here
    ├── css/               ← EMPTY (0 items) ❌
    ├── js/                ← EMPTY (0 items) ❌
    └── .vite/             ← EMPTY (0 items) ❌
```

### Why You Get MIME Type Errors

When your browser requests:
```
https://webberdoo.com/etsy-demos/actonepager/bundles/installer/css/app.css
```

Your webserver returns:
1. **404 Not Found** (file doesn't exist)
2. Symfony's error handler catches it
3. Returns an **HTML error page** instead
4. Browser rejects HTML as CSS → **MIME type error**

Same for JS (500 error) and favicon.svg (500 error).

---

## How to Fix This

You need to build the assets AND ensure they're in the correct location.

### Step 1: Build the Assets (Run in Bundle Directory)

**Windows:**
```batch
cd e:\websites\webberdoo\1.2025\1.Bundles\installer-bundle
.\build.bat
```

**Linux/Mac:**
```bash
cd /path/to/installer-bundle
./build.sh
```

**Or manually:**
```bash
cd assets
npm install
npm run build
```

This will build to `public/` directory (per vite.config.js).

---

### Step 2: The Vite Config Issue

**Current vite.config.js builds to:**
```javascript
outDir: '../public',  // → installer-bundle/public/
```

**But Symfony bundles need assets in:**
```
src/Resources/public/  // ← Symfony convention
```

### Fix Option A: Update Vite Config (Recommended)

Edit `assets/vite.config.js`:

```javascript
build: {
    outDir: '../src/Resources/public',  // ← Changed from '../public'
    emptyOutDir: true,
    // ... rest stays the same
}
```

Then rebuild:
```bash
cd assets
npm run build
```

### Fix Option B: Copy Assets Manually

After building:
```bash
# From installer-bundle root
cp -r public/* src/Resources/public/
```

Or Windows:
```batch
xcopy /E /Y public src\Resources\public\
```

---

### Step 3: Install Assets to Your App

In your **actonepager** app (not the bundle):

```bash
php bin/console cache:clear
php bin/console assets:install --symlink public
```

This creates:
```
actonepager/public/bundles/installer/ → symlink to vendor/.../src/Resources/public/
```

---

### Step 4: Verify on Your Webhost

After deploying to your webhost, ensure these files exist:

```
https://webberdoo.com/etsy-demos/actonepager/bundles/installer/css/app.css
https://webberdoo.com/etsy-demos/actonepager/bundles/installer/js/app.js
https://webberdoo.com/etsy-demos/actonepager/bundles/installer/favicon.svg
```

**If they 404:**
- Your host doesn't support symlinks → Use `assets:install` without `--symlink` flag
- Or manually copy the files

---

## Production Build Checklist

✅ **Before deploying to production:**

1. **Build assets in bundle:**
   ```bash
   cd installer-bundle
   ./build.bat  # or ./build.sh
   ```

2. **Verify assets exist:**
   ```bash
   ls -la src/Resources/public/css/
   ls -la src/Resources/public/js/
   ```
   Should show `app.css`, `app.js`, and `favicon.svg`

3. **Install in your app:**
   ```bash
   cd actonepager
   php bin/console assets:install public
   ```

4. **Verify in app:**
   ```bash
   ls -la public/bundles/installer/
   ```
   Should have `css/`, `js/` folders with files

5. **Upload to webhost:**
   - Include `public/bundles/installer/*` in your deployment

---

## Understanding the Asset Flow

```
Development:
assets/src/main.jsx
    ↓ (npm run build)
installer-bundle/public/js/app.js          ← Vite output
    ↓ (should be in)
installer-bundle/src/Resources/public/js/app.js  ← Symfony convention
    ↓ (php bin/console assets:install)
actonepager/public/bundles/installer/js/app.js   ← Your app
    ↓ (deploy to webhost)
https://webberdoo.com/etsy-demos/actonepager/bundles/installer/js/app.js
```

---

## Why This Happened

The bundle's build process has a **misconfiguration**:

1. ❌ **Vite builds to** `public/` 
2. ✅ **Should build to** `src/Resources/public/`
3. ❌ **Build scripts say** "Assets have been built to: src/Resources/public/" (incorrect)
4. ❌ **Empty directories exist** but no actual files

---

## Quick Fix Commands

```bash
# 1. Fix vite config
cd e:\websites\webberdoo\1.2025\1.Bundles\installer-bundle\assets
# Edit vite.config.js: change outDir to '../src/Resources/public'

# 2. Build
npm run build

# 3. Verify
dir ..\src\Resources\public\css
dir ..\src\Resources\public\js

# 4. Install in app
cd ..\..\..\..\actonepager
php bin/console assets:install public

# 5. Deploy and test
```

---

## Testing Locally First

Before uploading to webhost:

```bash
# In actonepager directory
php bin/console cache:clear
symfony server:start
# or
php -S localhost:8000 -t public

# Visit: http://localhost:8000/install
```

If it works locally but not on production:
- Check file permissions on host
- Verify symlinks are supported (or use hard copy)
- Check .htaccess / web server config

---

## Next Steps

1. **Fix the vite config** (Option A above)
2. **Build the assets** 
3. **Verify files exist** in `src/Resources/public/`
4. **Install assets** in your actonepager app
5. **Deploy to webhost**
6. **Test** the `/install` route

After this, your installer should load correctly! 🎉
