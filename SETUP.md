# Task Manager — Full Stack Setup Guide

Laravel 11 REST API + Vue 3 SPA demo project for Upwork portfolio.

**Demo credentials:** `demo@example.com` / `password`

---

## Prerequisites

| Tool | Version |
|------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL | 8.0+ |
| Git | any |

---

## Project Structure

```
rest-api-sps/
├── task-api/          ← Laravel 11 REST API
└── task-spa-vue/      ← Vue 3 SPA frontend
```

---

## Part 1 — Backend (task-api)

### 1. Install dependencies

```bash
cd task-api
composer install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database credentials:

```env
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE task_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Run migrations and seed demo data

```bash
php artisan migrate
php artisan db:seed
```

This creates:
- 1 demo user (`demo@example.com` / `password`)
- 3 categories: Work, Personal, Shopping
- 20 sample tasks across all statuses and priorities

### 5. Start the API server

```bash
php artisan serve
```

API is now running at `http://localhost:8000`

---

## Part 2 — Frontend (task-spa-vue)

### 1. Install dependencies

```bash
cd task-spa-vue
npm install
```

### 2. Environment setup

```bash
cp .env.example .env
```

The default `.env` points to `http://localhost:8000` — no changes needed for local dev.

### 3. Start the dev server

```bash
npm run dev
```

SPA is now running at `http://localhost:5173`

---

## Running Both Together

Open two terminal tabs:

```bash
# Tab 1 — API
cd task-api && php artisan serve

# Tab 2 — SPA
cd task-spa-vue && npm run dev
```

Then open `http://localhost:5173` in your browser.

---

## API Endpoints Reference

Base URL: `http://localhost:8000/api`

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/register` | No | Create account |
| POST | `/login` | No | Login and get session |
| POST | `/logout` | Yes | End session |
| GET | `/user` | Yes | Get current user |

### Tasks

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/tasks` | Yes | List tasks (paginated, filterable) |
| POST | `/tasks` | Yes | Create a task |
| GET | `/tasks/{id}` | Yes | Get a task |
| PUT | `/tasks/{id}` | Yes | Update a task |
| DELETE | `/tasks/{id}` | Yes | Delete a task |
| PATCH | `/tasks/{id}/status` | Yes | Update status only |

**Query params for GET /tasks:**
- `page` — page number (default: 1)
- `per_page` — items per page (default: 10)
- `status` — filter by `todo`, `in_progress`, or `done`
- `category_id` — filter by category
- `search` — search by title

### Categories

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/categories` | Yes | List all categories |
| POST | `/categories` | Yes | Create a category |
| PUT | `/categories/{id}` | Yes | Update a category |
| DELETE | `/categories/{id}` | Yes | Delete a category |

---

## Testing the API with Postman

1. Import `task-api/postman_collection.json` into Postman
2. Set the `base_url` variable to `http://localhost:8000`
3. Run **POST /login** first — the collection auto-saves the token
4. All authenticated requests will use it automatically

---

## Running Tests

```bash
cd task-api

# Run all tests
php artisan test

# Run specific test files
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/TaskTest.php
```

---

## Deployment

### API → Railway

1. Push `task-api` to a GitHub repo
2. Create a new Railway project, connect the repo
3. Add a **MySQL** plugin from the Railway dashboard
4. Set these environment variables in Railway:

```env
APP_KEY=                          # generate with: php artisan key:generate
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-api.railway.app

DB_CONNECTION=mysql
DB_HOST=                          # from Railway MySQL plugin
DB_PORT=3306
DB_DATABASE=                      # from Railway MySQL plugin
DB_USERNAME=                      # from Railway MySQL plugin
DB_PASSWORD=                      # from Railway MySQL plugin

SESSION_DRIVER=cookie
SESSION_DOMAIN=.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-spa.vercel.app

FRONTEND_URL=https://your-spa.vercel.app
CORS_ALLOWED_ORIGINS=https://your-spa.vercel.app
```

5. After first deploy, run via Railway shell:

```bash
php artisan migrate --force
php artisan db:seed --force
```

### SPA → Vercel

1. Push `task-spa-vue` to a GitHub repo
2. Import the repo into Vercel
3. Set this environment variable in Vercel:

```env
VITE_API_URL=https://your-api.railway.app
```

4. Deploy — `vercel.json` already handles SPA routing rewrites

> **Note:** Both the API and SPA must be on HTTPS for Sanctum cookie auth to work in production. Railway and Vercel both provide HTTPS by default.

---

## Troubleshooting

**CORS errors in browser**
- Confirm `FRONTEND_URL` in the API `.env` exactly matches the SPA URL (no trailing slash)
- Confirm `supports_credentials: true` in `config/cors.php`

**401 on every request after login**
- Check `SESSION_DOMAIN` — in production it must match the SPA domain
- Confirm `withCredentials: true` is set in `src/api/axios.js`

**419 CSRF token mismatch**
- Make sure the SPA calls `GET /sanctum/csrf-cookie` before login/register
- The axios instance handles this automatically on 419 retries

**Vite proxy not needed**
- This setup uses real cross-origin requests with cookies, not a Vite proxy

---

## Folder Reference

```
task-api/
├── app/Http/Controllers/Api/   ← AuthController, TaskController, CategoryController
├── app/Http/Requests/          ← StoreTaskRequest, UpdateTaskRequest
├── app/Http/Resources/         ← TaskResource, CategoryResource, UserResource
├── app/Models/                 ← User, Task, Category
├── app/Policies/               ← TaskPolicy
├── database/migrations/        ← categories and tasks tables
├── database/seeders/           ← DatabaseSeeder
├── routes/api.php              ← all API routes
├── config/cors.php             ← CORS configuration
├── tests/Feature/              ← AuthTest, TaskTest
├── postman_collection.json     ← import into Postman
└── railway.toml                ← Railway deploy config

task-spa-vue/
├── src/api/axios.js            ← configured axios instance
├── src/stores/auth.js          ← Pinia auth store
├── src/stores/tasks.js         ← Pinia tasks store
├── src/router/index.js         ← routes + navigation guards
├── src/pages/                  ← Login, Register, Dashboard, Tasks, TaskDetail
├── src/components/             ← TaskCard, TaskForm, Pagination, SkeletonCard
├── src/layouts/AppLayout.vue   ← main app shell
└── vercel.json                 ← Vercel SPA routing config
```
