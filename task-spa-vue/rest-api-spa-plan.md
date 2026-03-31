# REST API + Vue/React SPA — Complete Build Plan
> Upwork portfolio demo showing a decoupled Laravel API + modern SPA frontend.
> Paste the stack context block before any code generation prompt.

---

## Stack Context Block (paste this first in every Claude prompt)

> "I am building a decoupled REST API demo for Upwork using:
> - Backend: Laravel 11, Sanctum (SPA cookie auth), MySQL 8, API Resources, Form Requests
> - Frontend: Vue 3 + Pinia + Vue Router + Vite + Tailwind CSS (OR React 18 + Zustand + React Router + Vite + Tailwind CSS)
> - Demo app: Task Manager (tasks, categories, user auth)
> - Deployment: Laravel API on Railway, SPA on Vercel"

---

## Project Overview

**App concept:** Task Manager — simple enough to build fast, complex enough to show real patterns clients care about.

**Demo credentials:** demo@example.com / password

**Two repos:**
- `task-api` — Laravel 11 REST API
- `task-spa-vue` (or `task-spa-react`) — SPA frontend

**Live URLs:**
- API: https://task-api.railway.app
- SPA: https://task-spa.vercel.app

---

## Folder Structure

### Backend — `task-api/`

```
task-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── TaskController.php
│   │   │   └── CategoryController.php
│   │   ├── Requests/
│   │   │   ├── StoreTaskRequest.php
│   │   │   └── UpdateTaskRequest.php
│   │   └── Resources/
│   │       ├── TaskResource.php
│   │       ├── TaskCollection.php
│   │       └── CategoryResource.php
│   └── Models/
│       ├── User.php
│       ├── Task.php
│       └── Category.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_tasks_table.php
│   │   └── xxxx_create_categories_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
├── config/
│   └── cors.php
├── .env.example
├── postman_collection.json    ← attach to GitHub + Upwork proposal
└── README.md
```

### Frontend Vue 3 — `task-spa-vue/`

```
task-spa-vue/
├── src/
│   ├── api/
│   │   └── axios.js           ← configured axios instance
│   ├── stores/
│   │   ├── auth.js            ← Pinia auth store
│   │   └── tasks.js           ← Pinia tasks store
│   ├── router/
│   │   └── index.js           ← routes + navigation guards
│   ├── pages/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── Dashboard.vue
│   │   ├── Tasks.vue
│   │   └── TaskDetail.vue
│   ├── components/
│   │   ├── TaskCard.vue
│   │   ├── TaskForm.vue
│   │   ├── CategoryBadge.vue
│   │   ├── Pagination.vue
│   │   ├── SkeletonCard.vue
│   │   └── ToastContainer.vue
│   ├── layouts/
│   │   └── AppLayout.vue
│   └── main.js
├── .env.example               ← VITE_API_URL=
├── vercel.json
└── README.md
```

### Frontend React — `task-spa-react/`

```
task-spa-react/
├── src/
│   ├── api/
│   │   └── axios.ts
│   ├── stores/
│   │   ├── authStore.ts       ← Zustand
│   │   └── taskStore.ts
│   ├── router/
│   │   └── ProtectedRoute.tsx
│   ├── pages/
│   │   ├── Login.tsx
│   │   ├── Register.tsx
│   │   ├── Dashboard.tsx
│   │   └── Tasks.tsx
│   ├── components/
│   │   ├── TaskCard.tsx
│   │   ├── TaskForm.tsx
│   │   ├── Pagination.tsx
│   │   └── SkeletonCard.tsx
│   └── main.tsx
├── .env.example
├── vercel.json
└── README.md
```

---

## Database Schema

### users (Laravel default + additions)
```sql
id, name, email, password, email_verified_at,
remember_token, created_at, updated_at
```

### categories
```sql
id, user_id (FK), name, color (hex string), created_at, updated_at
```

### tasks
```sql
id, user_id (FK), category_id (FK nullable),
title, description (nullable), status (enum: todo/in_progress/done),
priority (enum: low/medium/high), due_date (date nullable),
created_at, updated_at
```

---

## API Endpoints

### Auth
```
POST   /api/register          { name, email, password, password_confirmation }
POST   /api/login             { email, password }
POST   /api/logout            (auth required)
GET    /api/user              returns authenticated user
```

### Tasks (all require auth)
```
GET    /api/tasks             ?page=1&per_page=10&status=todo&category_id=1&search=
POST   /api/tasks             { title, description, category_id, status, priority, due_date }
GET    /api/tasks/{id}
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
PATCH  /api/tasks/{id}/status { status }   ← for drag-and-drop kanban
```

### Categories (all require auth)
```
GET    /api/categories
POST   /api/categories        { name, color }
PUT    /api/categories/{id}
DELETE /api/categories/{id}
```

---

## Phase 1 — Laravel API Foundation (2 days)

### Step 1.1 — Laravel install + API-only config
**Prompt:**
> "Generate the setup commands and config changes to make a fresh Laravel 11 install API-only:
> Remove the web routes for auth (we use Sanctum), install sanctum, set SESSION_DRIVER=cookie, configure api.php with the sanctum middleware group. Show the bootstrap/app.php changes for Laravel 11."

### Step 1.2 — Sanctum SPA auth
**Prompt:**
> "Generate a Laravel 11 AuthController for Sanctum SPA authentication with these JSON endpoints:
> - POST /api/register: validate name/email/password, create user, return UserResource + 201
> - POST /api/login: validate credentials, return UserResource or 422 with errors
> - POST /api/logout: revoke tokens, return 204
> - GET /api/user: return authenticated user as UserResource
> Use Sanctum's stateful SPA auth (cookie-based), not tokens."

### Step 1.3 — CORS config
**Prompt:**
> "Generate the config/cors.php for a Laravel API that accepts requests from a Vite SPA running on http://localhost:5173 in dev and https://task-spa.vercel.app in production. Set supports_credentials to true. Show the .env variables needed."

### Step 1.4 — Tasks migration + model
**Prompt:**
> "Generate a Laravel migration and Task model for:
> - tasks table: id, user_id (FK), category_id (FK nullable), title (string), description (text nullable), status (enum: todo, in_progress, done, default: todo), priority (enum: low, medium, high, default: medium), due_date (date nullable), timestamps
> - Task model: belongsTo User, belongsTo Category, scopeForUser(), scopeByStatus(), scopeByCategory(), a getIsOverdueAttribute() accessor"

### Step 1.5 — TaskController (full CRUD)
**Prompt:**
> "Generate a Laravel TaskController in App\Http\Controllers\Api with:
> - index(): paginated (10/page), filterable by status and category_id, searchable by title, scoped to auth()->user(), returns TaskCollection
> - store(): uses StoreTaskRequest, creates task for auth user, returns TaskResource 201
> - show(): returns TaskResource (authorize user owns task)
> - update(): uses UpdateTaskRequest, returns TaskResource
> - destroy(): returns 204
> Also generate StoreTaskRequest with validation rules and TaskResource with: id, title, description, status, priority, due_date, is_overdue, category (nested CategoryResource), created_at"

### Step 1.6 — CategoryController
**Prompt:**
> "Generate a CategoryController for /api/categories with full CRUD scoped to auth user. Generate the Category migration (id, user_id, name, color hex), model with hasMany tasks, and CategoryResource."

### Step 1.7 — Postman collection
**Prompt:**
> "Generate a Postman collection JSON (v2.1 format) for this Task Manager API covering: register, login, get user, logout, CRUD for tasks (with pagination params), CRUD for categories. Set a baseUrl variable to {{base_url}} and a bearerToken variable. Include example request bodies."

---

## Phase 2 — Frontend (Vue 3 version) (3 days)

### Step 2.1 — Vite + Vue 3 setup
**Prompt:**
> "Generate the setup commands and initial file structure for a Vue 3 + Vite + Tailwind CSS + Vue Router 4 + Pinia project called task-spa-vue. Include: npm install commands, tailwind.config.js, main.js wiring up router and pinia, and a .env.example with VITE_API_URL."

### Step 2.2 — Axios instance
**Prompt:**
> "Generate src/api/axios.js for a Vue 3 SPA connecting to a Laravel Sanctum API:
> - baseURL from import.meta.env.VITE_API_URL
> - withCredentials: true (for cookie auth)
> - response interceptor: on 401, clear Pinia auth store and redirect to /login
> - request interceptor: add Accept: application/json header
> Export as default named instance."

### Step 2.3 — Pinia auth store
**Prompt:**
> "Generate src/stores/auth.js as a Pinia store with:
> - state: user (null), isAuthenticated (computed from user)
> - actions: login(credentials), register(data), logout(), fetchUser()
> - fetchUser() is called on app mount to restore session from existing cookie
> - All actions use the axios instance from src/api/axios.js
> - Persist user to localStorage as fallback"

### Step 2.4 — Router with guards
**Prompt:**
> "Generate src/router/index.js for Vue Router 4 with routes:
> - / → redirect to /tasks if authenticated, else /login
> - /login → Login.vue (guest only)
> - /register → Register.vue (guest only)
> - /tasks → Tasks.vue (auth required)
> - /tasks/:id → TaskDetail.vue (auth required)
> Add a beforeEach navigation guard using the Pinia auth store. On first load, call fetchUser() to check session before deciding redirect."

### Step 2.5 — Pinia tasks store
**Prompt:**
> "Generate src/stores/tasks.js as a Pinia store with:
> - state: tasks (array), pagination (meta from Laravel paginator), loading, filters { status, category_id, search, page }
> - actions: fetchTasks(), createTask(data), updateTask(id, data), deleteTask(id), updateStatus(id, status)
> - deleteTask uses optimistic update: remove from array immediately, restore on API error
> - fetchTasks uses current filters state"

### Step 2.6 — Tasks page
**Prompt:**
> "Generate src/pages/Tasks.vue for Vue 3 using the tasks Pinia store:
> - Filter bar: status select (All/To Do/In Progress/Done), category select, search input with debounce
> - Task cards grid using TaskCard.vue component
> - SkeletonCard.vue shown while loading (3 placeholder cards)
> - Pagination component at bottom
> - Floating '+' button to open TaskForm modal
> - Empty state illustration when no tasks match filters"

### Step 2.7 — TaskCard component
**Prompt:**
> "Generate src/components/TaskCard.vue for Vue 3:
> Props: task object (id, title, description, status, priority, due_date, is_overdue, category)
> - Show title, truncated description, category badge (color from category.color), priority badge (low=gray, medium=amber, high=red), due date (red if is_overdue)
> - Status shown as a small pill
> - Edit and Delete buttons on hover
> - Clicking edit emits 'edit' event with task
> - Delete calls store.deleteTask with optimistic removal
> Style with Tailwind, clean card with border"

### Step 2.8 — TaskForm component
**Prompt:**
> "Generate src/components/TaskForm.vue for Vue 3 as a modal form:
> Props: task (null for create, object for edit), categories array
> - Fields: title (required), description (textarea), category_id (select), status (select), priority (select), due_date (date input)
> - Show API validation errors per field (422 response errors)
> - Loading state on submit button
> - Emits 'saved' on success, 'close' on cancel
> - Uses tasks Pinia store createTask/updateTask"

---

## Phase 2 — Frontend (React version) (3 days)

### Step 2.1R — Vite + React + TypeScript setup
**Prompt:**
> "Generate the setup commands for a React 18 + TypeScript + Vite + Tailwind CSS + React Router 6 + Zustand project called task-spa-react. Include: package.json dependencies, tailwind.config.ts, main.tsx, a .env.example with VITE_API_URL, and tsconfig.json essentials."

### Step 2.2R — Axios instance (TypeScript)
**Prompt:**
> "Generate src/api/axios.ts for React + TypeScript connecting to Laravel Sanctum:
> - baseURL from import.meta.env.VITE_API_URL
> - withCredentials: true
> - Response interceptor: 401 → clear Zustand auth store → navigate to /login (use a navigation ref pattern since we're outside React tree)
> - Request interceptor: Accept: application/json
> Export typed instance."

### Step 2.3R — Zustand auth store
**Prompt:**
> "Generate src/stores/authStore.ts as a Zustand store with TypeScript:
> - State: user (User | null), isAuthenticated (derived)
> - Actions: login(credentials), register(data), logout(), fetchUser()
> - Persist slice to localStorage using zustand/middleware persist
> - User type: { id, name, email }
> Use the axios instance from src/api/axios.ts"

### Step 2.4R — Protected route + router
**Prompt:**
> "Generate src/router/ProtectedRoute.tsx and the main React Router 6 setup in App.tsx:
> - ProtectedRoute: checks Zustand isAuthenticated, redirects to /login if not
> - GuestRoute: redirects to /tasks if already authenticated
> - Routes: /login, /register (guest), /tasks, /tasks/:id (protected)
> - On app mount, call fetchUser() to restore session"

### Step 2.5R — Zustand tasks store
**Prompt:**
> "Generate src/stores/taskStore.ts as a Zustand store with TypeScript:
> - State: tasks (Task[]), pagination (Laravel paginator meta), loading, filters { status?, category_id?, search?, page }
> - Actions: fetchTasks(), createTask(data), updateTask(id, data), deleteTask(id) with optimistic removal
> - Task type: { id, title, description, status, priority, due_date, is_overdue, category }
> Use immer middleware for immutable updates."

### Step 2.6R — Tasks page + components
**Prompt:**
> "Generate src/pages/Tasks.tsx for React 18 with hooks:
> - useEffect to fetchTasks on filter change (debounced search)
> - Filter bar: status, category, search
> - Map tasks to TaskCard components
> - Skeleton loading state
> - Pagination component driven by Laravel paginator meta
> - useTasks() custom hook that wraps the Zustand store
> All TypeScript, Tailwind styled."

---

## Phase 3 — Polish Features (3 days)

### Step 3.1 — Pagination component
**Prompt (Vue):**
> "Generate a Vue 3 Pagination.vue component that accepts props: meta (Laravel paginator meta: current_page, last_page, from, to, total) and emits 'page-change' with the new page number. Show previous/next buttons and page number pills. Disable prev on page 1, next on last page."

**Prompt (React):**
> "Generate a React Pagination.tsx component with props: meta (Laravel paginator meta) and onPageChange callback. Same UI: prev/next + page pills. TypeScript."

### Step 3.2 — Toast notifications
**Prompt (Vue):**
> "Show how to integrate vue-toastification into this Vue 3 app. Show the main.js setup and how to call useToast() in the tasks Pinia store after createTask (success), deleteTask (success), and on API errors (error toast with message from response)."

**Prompt (React):**
> "Show how to integrate react-hot-toast into the React app. Show Toaster placement in App.tsx and how to call toast.success / toast.error in the Zustand task store actions."

### Step 3.3 — Loading skeletons
**Prompt:**
> "Generate a SkeletonCard component (Vue or React) that matches TaskCard's dimensions but shows animated shimmer placeholders using Tailwind's animate-pulse. Show how to render 6 skeletons in a grid while tasks are loading."

### Step 3.4 — 401 interceptor + session expiry
**Prompt:**
> "Generate the complete axios response interceptor pattern for handling session expiry in a Sanctum SPA:
> - 401 on /api/user → clear auth store, redirect to /login
> - 401 on other endpoints → show toast 'Session expired, please log in again', then redirect
> - 419 (CSRF token mismatch) → refresh CSRF token and retry the request once
> Show both Vue (Pinia) and React (Zustand) versions."

### Step 3.5 — Kanban board (bonus)
**Prompt (Vue):**
> "Generate a Vue 3 KanbanBoard.vue component using vue-draggable-plus with 3 columns: To Do, In Progress, Done. Each column shows TaskCard components filtered by status. On drop, call tasks store updateStatus(taskId, newStatus) which sends PATCH /api/tasks/{id}/status."

**Prompt (React):**
> "Generate a React KanbanBoard.tsx using @dnd-kit/core with 3 droppable columns. On drag end, call taskStore.updateStatus(). TypeScript."

---

## Phase 4 — Seed, Test & Deploy (2 days)

### Step 4.1 — DatabaseSeeder
**Prompt:**
> "Generate a DatabaseSeeder for the Task Manager API that creates:
> - 1 demo user: name='Demo User', email='demo@example.com', password='password'
> - 3 categories for the demo user: Work (#3B82F6), Personal (#10B981), Shopping (#F59E0B)
> - 20 tasks spread across statuses (8 todo, 7 in_progress, 5 done) and priorities, with random category assignments and some with due dates in the past (to test is_overdue)"

### Step 4.2 — Feature tests
**Prompt:**
> "Generate Laravel feature tests for the Task API:
> - AuthTest: test register, login, logout, unauthenticated access returns 401
> - TaskTest: test index (pagination, filters), store (validation, 201), show (ownership check), update, destroy (204), 403 on other user's task
> Use RefreshDatabase, actingAs(), and assertJson patterns."

### Step 4.3 — Deploy API to Railway
**Prompt:**
> "Generate the complete Railway deployment setup for this Laravel 11 API:
> - railway.toml with build and start commands
> - List of all required env variables (APP_KEY, APP_URL, DB_*, SANCTUM_STATEFUL_DOMAINS, SESSION_DOMAIN, FRONTEND_URL, CORS_ALLOWED_ORIGINS)
> - Post-deploy commands: php artisan migrate --force, php artisan db:seed --force
> - How to add the MySQL plugin and connect it
> Show the exact SESSION_DOMAIN and SANCTUM_STATEFUL_DOMAINS values for a Vercel-hosted frontend."

### Step 4.4 — Deploy SPA to Vercel
**Prompt:**
> "Generate the Vercel deployment config for this Vite Vue/React SPA:
> - vercel.json with rewrites so all routes return index.html (SPA routing)
> - The VITE_API_URL environment variable setup in Vercel dashboard
> - How to set CORS_ALLOWED_ORIGINS on the Railway Laravel side to accept the Vercel domain
> - Note about withCredentials and same-site cookie requirements (need HTTPS on both ends)"

### Step 4.5 — README
**Prompt:**
> "Generate a professional README.md for the task-api GitHub repo:
> - Badges: PHP version, Laravel version, tests passing
> - Live API base URL
> - Postman collection button (link to file in repo)
> - Endpoint reference table (method, path, auth required, description)
> - Local setup (clone, composer install, .env, migrate, seed)
> - Demo credentials
> Also generate a shorter README for the task-spa-vue / task-spa-react repo with: live demo URL, screenshots section, local setup (npm install, .env, npm run dev)."

---

## Key Demo Flow for Loom Video (~2 mins)

1. Open live SPA URL — show Login page
2. Register a new account — redirected to tasks (show guard worked)
3. Create 3 tasks with different priorities and a category
4. Show pagination with 20 seeded tasks loaded
5. Filter by status "In Progress" — list updates instantly
6. Edit a task — show form with validation (clear the title and submit)
7. Delete a task — show it disappears immediately (optimistic UI)
8. Open Postman → show the collection → hit GET /api/tasks without auth → 401
9. Login via Postman → hit GET /api/tasks → paginated JSON response
10. End on GitHub repo showing clean folder structure + tests

---

## .env.example — Backend (`task-api`)

```env
APP_NAME="Task API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173

FRONTEND_URL=http://localhost:5173
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

## .env.example — Frontend (`task-spa-vue` or `task-spa-react`)

```env
VITE_API_URL=http://localhost:8000
```

---

## How to Use This Document With Claude

**Before any prompt, paste the stack context block at the top of this file.**

Then paste the specific step prompt. Each prompt is self-contained.

**Common follow-up prompts:**
- "Now generate the corresponding unit test for this."
- "Add TypeScript types to this." (for React steps)
- "Add error handling for network failures (no internet connection)."
- "Show me how this integrates with the Pinia/Zustand store I already have."

**Choosing Vue vs React:**
Use Vue if the Upwork job mentions Vue or Laravel (natural pairing, clients expect it).
Use React if the job mentions React, Next.js, or doesn't specify (React has more Upwork volume overall).
Building both repos gives you maximum coverage — same API backend, two frontend demos.
