<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-gray-900">Create an account</h1>
          <p class="text-gray-500 text-sm mt-1">Start managing your tasks today</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" novalidate>
          <!-- Name -->
          <div class="mb-4">
            <label for="name" class="form-label">Full name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              autocomplete="name"
              placeholder="Jane Doe"
              class="form-input"
              :class="{ 'border-red-400': errors.name }"
            />
            <p v-if="errors.name" class="form-error">{{ errors.name[0] }}</p>
          </div>

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
              :class="{ 'border-red-400': errors.email }"
            />
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <!-- Password -->
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              placeholder="Min. 8 characters"
              class="form-input"
              :class="{ 'border-red-400': errors.password }"
            />
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="mb-6">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              placeholder="Repeat your password"
              class="form-input"
              :class="{ 'border-red-400': errors.password_confirmation }"
            />
            <p v-if="errors.password_confirmation" class="form-error">
              {{ errors.password_confirmation[0] }}
            </p>
          </div>

          <!-- Submit -->
          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ loading ? 'Creating account…' : 'Create account' }}
          </button>
        </form>

        <!-- Footer link -->
        <p class="text-center text-sm text-gray-500 mt-6">
          Already have an account?
          <RouterLink to="/login" class="font-medium text-blue-600 hover:text-blue-700">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const router = useRouter()
const toast = useToast()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const errors = ref({})
const loading = ref(false)

async function handleRegister() {
  errors.value = {}
  loading.value = true

  try {
    await authStore.register(form)
    toast.success('Account created! Welcome aboard.')
    router.push('/tasks')
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
