# R3Booted Technology E-commerce Platform

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

> A complete, responsive e-commerce platform for technology products built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## 🚀 Live Demo

- **Website**: [View Live Demo](https://your-demo-url.com)
- **Admin Panel**: [Admin Dashboard](https://your-demo-url.com/admin)

### Demo Credentials
- **Admin**: `admin@r3booted.com` / `admin123`
- **User**: `user@example.com` / `password123`

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Technology Stack](#-technology-stack)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Security Features](#-security-features)
- [Contributing](#-contributing)
- [License](#-license)


## 📸 Screenshots

<details>
<summary>Click to view screenshots</summary>

### Homepage
![Homepage](screenshots/homepage.png)

### Product Catalog
![Products](screenshots/products.png)

### Shopping Cart
![Cart](screenshots/cart.png)

### Admin Dashboard
![Admin](screenshots/admin-dashboard.png)

### Mobile View
![Mobile](screenshots/mobile-view.png)

</details>

## 🛠️ Technology Stack

### Backend
- **PHP 8.1+**: Server-side logic and business rules
- **MySQL 8.0+**: Database management system
- **PDO**: Database abstraction layer with prepared statements

### Frontend
- **HTML5**: Semantic markup and structure
- **CSS3**: Modern styling with Grid and Flexbox
- **JavaScript (ES6)**: Interactive functionality and form validation

### Development Tools
- **Apache/Nginx**: Web server
- **Docker**: Containerization support
- **Git**: Version control

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Git

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/r3booted-technology.git
   cd r3booted-technology
   ```

2. **Set up environment variables**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` with your database credentials:
   ```env
   MYSQLHOST=localhost
   MYSQLPORT=3306
   MYSQLUSER=your_username
   MYSQLPASSWORD=your_password
   MYSQLDATABASE=r3booted_db
   PORT=8080
   ```

3. **Create database and import schema**
   ```sql
   CREATE DATABASE r3booted_db;
   ```
   
   Import the database schema:
   ```bash
   mysql -u your_username -p r3booted_db < database_schema.sql
   ```

4. **Set up file permissions**
   ```bash
   chmod 755 -R .
   chmod 777 -R uploads/
   ```

5. **Start the application**
   ```bash
   php -S localhost:8080
   ```

6. **Access the application**
   - Frontend: http://localhost:8080
   - Admin Panel: http://localhost:8080/admin

### Docker Installation

1. **Using Docker Compose**
   ```bash
   docker-compose up -d
   ```

2. **Or build manually**
   ```bash
   docker build -t r3booted .
   docker run -p 8080:8080 r3booted
   ```

## 🗄️ Database Setup

### Automatic Setup
The application automatically creates tables and inserts sample data on first run.

### Manual Setup
Run the SQL schema file:
```bash
mysql -u username -p database_name < database_schema.sql
```

### Database Schema
```
├── users (authentication & profiles)
├── categories (product categories)
├── products (product catalog)
├── cart (shopping cart items)
├── orders (order headers)
├── order_items (order line items)
└── contact_messages (customer inquiries)
```

## ⚙️ Configuration

### Environment Variables
| Variable | Description | Default |
|----------|-------------|---------|
| `MYSQLHOST` | Database host | localhost |
| `MYSQLPORT` | Database port | 3306 |
| `MYSQLUSER` | Database username | root |
| `MYSQLPASSWORD` | Database password | |
| `MYSQLDATABASE` | Database name | railway |
| `PORT` | Application port | 9000 |

### Application Settings
Edit `config.php` to modify:
- Site name and branding
- Security settings
- Upload limits
- Session configuration

## 📖 Usage

### For Customers

1. **Browse Products**
   - Visit the homepage to see featured products
   - Use the navigation to browse by category
   - Filter products using category buttons

2. **Shopping & Checkout**
   - Register for an account or login
   - Add products to cart with desired quantities
   - Review cart and proceed to checkout
   - Enter shipping information and payment method
   - Complete order and receive confirmation

3. **Account Management**
   - View order history (coming soon)
   - Update profile information
   - Contact customer support

### For Administrators

1. **Access Admin Panel**
   - Login with admin credentials
   - Navigate to `/admin` or use the admin link

2. **Product Management**
   - Add new products with images and details
   - Edit existing products and manage inventory
   - Set product status (active/inactive)
   - Monitor low stock alerts

3. **Order Management**
   - View all customer orders
   - Update order statuses
   - View detailed order information
   - Track order fulfillment

4. **User Management**
   - View all registered users
   - Manage user roles (user/admin)
   - Monitor user activity

## 🔐 Security Features

### Authentication & Authorization
- **Password Hashing**: Bcrypt encryption for all passwords
- **Session Security**: Secure session management with regeneration
- **Role-Based Access**: User and admin role separation
- **CSRF Protection**: Form token validation

### Data Protection
- **SQL Injection Prevention**: Prepared statements for all queries
- **XSS Protection**: Input sanitization and output encoding
- **Input Validation**: Client-side and server-side validation
- **File Upload Security**: Type and size validation for uploads

### Best Practices
- **Error Handling**: Secure error messages without information disclosure
- **Database Security**: Principle of least privilege
- **Session Management**: Secure cookie configuration
- **Data Sanitization**: All user input properly sanitized

## 🗂️ Project Structure

```
r3booted-technology/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── products.php      # Product management
│   ├── orders.php        # Order management
│   ├── users.php         # User management
│   └── messages.php      # Message management
├── css/
│   └── style.css         # Main stylesheet
├── js/
│   └── main.js          # JavaScript functionality
├── uploads/             # File upload directory
├── config.php           # Database & configuration
├── index.php           # Homepage
├── products.php        # Product catalog
├── cart.php           # Shopping cart
├── checkout.php       # Checkout process
├── login.php          # Authentication
├── contact.php        # Contact form
├── about.php          # About page
├── header.php         # Common header
├── footer.php         # Common footer
├── database_schema.sql # Database structure
├── .env.example       # Environment template
├── dockerfile         # Docker configuration
└── README.md         # This file
```

## 🧪 Testing

### Manual Testing
1. **User Registration & Login**
   ```bash
   # Test user registration flow
   # Test login with valid/invalid credentials
   # Test session management
   ```

2. **Shopping Cart Functionality**
   ```bash
   # Add items to cart
   # Update quantities
   # Remove items
   # Test cart persistence
   ```

3. **Order Processing**
   ```bash
   # Complete checkout process
   # Test order validation
   # Verify stock updates
   ```

4. **Admin Functionality**
   ```bash
   # Test product CRUD operations
   # Test order management
   # Test user management
   ```

### Database Testing
```sql
-- Test database connection
SELECT 1;

-- Verify table structure
DESCRIBE products;

-- Check sample data
SELECT COUNT(*) FROM users;
```


We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit your changes**
   ```bash
   git commit -m 'Add amazing feature'
   ```
4. **Push to the branch**
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open a Pull Request**

## 🐛 Known Issues

- [ ] Email notifications not implemented (placeholder functionality)
- [ ] Advanced search functionality pending
- [ ] Order history page for customers (coming soon)
- [ ] Payment gateway integration (demo mode only)

## 🔮 Future Enhancements

- [ ] **Payment Integration**: Stripe/PayPal integration
- [ ] **Email System**: Order confirmations and notifications
- [ ] **Advanced Search**: Elasticsearch integration
- [ ] **API Development**: RESTful API for mobile apps
- [ ] **Multi-language**: Internationalization support
- [ ] **Analytics**: Advanced reporting dashboard
- [ ] **Inventory Management**: Advanced stock tracking
- [ ] **Customer Reviews**: Product rating and review system

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


## 📊 Project Stats

- **Development Time**: 6 weeks
- **Lines of Code**: 3000+
- **Files**: 30+
- **Database Tables**: 7
- **Features**: 20+

---

<div align="center">

**Made with ❤️ by R3Booted Technology**

[⬆ Back to Top](#r3booted-technology-e-commerce-platform)

</div>
