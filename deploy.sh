#!/bin/bash

# AI Coach PHP Deployment Script for Cloudways
echo "🚀 Starting deployment process..."

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "⚠️  .env file not found. Creating from .env.example..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "✅ .env file created. Please update it with your production values."
        echo "📝 Don't forget to set your OPENAI_API_KEY!"
    else
        echo "❌ .env.example not found. Please create .env manually."
        exit 1
    fi
fi

# Install/Update composer dependencies for production
echo "📦 Installing Composer dependencies for production..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed!"
    exit 1
fi

# Set proper permissions
echo "🔐 Setting file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 public/

# Create necessary directories if they don't exist
echo "📁 Creating necessary directories..."
mkdir -p sessions
mkdir -p logs
mkdir -p cache

# Set permissions for writable directories
chmod 755 sessions logs cache 2>/dev/null || true

# Verify .htaccess is in public directory
if [ ! -f "public/.htaccess" ]; then
    echo "⚠️  .htaccess not found in public directory"
    if [ -f ".htaccess" ]; then
        echo "🔄 Moving .htaccess to public directory..."
        mv .htaccess public/
    fi
fi

# Check for sensitive files that shouldn't be deployed
echo "🔍 Checking for sensitive files..."
if [ -f ".env" ]; then
    echo "⚠️  .env file found - make sure it's not accessible via web!"
fi

# Display deployment checklist
echo ""
echo "✅ Deployment preparation complete!"
echo ""
echo "📋 Pre-deployment checklist:"
echo "  1. Update .env with production values"
echo "  2. Set OPENAI_API_KEY in .env"
echo "  3. Upload files to Cloudways (exclude .env initially)"
echo "  4. Create .env on server with production values"
echo "  5. Set document root to 'public' folder"
echo "  6. Enable SSL certificate"
echo "  7. Test the application"
echo ""
echo "📖 See DEPLOYMENT.md for detailed instructions"
echo ""
echo "🎉 Ready for deployment to Cloudways!" 