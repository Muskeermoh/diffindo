# Diffindo Cakes and Bakes 🍰

A modern, responsive e-commerce web application for an artisan bakery specializing in premium cakes and baked goods. Built with PHP and MySQL, featuring a complete shopping experience from browsing to checkout.

## ✨ Features

### Customer Features
- 🏪 **Product Catalog** - Browse beautiful cake collections with images and descriptions
- 🛒 **Shopping Cart** - Add, remove, and manage items with real-time updates
- 👤 **User Authentication** - Secure registration and login system
- 📦 **Order Management** - Place orders and track order history
- ✉️ **Email Notifications** - Automated order confirmations via PHPMailer
- 💳 **Checkout System** - Streamlined checkout process with order confirmation
- 📱 **Responsive Design** - Mobile-friendly interface using Tailwind CSS

### Admin Features
- 📊 **Admin Dashboard** - Comprehensive overview of orders and products
- 📝 **Product Management** - Add, edit, and delete products
- 📋 **Order Processing** - View and manage customer orders
- 🔐 **Secure Admin Panel** - Protected admin-only access

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+ with PDO
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, Tailwind CSS, JavaScript
- **Email:** PHPMailer with Gmail SMTP
- **Authentication:** Session-based user management
- **Architecture:** MVC-inspired structure with separation of concerns

## 📁 Project Structure

```
diffindo-cakes-and-bakes/
├── admin/              # Admin panel pages
│   ├── dashboard.php
│   ├── login.php
│   ├── orders.php
│   └── products.php
├── cart/               # Shopping cart functionality
│   ├── add.php
│   ├── remove.php
│   └── view.php
├── includes/           # Core utilities and configuration
│   ├── auth.php       # Authentication functions
│   ├── db.php         # Database connection
│   ├── mailer.php     # Email functionality
│   └── utils.php      # Helper functions
├── order/              # Order processing
│   ├── cancel.php
│   ├── checkout.php
│   └── confirm.php
├── user/               # User account management
│   ├── login.php
│   ├── logout.php
│   ├── orders.php
│   └── register.php
├── assets/             # Static resources
│   └── images/        # Product and UI images
├── index.php           # Homepage
├── database.sql        # Database schema
└── README.md
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or local development environment (XAMPP/Laragon/WAMP)
- Composer (for PHPMailer dependencies)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/diffindo-cakes-and-bakes.git
   cd diffindo-cakes-and-bakes
   ```

2. **Import the database**
   - Create a new MySQL database named `diffindo`
   - Import `database.sql` into your database
   ```bash
   mysql -u root -p diffindo < database.sql
   ```

3. **Configure database connection**
   - Edit `includes/db.php` with your local database credentials
   - The file auto-detects local vs remote environments

4. **Install PHPMailer**
   ```bash
   composer require phpmailer/phpmailer
   ```

5. **Configure email settings**
   - Edit `includes/mailer.php` with your SMTP credentials
   - For Gmail, enable 2FA and create an app-specific password

6. **Set permissions** (Linux/Mac)
   ```bash
   chmod 755 assets/images/
   chmod 755 logs/
   ```

7. **Start your web server**
   - For XAMPP/Laragon: Place files in `htdocs` directory
   - Access via `http://localhost/diffindo-cakes-and-bakes`

## ⚙️ Configuration

### Database Configuration
The application automatically detects the environment (local/remote):
- **Local:** Uses `localhost` with default credentials
- **Remote:** Uses configured remote MySQL server

Edit `includes/db.php` to customize credentials.

### Email Configuration
Configure SMTP settings in `includes/mailer.php`:
- SMTP Host: `smtp.gmail.com`
- SMTP Port: `587` (TLS)
- Username: Your email address
- Password: App-specific password

### Admin Access
Default admin credentials (change after first login):
- Email: `admin@diffindo.com`
- Password: Set during initial setup

## 🎨 Features in Detail

### Product Management
- Upload product images
- Set prices and descriptions
- Manage inventory
- Category organization

### Order System
- Real-time cart updates
- Order confirmation emails
- Order history tracking
- Admin order dashboard

### User Experience
- Clean, modern UI with Tailwind CSS
- Responsive design for all devices
- Intuitive navigation
- Fast page loads

## 🔒 Security Features

- Password hashing with PHP's built-in functions
- SQL injection prevention using PDO prepared statements
- Session-based authentication
- Admin-only protected routes
- XSS protection

## 🌐 Deployment

### For Free Hosting (like Hostinger, InfinityFree)
1. Upload all files via FTP/File Manager
2. Import database via phpMyAdmin
3. Update `includes/db.php` with remote database credentials
4. Ensure PHPMailer vendor folder is uploaded
5. Set proper file permissions (755 for directories, 644 for files)

### For Production Servers
1. Use HTTPS/SSL certificate
2. Disable error display in `includes/db.php`
3. Use environment variables for sensitive data
4. Enable MySQL remote access only for required IPs
5. Regular database backups

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

Created with ❤️ for Diffindo Cakes and Bakes

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

## 📧 Contact

For inquiries: diffindocakes@gmail.com

---

**Live Demo:** [https://diffindo-cakesandbakes.zya.me/](https://diffindo-cakesandbakes.zya.me/)

**Note:** Remember to change default credentials and configure your own SMTP settings before deploying to production.