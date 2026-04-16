# Subscription Payment Management

## Overview
The subscription payment management system allows administrators to track and manage payment status for upcoming subscription payments.

## Features

### 1. Payment List View
- **Route**: `/admin/subscriptionpayments`
- **Shows**: Latest payment record for each subscription
- **Columns**: Company, Package, Payment Period, Amount, Status
- **Advanced Filters**: Sidebar-based filter system with multiple filter options

### 2. Advanced Filter System
- **Payment Status**: Multi-select filter for Paid/Pending status
- **Company**: Multi-select filter for companies (not customers)
- **Package**: Multi-select filter for subscription packages
- **Due Date Range**: Filter by next billing date (from/to)
- **Payment Period Range**: Filter by payment start/end dates (from/to)
- **Real-time Filtering**: Filters apply automatically on change

### 3. Payment Status Management
- **Toggle Status**: Click status button to switch between Paid/Pending
- **Real-time Updates**: Status changes are immediately reflected
- **Audit Trail**: All status changes are logged

### 4. Latest Payment Records Only
- Shows only the most recent payment entry per subscription
- Previous payments are hidden for cleaner interface
- Focuses on upcoming/current payment cycles

## API Endpoints

### Get Subscription Payments
```
GET /api/v4_4_0/subscriptionpayment
```

**Parameters:**
- `payment_status[]`: Array of payment statuses (pending/paid)
- `company[]`: Array of company IDs
- `package[]`: Array of package IDs
- `next_billing_from_date`: Filter by next billing date from
- `next_billing_to_date`: Filter by next billing date to
- `payment_start_from_date`: Filter by payment start date from
- `payment_start_to_date`: Filter by payment start date to
- `payment_end_from_date`: Filter by payment end date from
- `payment_end_to_date`: Filter by payment end date to

**Response:**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "subscription_id": 1,
      "payment_start_date": "2026-01-01",
      "payment_end_date": "2026-01-30",
      "next_billing_date": "2026-01-15",
      "emi_cost": 99.99,
      "payment_status": "pending",
      "company_name": "Company Name",
      "package_name": "Package Name"
    }
  ]
}
```

### Update Payment Status
```
PUT /api/v4_4_0/subscriptionpayment/statusupdate/{id}
```

**Request Body:**
```json
{
  "payment_status": "paid"
}
```

## Database Schema

### Subscription Payments Table
- `subscription_id`: Foreign key to subscriptions
- `payment_start_date`: Start of payment period
- `payment_end_date`: End of payment period  
- `next_billing_date`: When next payment is due
- `payment_status`: 'pending' or 'paid'
- `created_by`, `updated_by`: User tracking
- `is_active`, `is_deleted`: Soft delete flags

## Permissions Required
- `adminmodule.subscription.view`: View payment list
- `adminmodule.subscription.edit`: Change payment status

## Usage Notes

1. **Latest Records Only**: The system automatically filters to show only the most recent payment entry per subscription
2. **Advanced Filtering**: Use sidebar filters for comprehensive filtering options
3. **Multi-select Filters**: Company, Package, and Status filters support multiple selections
4. **Date Range Filters**: Use from/to date ranges for precise date filtering
5. **Status Toggle**: Click the status button to quickly change between Paid/Pending
6. **Real-time Updates**: All filters apply automatically without manual refresh
7. **Company Focus**: Filters show companies (not customers) as requested
8. **Amount Display**: Shows EMI cost from the associated subscription
9. **Date Formatting**: All dates are displayed in user-friendly format

## Integration with Renewal System

When subscriptions are automatically renewed:
- New payment entries are created with `pending` status
- Payment period dates are calculated based on billing cycle
- Next billing date is set to 15 days before cycle end
- Administrators can mark payments as paid via this interface
