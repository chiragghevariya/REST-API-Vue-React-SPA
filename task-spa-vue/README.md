# Task Manager SPA

![Vue](https://img.shields.io/badge/Vue-3.4-42b883?logo=vue.js&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-5.x-646cff?logo=vite&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.4-38bdf8?logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

A fully-featured Task Manager Single Page Application built with **Vue 3**, **Pinia**, **Vue Router 4**, and **Tailwind CSS**. Communicates with the [Task Manager API](../task-api/README.md).

**Live Demo:** `https://YOUR-VERCEL-URL.vercel.app`

---

## Features

- Token-based authentication (register, login, logout)
- Full task CRUD with optimistic UI updates
- Filter tasks by status, category, and free-text search (debounced)
- Paginated task list (10 per page)
- Quick status update without opening the edit form
- Category sidebar for fast filtering
- Dashboard with stats: total, by status, overdue count, completion rate
- Animated skeleton loading cards
- Toast notifications on all mutations
- Responsive layout (mobile FAB, desktop sidebar)
- Persistent auth (localStorage token)

---

## Local Setup

### Requirements

- Node 18+
- The [Task API](../task-api/) running locally on port 8000

### 1. Install dependencies

```bash
cd task-spa-vue
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
```

Default `.env`:
```
VITE_API_URL=http://localhost:8000
```

### 3. Start the development server

```bash
npm run dev
```

The SPA is now running at `http://localhost:5173`.

---

## Demo Credentials

```
Email:    demo@example.com
Password: password
```

---

## Build for Production

```bash
npm run build
```

Output goes to `dist/`. Deploy the `dist/` folder to any static host (Vercel, Netlify, etc.).

---

## Deployment (Vercel)

1. Push this repo to GitHub
2. Import the project into [Vercel](https://vercel.com)
3. Set build command: `npm run build`
4. Set output directory: `dist`
5. Add environment variable: `VITE_API_URL=https://your-api.railway.app`
6. The included `vercel.json` handles SPA client-side routing rewrites

---

## Project Structure

```
src/
  api/
    axios.js          # Configured axios instance with interceptors
  assets/
    main.css          # Tailwind base + component classes
  components/
    CategoryBadge.vue # Colored dot + name badge
    Pagination.vue    # Page navigation with ellipsis
    SkeletonCard.vue  # Shimmer loading placeholder
    TaskCard.vue      # Task grid card with hover actions
    TaskForm.vue      # Create / edit modal
    ToastContainer.vue# Documentation for vue-toastification
  layouts/
    AppLayout.vue     # Navbar + category sidebar + main slot
  pages/
    Dashboard.vue     # Stats overview
    Login.vue         # Login form
    Register.vue      # Registration form
    TaskDetail.vue    # Single task view with status controls
    Tasks.vue         # Filterable task grid
  router/
    index.js          # Vue Router 4 with auth guards
  stores/
    auth.js           # Pinia auth store (login/register/logout/fetchUser)
    tasks.js          # Pinia tasks store with optimistic updates
  App.vue
  main.js
```
