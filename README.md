# YAICESS Innovation Conference 2025 - Registration System

A secure and modern event registration system with integrated payment processing for the YAICESS Innovation Conference 2025.

## 🚀 Features

- **Secure Registration**: Modern password hashing and input validation
- **Payment Integration**: Razorpay payment gateway integration
- **Admin Dashboard**: Manage registrations with DataTables
- **Email Notifications**: Automated confirmation emails
- **Responsive Design**: Mobile-friendly interface
- **Database Security**: Prepared statements to prevent SQL injection

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Payment**: Razorpay API
- **Email**: PHPMailer
- **Frontend**: HTML5, CSS3, JavaScript
- **Dependencies**: Composer

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)
- Razorpay account (for payment processing)
- SMTP email account (for notifications)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd yaicess-registration-system
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
# Create database and import schema
mysql -u root -p < users.sql
```

### 4. Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Edit .env file with your configuration
nano .env
```

### 5. Configure Environment Variables

Edit the `.env` file with your actual configuration:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=your_db_user
DB_PASSWORD=your_secure_password
DB_NAME=event_db

# Razorpay Configuration
RAZORPAY_KEY_ID=your_razorpay_key_id
RAZORPAY_KEY_SECRET=your_razorpay_key_secret

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME="YAICESS Innovation Conference 2025"

# Application Configuration
APP_DEBUG=false
APP_ENV=production
```

### 6. Set File Permissions
```bash
chmod 755 *.php
chmod 644 *.html *.css *.js
```

## 🔐 Security Features

### Fixed Security Issues:
- ✅ SQL Injection prevention with prepared statements
- ✅ Secure password hashing (bcrypt)
- ✅ Input validation and sanitization
- ✅ Environment variable configuration
- ✅ Session security improvements
- ✅ Error logging instead of displaying

### Admin Credentials:
- **Username**: admin
- **Password**: admin123

> **Important**: Change the default admin password after first login!

## 🗃️ Database Schema

### Tables:
- **participants**: User registration data
- **admin**: Administrator accounts
- **payments**: Payment transaction records

## 🎯 Usage

### User Registration Flow:
1. User visits `project.html`
2. Clicks "Register Now" → `userform.html`
3. Fills registration form → `startpayment.php`
4. Completes payment via Razorpay
5. Payment success → `paymentsuccess.php`
6. Receives confirmation email
7. Redirected to `thankyou.html`

### Admin Access:
1. Visit `admin_login.html`
2. Login with admin credentials
3. View registered participants in `admin_dashboard.php`

## 🔧 Configuration

### Payment Configuration:
- Register at [Razorpay](https://razorpay.com/)
- Get API keys from dashboard
- Add keys to `.env` file

### Email Configuration:
- Use Gmail App Password for SMTP
- Configure SMTP settings in `.env`

## 📝 File Structure

```
├── config.php              # Configuration loader
├── db_config.php           # Database connection
├── admin_dashboard.php     # Admin panel
├── admin_login.html        # Admin login form
├── register.php            # User registration handler
├── startpayment.php        # Payment initialization
├── paymentsuccess.php      # Payment success handler
├── userform.html           # Registration form
├── project.html            # Main landing page
├── thankyou.html           # Success page
├── logout.php              # Admin logout
├── users.sql               # Database schema
├── composer.json           # Dependencies
├── .env.example            # Environment template
└── README.md              # This file
```

## 🐛 Troubleshooting

### Common Issues:

1. **Payment Gateway Error**:
   - Check Razorpay API keys
   - Verify webhook configuration

2. **Email Not Sending**:
   - Verify SMTP credentials
   - Check Gmail App Password

3. **Database Connection Error**:
   - Check database credentials
   - Ensure MySQL service is running

4. **Composer Dependencies**:
   ```bash
   composer install --no-dev
   ```

## 🔄 Maintenance

### Regular Tasks:
- Monitor error logs
- Update dependencies
- Backup database
- Review security settings

### Updating Dependencies:
```bash
composer update
```

### Database Backup:
```bash
mysqldump -u root -p event_db > backup_$(date +%Y%m%d).sql
```

## 📊 Monitoring

### Log Files:
- PHP error logs
- Payment transaction logs
- Email delivery logs

### Admin Dashboard Features:
- View all registrations
- Export participant data
- Monitor payment status

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

For technical support or questions:
- Email: support@yaicess.com
- Issues: GitHub Issues page

## 🔖 Version History

- **v1.0.0**: Initial secure release
  - Fixed all security vulnerabilities
  - Added proper dependency management
  - Implemented secure payment flow
  - Added comprehensive email notifications

---

**Note**: This system has been thoroughly tested and all major security issues have been resolved. Always keep dependencies updated and monitor for security advisories.