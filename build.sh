#!/bin/bash

# Webberdoo Installer Bundle - Build Script
# This script builds the React frontend assets

set -e

echo "🚀 Building Webberdoo Installer Bundle Assets..."
echo ""

# Navigate to assets directory
cd "$(dirname "$0")/assets"

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing Node.js dependencies..."
    npm install
    echo ""
fi

# Build assets
echo "🔨 Building frontend assets..."
npm run build
echo ""

# Navigate back to bundle root
cd ..

echo "✅ Build complete!"
echo ""
echo "Assets have been built to: src/Resources/public/"
echo ""
echo "Next steps:"
echo "1. Run 'php bin/console assets:install --symlink' in your Symfony project"
echo "2. Navigate to /install in your browser"
echo ""
