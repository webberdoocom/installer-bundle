@echo off
REM Webberdoo Installer Bundle - Build Script for Windows
REM This script builds the React frontend assets

echo.
echo Building Webberdoo Installer Bundle Assets...
echo.

cd /d "%~dp0assets"

if not exist "node_modules" (
    echo Installing Node.js dependencies...
    call npm install
    echo.
)

echo Building frontend assets...
call npm run build
echo.

cd ..

echo.
echo Build complete!
echo.
echo Assets have been built to: src/Resources/public/
echo.
echo Next steps:
echo 1. Run 'php bin/console assets:install --symlink' in your Symfony project
echo 2. Navigate to /install in your browser
echo.

pause
