<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
          <p class="text-gray-500 text-sm mt-1">Sign in to your Task Manager account</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" novalidate>
          <!-- Email -->
          <div class="mb-4">
            <label for="email" class="form-label">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="you@example.com"
              class="form-input"
              :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': errors.email }"
            />
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              placeholder="••••••••"
              class="form-input"
              :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': errors.password }"
            />
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <!-- Submit -->
          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>

        <!-- Divider + demo credentials hint -->
        <div class="mt-4 p-3 bg-blue-50 rounded-lg text-xs text-blue-700 text-center">
          Demo: <strong>demo@example.com</strong> / <strong>password</strong>
        </div>

        <!-- Footer link -->
        <p class="text-center text-sm text-gray-500 mt-6">
          Don't have an account?
          <RouterLink to="/register" class="font-medium text-blue-600 hover:text-blue-700">Create one</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const form = reactive({ email: '', password: '' })
const errors = ref({})
const loading = ref(false)

async function handleLogin() {
  errors.value = {}
  loading.value = true

  try {
    await authStore.login(form)
    const redirect = route.query.redirect || '/tasks'
    router.push(redirect)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
    } else {
      toast.error('Something went wrong. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>
