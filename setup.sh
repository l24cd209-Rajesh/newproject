#!/bin/bash

# YAICESS Innovation Conference 2025 - Setup Script
# This script will help you set up the registration system

echo "🚀 YAICESS Innovation Conference 2025 - Setup Script"
echo "=================================================="

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 7.4+ first."
    echo "   Ubuntu/Debian: sudo apt install php php-mysql php-curl php-mbstring"
    echo "   CentOS/RHEL: sudo yum install php php-mysql php-curl php-mbstring"
    exit 1
fi

echo "✅ PHP is installed: $(php -v | head -n1)"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "📦 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
else
    echo "✅ Composer is already installed: $(composer --version)"
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "⚙️  Creating environment configuration..."
    cp .env.example .env
    echo "📝 Please edit .env file with your actual configuration:"
    echo "   - Database credentials"
    echo "   - Razorpay API keys"
    echo "   - SMTP email settings"
else
    echo "✅ Environment file already exists"
fi

# Set proper file permissions
echo "🔐 Setting file permissions..."
chmod 755 *.php
chmod 644 *.html *.css *.js
chmod 600 .env

# Check if MySQL is running
if command -v mysql &> /dev/null; then
    echo "✅ MySQL is available"
    echo "📊 To set up the database, run: mysql -u root -p < users.sql"
else
    echo "⚠️  MySQL not found. Please install MySQL/MariaDB and import users.sql"
fi

echo ""
echo "🎉 Setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Edit .env file with your configuration"
echo "2. Import database: mysql -u root -p < users.sql"
echo "3. Configure web server to point to this directory"
echo "4. Access project.html in your browser"
echo ""
echo "🔐 Default admin credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo "   (Change after first login!)"
echo ""
echo "📖 For detailed instructions, see README.md"