# Task Manager API

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

A production-ready REST API for a Task Manager application built with **Laravel 11** and **Laravel Sanctum** for token-based authentication.

**Live API:** `https://YOUR-RAILWAY-URL.railway.app`

---

## Endpoints at a Glance

| Method   | Endpoint                     | Auth | Description                          |
|----------|------------------------------|------|--------------------------------------|
| POST     | `/api/register`              | No   | Register a new account               |
| POST     | `/api/login`                 | No   | Login and receive an API token       |
| POST     | `/api/logout`                | Yes  | Revoke current token                 |
| GET      | `/api/user`                  | Yes  | Get authenticated user profile       |
| GET      | `/api/tasks`                 | Yes  | List tasks (paginated, filterable)   |
| POST     | `/api/tasks`                 | Yes  | Create a new task                    |
| GET      | `/api/tasks/{id}`            | Yes  | Get a single task                    |
| PUT      | `/api/tasks/{id}`            | Yes  | Update a task                        |
| DELETE   | `/api/tasks/{id}`            | Yes  | Delete a task                        |
| PATCH    | `/api/tasks/{id}/status`     | Yes  | Update task status only              |
| GET      | `/api/categories`            | Yes  | List all categories                  |
| POST     | `/api/categories`            | Yes  | Create a category                    |
| GET      | `/api/categories/{id}`       | Yes  | Get a single category                |
| PUT      | `/api/categories/{id}`       | Yes  | Update a category                    |
| DELETE   | `/api/categories/{id}`       | Yes  | Delete a category                    |

### Task Filters (GET /api/tasks)

| Parameter     | Type    | Description                              |
|---------------|---------|------------------------------------------|
| `status`      | string  | `todo` \| `in_progress` \| `done`        |
| `category_id` | integer | Filter tasks by category                 |
| `search`      | string  | Partial title match (LIKE)               |
| `page`        | integer | Page number (default: 1, size: 10)       |

---

## Local Setup

### Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (or SQLite for quick testing)
- Laravel Sanctum-compatible driver

### 1. Clone and install dependencies

```bash
git clone https://github.com/YOUR_USERNAME/task-api.git
cd task-api
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and update your database credentials:

```env
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run migrations and seed demo data

```bash
php artisan migrate --seed
```

### 4. Start the development server

```bash
php artisan serve
```

The API is now available at `http://localhost:8000`.

---

## Demo Credentials

```
Email:    demo@example.com
Password: password
```

The seeder creates:
- **1 demo user**
- **3 categories**: Work, Personal, Shopping
- **20 tasks**: 8 todo, 7 in_progress, 5 done (some with past due dates)

---

## Authentication

This API uses **token-based authentication** via Laravel Sanctum.

1. Call `POST /api/login` with your credentials
2. Copy the `token` from the response
3. Include it in every subsequent request as a Bearer token:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Running Tests

```bash
php artisan test
# or
./vendor/bin/phpunit
```

---

## Deployment (Railway)

1. Push this repo to GitHub
2. Create a new project on [Railway](https://railway.app)
3. Connect your GitHub repository
4. Add a MySQL plugin and link it
5. Set environment variables (copy from `.env.example`)
6. Railway will auto-detect `railway.toml` and deploy

---

## Postman Collection

Import `postman_collection.json` into Postman. The collection:
- Pre-configures `base_url = http://localhost:8000`
- Auto-saves the token after login via test scripts
- Includes all endpoints with example request bodies
