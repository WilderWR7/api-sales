# Sales & Audit Trail REST API - Laravel 11

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon.tech-4169E1?style=for-the-badge&logo=postgresql)](https://neon.tech)
[![Swagger Docs](https://img.shields.io/badge/Swagger-OpenAPI--3.0-85EA2D?style=for-the-badge&logo=swagger)](http://wilder.alwaysdata.net/api/documentation)

A professional **Laravel 11** REST API for sales management, real-time stock control, secure authentication with **Laravel Sanctum**, and an immutable **Activity Log / Audit Trail** system.

---

## 🚀 Live Demo

The project is deployed and ready for testing in production:

- 🌐 **Production Server**: [http://wilder.alwaysdata.net/](http://wilder.alwaysdata.net/)
- 📄 **Interactive Swagger UI**: [http://wilder.alwaysdata.net/api/documentation](http://wilder.alwaysdata.net/api/documentation)

---

## ✨ Features & Architecture

- **Token Authentication (Laravel Sanctum)**: Protected API endpoints for user registration, login, and logout.
- **Database Transactions**: Sales creation, subtotal/total calculations, and stock deduction are wrapped inside atomic `DB::transaction` blocks.
- **Stock Restoration with Soft Deletes**: Deleting a sale safely restores product inventory automatically.
- **Immutable Audit Trail (Activity Logs)**: Background logging of authentication events, sales creation/deletion, and product updates stored in the `activity_log` table.
- **OpenAPI 3.0 Documentation (Swagger)**: Integrated with `l5-swagger` featuring Bearer authentication schemas and request parameters.
- **PostgreSQL Database**: Configured for cloud PostgreSQL (Neon.tech).

---

## 🛠️ API Endpoints

### Authentication (`/api`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/register` | Register a new user and receive a Bearer token | No |
| `POST` | `/login` | Log in and receive a Bearer token | No |
| `POST` | `/logout` | Revoke the current access token | Yes |
| `GET` | `/user` | Get authenticated user profile | Yes |

### Products (`/api/products`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/products` | List paginated products with stock details | Yes |

### Sales (`/api/sales`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/sales` | List user sales with item details | Yes |
| `POST` | `/sales` | Create a new sale (validates and deducts stock) | Yes |
| `DELETE` | `/sales/{id}` | Delete a sale (restores product stock) | Yes |

---

## 📦 Local Setup & Installation

### 1. Clone the repository and install dependencies

```bash
git clone <REPOSITORY_URL>
cd api-sales
composer install
```

### 2. Configure environment variables (`.env`)

```bash
cp .env.example .env
```

Update your database credentials in the `.env` file:

```env
APP_NAME="Sales API"
APP_ENV=local
APP_URL=http://api-sales.test
L5_SWAGGER_CONST_HOST=http://api-sales.test/api

DB_CONNECTION=pgsql
DB_HOST=your_postgresql_host
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate application key and run migrations

```bash
php artisan key:generate
php artisan migrate --seed
```

### 4. Generate Swagger documentation

```bash
php artisan l5-swagger:generate
```

### 5. Start local development server

```bash
php artisan serve
```

Access local API documentation at: `http://localhost:8000/api/documentation`

---

## 🧪 Testing

To run unit and feature test suites:

```bash
php artisan test
```

---

## 🔒 Audit Log & Security

All actions generate audit entries in the `activity_log` table:
- `log_name`: Event channel (`auth`, `products`, `sales`).
- `description`: Human-readable action summary.
- `user_id`: ID of the user who performed the action.
- `subject_id` & `subject_type`: Polymorphic resource affected.
- `properties`: JSON payload of old vs new values (`old` vs `attributes`).
- `ip_address` & `user_agent`: Source tracking.

> **Security Note:** Audit logs are saved passively in the database without public edit or delete endpoints to ensure historical integrity.
