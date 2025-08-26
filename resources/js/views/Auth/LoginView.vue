<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Logo and title -->
      <div>
        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">G</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link
            to="/register"
            class="font-medium text-blue-600 hover:text-blue-500"
          >
            create a new account
          </router-link>
        </p>
      </div>
      
      <!-- Login form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <!-- Email field -->
          <BaseInput
            v-model="form.email"
            type="email"
            label="Email address"
            placeholder="Enter your email"
            required
            :error-message="errors.email"
            left-icon="EnvelopeIcon"
            autocomplete="email"
          />
          
          <!-- Password field -->
          <BaseInput
            v-model="form.password"
            type="password"
            label="Password"
            placeholder="Enter your password"
            required
            :error-message="errors.password"
            left-icon="LockClosedIcon"
            autocomplete="current-password"
          />
        </div>
        
        <!-- Remember me and forgot password -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>
          
          <div class="text-sm">
            <router-link
              to="/forgot-password"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              Forgot your password?
            </router-link>
          </div>
        </div>
        
        <!-- Submit button -->
        <BaseButton
          type="submit"
          variant="primary"
          size="lg"
          :loading="authStore.loading"
          full-width
        >
          Sign in
        </BaseButton>
      </form>
      
      <!-- Demo credentials (remove in production) -->
      <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Demo Credentials:</h3>
        <div class="text-xs text-gray-600 space-y-1">
          <div><strong>Super Admin:</strong> admin@gohr.com / password</div>
          <div><strong>HR Manager:</strong> hr@gohr.com / password</div>
          <div><strong>Manager:</strong> manager@gohr.com / password</div>
          <div><strong>Employee:</strong> employee@gohr.com / password</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();
const authStore = useAuthStore();

// Form data
const form = reactive({
  email: '',
  password: '',
  remember: false
});

// Form errors
const errors = reactive({
  email: '',
  password: ''
});

// Validation
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  errors.email = '';
  errors.password = '';
  
  // Email validation
  if (!form.email) {
    errors.email = 'Email is required';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address';
    isValid = false;
  }
  
  // Password validation
  if (!form.password) {
    errors.password = 'Password is required';
    isValid = false;
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters';
    isValid = false;
  }
  
  return isValid;
};

// Handle form submission
const handleLogin = async () => {
  if (!validateForm()) {
    return;
  }
  
  try {
    const result = await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember
    });
    
    if (result.success) {
      // Redirect to dashboard
      router.push('/');
    } else {
      // Show error message
      console.error('Login failed:', result.message);
    }
  } catch (error) {
    console.error('Login error:', error);
  }
};

// Lifecycle
onMounted(() => {
  // Check if user is already authenticated
  if (authStore.isAuthenticated) {
    router.push('/');
  }
});
</script> 