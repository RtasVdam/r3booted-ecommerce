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
</div>
