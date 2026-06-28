# Extendable Order & Payment Management API

Laravel REST API for managing orders and payments with JWT authentication and an extensible payment gateway architecture using the **Strategy pattern**.

## Features

- JWT authentication (register, login, logout, refresh, profile)
- Order CRUD with automatic total calculation
- Payment processing through pluggable gateways
- Input validation with meaningful error messages
- Paginated list endpoints
- Unit and feature tests
- Postman collection for API documentation

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL
- PHP extensions: `pdo_sqlite` or `pdo_mysql`, `mbstring`, `openssl`

## Setup

```bash
# Clone the repository and install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key (if not already generated)
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Configure database in .env (SQLite is default)
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# Configure payment gateway credentials
CREDIT_CARD_API_KEY=your_credit_card_api_key
CREDIT_CARD_SECRET=your_credit_card_secret
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret

# Run migrations
php artisan migrate

# Start the development server
php artisan serve
```

Base API URL: `http://localhost:8000/api`

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login and receive JWT token |
| GET | `/api/auth/me` | Get authenticated user profile |
| POST | `/api/auth/logout` | Invalidate current token |
| POST | `/api/auth/refresh` | Refresh JWT token |

### Orders (requires JWT)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders` | List orders (`?status=pending\|confirmed\|cancelled`) |
| POST | `/api/orders` | Create order |
| GET | `/api/orders/{id}` | Show order |
| PUT/PATCH | `/api/orders/{id}` | Update order |
| DELETE | `/api/orders/{id}` | Delete order (only if no payments exist) |

### Payments (requires JWT)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/payments/gateways` | List available payment gateways |
| GET | `/api/payments` | List payments (`?order_id=1`) |
| POST | `/api/payments` | Process payment for an order |
| GET | `/api/payments/{id}` | Show payment details |

## Business Rules

1. Orders can have statuses: `pending`, `confirmed`, `cancelled`
2. Payments can only be processed for **confirmed** orders
3. Orders **cannot be deleted** if they have associated payments
4. Order total is calculated automatically from item quantities and prices

## Payment Gateway Extensibility

The payment system uses the **Strategy pattern** to keep gateway logic isolated and easy to extend.

### Architecture

```
PaymentGatewayInterface
├── CreditCardGateway
├── PayPalGateway
└── (your new gateway)

PaymentGatewayManager  → resolves gateway by payment method
PaymentService         → orchestrates payment processing
```

### Configuration

Gateway credentials are configured in `.env` and mapped through `config/payment.php`:

```php
'gateways' => [
    'credit_card' => [
        'api_key' => env('CREDIT_CARD_API_KEY'),
        'secret' => env('CREDIT_CARD_SECRET'),
    ],
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],
],
```

### How to Add a New Gateway

1. **Add enum value** in `app/Enums/PaymentMethod.php`:
   ```php
   case Stripe = 'stripe';
   ```

2. **Create gateway class** implementing `PaymentGatewayInterface`:
   ```php
   namespace App\Services\PaymentGateways;

   class StripeGateway implements PaymentGatewayInterface
   {
       public function getName(): string
       {
           return 'stripe';
       }

       public function process(Order $order, array $payload = []): PaymentGatewayResult
       {
           // Read config('payment.gateways.stripe.*')
           // Call Stripe API (or simulate)
           // Return PaymentGatewayResult
       }
   }
   ```

3. **Register gateway** in `PaymentGatewayManager::__construct()`:
   ```php
   $this->register(new StripeGateway());
   ```

4. **Add config** in `config/payment.php` and `.env`:
   ```env
   STRIPE_SECRET_KEY=sk_test_...
   ```

5. **Update validation** in `ProcessPaymentRequest` if the gateway needs specific payload fields.

6. **Add tests** in `tests/Unit/PaymentGatewayTest.php` and `tests/Feature/PaymentTest.php`.

No changes are required in controllers or `PaymentService` beyond registration.

## Example Requests

### Register

```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Create Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_name": "Jane Doe",
  "customer_email": "jane@example.com",
  "items": [
    { "product_name": "Laptop", "quantity": 1, "price": 999.99 }
  ]
}
```

### Process Payment

```http
POST /api/payments
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 1,
  "payment_method": "credit_card",
  "gateway_payload": {
    "card_number": "4242424242424242"
  }
}
```

## Testing

```bash
php artisan test
```

Tests cover authentication, order management, payment business rules, and gateway logic.

## Postman Collection

Import the collection from:

`docs/Order Payments.postman_collection.json`

1. Import into Postman
2. Set `base_url` variable (default: `http://localhost:8000/api`)
3. Run **Login** — the token is saved automatically to `{{accessToken}}`
4. Use the Orders and Payments folders

## Postman Documentation

`https://documenter.getpostman.com/view/9042950/2sBXwyGSjJ`


## Assumptions

- Payment gateways are **simulated** for demonstration (no real external API calls)
- Credit card ending in `0000` simulates a declined payment
- PayPal email containing `fail` simulates a failed authorization
- Orders belong to the authenticated user who created them
- JWT is provided via `Authorization: Bearer {token}` header

## Project Structure

```
app/
├── Enums/                  # OrderStatus, PaymentStatus, PaymentMethod
├── Http/
│   ├── Controllers/Api/    # Auth, Order, Payment controllers
│   ├── Requests/           # Form request validation
│   └── Resources/          # API response transformers
├── Models/                 # User, Order, OrderItem, Payment
└── Services/
    ├── OrderService.php
    ├── PaymentService.php
    └── PaymentGateways/    # Strategy pattern implementation
```
