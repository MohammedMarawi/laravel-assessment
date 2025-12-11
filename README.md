# Digital Subscriptions Management System

A Laravel API for managing digital product subscriptions with Stripe payment integration.

## Features

-   **Authentication**: Register, Login, Logout (Laravel Sanctum)
-   **Products**: CRUD with file uploads (Spatie MediaLibrary)
-   **Subscriptions**: Create, view, cancel subscriptions
-   **Payments**: Stripe Checkout integration with webhooks
-   **Reports**: Excel export of subscriptions (Maatwebsite Excel)
-   **Authorization**: Role-based access (Spatie Permission)

## Requirements

-   PHP 8.2+
-   Composer
-   MySQL/SQLite
-   Stripe account (test mode)

## Installation

```bash
# Clone repository
git clone <repository-url>
cd laravel-assessment

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscriptions_db
DB_USERNAME=root
DB_PASSWORD=

# Configure Stripe in .env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start the server
php artisan serve
```

## API Endpoints

### Authentication

```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Logout (requires token)
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Products

```bash
# List products
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create product
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Premium Course" \
  -F "description=Full access" \
  -F "price=99.99" \
  -F "status=active" \
  -F "duration_days=30" \
  -F "file=@/path/to/image.jpg"

# Filter products
curl "http://localhost:8000/api/products?status=active&min_price=50" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Subscriptions

```bash
# List my subscriptions
curl http://localhost:8000/api/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create subscription
curl -X POST http://localhost:8000/api/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'

# View subscription
curl http://localhost:8000/api/subscriptions/1 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Cancel subscription
curl -X POST http://localhost:8000/api/subscriptions/1/cancel \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get statistics
curl http://localhost:8000/api/subscriptions/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"

# Export to Excel
curl -o subscriptions.xlsx http://localhost:8000/api/subscriptions/export \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Payments

```bash
# Create checkout session
curl -X POST http://localhost:8000/api/payments/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"subscription_id": 1}'

# List my payments
curl http://localhost:8000/api/payments \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Stripe Webhook Testing

```bash
# Install Stripe CLI
# https://stripe.com/docs/stripe-cli

# Forward webhooks to local
stripe listen --forward-to localhost:8000/api/webhook/stripe

# Trigger test events
stripe trigger checkout.session.completed
stripe trigger customer.subscription.updated
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

## Environment Variables

| Variable                | Description                   |
| ----------------------- | ----------------------------- |
| `STRIPE_KEY`            | Stripe publishable key        |
| `STRIPE_SECRET`         | Stripe secret key             |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret |

## Response Format

All API responses follow this format:

```json
{
  "status": "success|error",
  "message": "Description",
  "data": { ... },
  "meta": { "total": 100, "page": 1 }
}
```

## License

MIT
