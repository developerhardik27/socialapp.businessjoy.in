# BusinessJoy - Business Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

BusinessJoy is a comprehensive business management system built with Laravel 10.x, designed to streamline and automate various business operations including invoicing, quotations, lead management, subscriptions, and more.

## 🚀 Features

### Core Modules
- **Company Management**: Multi-company support with role-based access control
- **User Management**: Comprehensive user authentication and authorization
- **Invoice Management**: Create, manage, and track invoices with multiple payment statuses
- **Quotation System**: Generate and manage business quotations
- **Lead Management**: Track and convert business leads
- **Subscription System**: Manage recurring subscriptions with automated billing
- **Inventory Management**: Track products and stock levels
- **Customer Management**: Centralized customer database and relationship management
- **Report & Analytics**: Comprehensive reporting with interactive charts
- **Transport & Logistics**: Manage consignments and shipping

### Advanced Features
- **Multi-Version Support**: Version-controlled API endpoints for backward compatibility
- **Dynamic Database Connections**: Per-company database isolation
- **Role-Based Permissions**: Granular access control system
- **Real-time Dashboard**: Interactive charts and analytics
- **PDF Generation**: Generate invoices, quotations, and reports as PDFs
- **Email Integration**: Automated email notifications and templates
- **File Management**: Document upload and management system
- **API Documentation**: RESTful API with comprehensive endpoints

## 📊 Dashboard & Analytics

### Admin Dashboard
- **Company Overview**: User statistics and activity monitoring
- **Subscription Analytics**: Pie chart showing subscription status distribution
- **Financial Metrics**: Revenue and payment tracking
- **System Health**: Performance and usage monitoring

### Module-Specific Dashboards
- **Invoice Dashboard**: Payment status tracking and monthly reports
- **Quotation Dashboard**: Conversion rates and status analysis
- **Lead Dashboard**: Lead conversion and follow-up tracking
- **Developer Dashboard**: System performance and slow page analysis

## 🔧 Technical Architecture

### Backend
- **Framework**: Laravel 10.x
- **Database**: MySQL with dynamic connections
- **Authentication**: Laravel Sanctum & Passport
- **API Versioning**: Multi-version API support (v1.0.0 - v4.4.1)
- **Queue System**: Background job processing
- **File Storage**: Local and cloud storage support

### Frontend
- **Blade Templates**: Server-side rendering with Blade
- **Responsive Design**: Mobile-first approach
- **Interactive Charts**: ApexCharts integration
- **Data Tables**: Yajra DataTables for complex data display
- **UI Components**: Bootstrap-based admin interface

### Database Design
- **Multi-Tenancy**: Dynamic database connections per company
- **Soft Deletes**: Data preservation with soft deletion
- **Audit Trail**: User action tracking and logging
- **Migration System**: Version-controlled database schema

## 📦 Installation

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (for asset compilation)

### Setup Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd BusinessJoy
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

5. **Link storage**
```bash
php artisan storage:link
```

6. **Compile assets**
```bash
npm run build
npm run dev
```

7. **Start the server**
```bash
php artisan serve
```

## 🔐 Security Features

- **API Authentication**: Token-based authentication
- **Permission System**: Role-based access control
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Eloquent ORM protection
- **CSRF Protection**: Built-in CSRF token validation
- **XSS Protection**: Input sanitization and output encoding

## 📚 API Documentation

### Base URL
```
http://your-domain.com/api/v4_4_1
```

### Authentication
All API requests require:
- `token`: API authentication token
- `user_id`: Current user ID
- `company_id`: Current company ID

### Key Endpoints

#### Company Management
- `GET /company` - List companies
- `POST /company/insert` - Create company
- `GET /company/search/{id}` - Get company details
- `PUT /company/update/{id}` - Update company
- `POST /company/delete/{id}` - Delete company
- `PUT /company/statusupdate/{id}` - Update company status

#### Subscription Management
- `GET /subscription` - List subscriptions
- `POST /subscription/insert` - Create subscription
- `GET /subscription/history/{id}` - Get subscription history
- `PUT /subscription/update/{id}` - Update subscription
- `POST /subscription/renew` - Renew subscription

#### Invoice Management
- `GET /invoice` - List invoices
- `POST /invoice/insert` - Create invoice
- `GET /invoice/search/{id}` - Get invoice details
- `PUT /invoice/update/{id}` - Update invoice

#### Payment Management
- `GET /subscriptionpayment` - List subscription payments
- `PUT /subscriptionpayment/statusupdate/{id}` - Update payment status

## 🏢 Module Structure

### Company Management
- Multi-company support
- Dynamic database connections
- User assignment and permissions
- Company details and branding

### Subscription System
- Recurring billing management
- Payment cycle tracking
- Automated renewal system
- Subscription history tracking

### Invoice & Quotation
- Professional invoice generation
- Quotation management
- Payment status tracking
- PDF export functionality

### Lead Management
- Lead capture and tracking
- Conversion analytics
- Follow-up management
- Lead scoring system

## 🎯 Key Features Highlight

### Subscription Payment Management
- **Advanced Filtering**: Multi-select filters for status, company, package, and date ranges
- **Real-time Updates**: Instant status changes and notifications
- **Billing Cycle Tracking**: Comprehensive payment period management
- **Audit Trail**: Complete history of all payment changes

### Dashboard Analytics
- **Interactive Charts**: Real-time data visualization
- **Subscription Status Pie Chart**: Visual distribution of active/inactive/trial/expired subscriptions
- **Performance Metrics**: System performance and usage analytics
- **Custom Reports**: Flexible reporting system

### Multi-Version API Support
- **Backward Compatibility**: Support for older API versions
- **Version Management**: Easy version switching and migration
- **Deprecation Warnings**: Clear communication about API changes

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Test Coverage
- Unit tests for core business logic
- Feature tests for API endpoints
- Database transaction tests
- Authentication and authorization tests

## 📝 Development

### Code Style
- Follow PSR-12 coding standards
- Use Laravel conventions
- Comprehensive code comments
- Type hinting for all methods

### Contributing Guidelines
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 🔧 Configuration

### Environment Variables
Key environment variables:
- `DB_CONNECTION`: Database connection type
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `MAIL_MAILER`: Mail service configuration
- `APP_URL`: Application URL

### Permission System
The system uses a granular permission system:
- `adminmodule.company.view`: View companies
- `adminmodule.company.edit`: Edit companies
- `adminmodule.company.add`: Add companies
- `adminmodule.company.delete`: Delete companies
- `adminmodule.subscription.view`: View subscriptions
- `adminmodule.subscription.edit`: Edit subscriptions
- `adminmodule.invoice.view`: View invoices
- `adminmodule.invoice.edit`: Edit invoices

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review existing issues and discussions

## 🔄 Version History

### v4.4.1 (Latest)
- Enhanced ledger balance calculation system
- Records displayed newest to oldest with correct running balance
- Improved financial reporting accuracy
- Complete v4_4_1 model and controller namespace structure
- Advanced subscription payment management
- Enhanced filtering and analytics
- Improved dashboard with pie charts
- Optimized API performance

### v4.4.0
- Subscription payment management system
- Advanced filtering and analytics
- Enhanced dashboard with pie charts
- Improved API performance

### Previous Versions
- v4.3.x: Enhanced reporting and analytics
- v4.2.x: Lead management improvements
- v4.1.x: Quotation system enhancements
- v4.0.x: Major architecture overhaul

---

**BusinessJoy** - Streamlining Business Operations Through Technology
