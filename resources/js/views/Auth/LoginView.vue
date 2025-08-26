<template>
  <div class="login-container">
    <v-container fluid class="fill-height">
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="4" lg="4">
          <!-- Login Card -->
          <v-card class="login-card" elevation="8">
            <v-card-text class="pa-8">
              <!-- Logo and Title -->
              <div class="text-center mb-6">
                <v-avatar size="80" color="primary" class="mb-4">
                  <span class="text-h3 font-weight-bold text-white">G</span>
                </v-avatar>
                <h1 class="text-h4 font-weight-bold primary--text mb-2">
                  Welcome to GOHR
                </h1>
                <p class="text-body-1 text-medium-emphasis">
                  HR Management System
                </p>
              </div>

              <!-- Login Form -->
              <v-form @submit.prevent="handleLogin" v-model="isFormValid">
                <v-text-field
                  v-model="form.email"
                  label="Email Address"
                  type="email"
                  prepend-inner-icon="mdi-email"
                  variant="outlined"
                  :rules="emailRules"
                  required
                  class="mb-4"
                />

                <v-text-field
                  v-model="form.password"
                  label="Password"
                  type="password"
                  prepend-inner-icon="mdi-lock"
                  variant="outlined"
                  :rules="passwordRules"
                  required
                  class="mb-6"
                />

                <!-- Remember Me & Forgot Password -->
                <div class="d-flex justify-space-between align-center mb-6">
                  <v-checkbox
                    v-model="form.remember"
                    label="Remember me"
                    color="primary"
                    hide-details
                  />
                  <v-btn
                    variant="text"
                    color="primary"
                    @click="forgotPassword"
                    class="text-none"
                  >
                    Forgot Password?
                  </v-btn>
                </div>

                <!-- Login Button -->
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="isLoading"
                  :disabled="!isFormValid"
                  class="mb-4"
                >
                  <v-icon left>mdi-login</v-icon>
                  Sign In
                </v-btn>

                <!-- Divider -->
                <v-divider class="my-4">
                  <span class="text-caption text-medium-emphasis px-4">OR</span>
                </v-divider>

                <!-- Register Link -->
                <div class="text-center">
                  <span class="text-body-2 text-medium-emphasis">
                    Don't have an account?
                  </span>
                  <v-btn
                    variant="text"
                    color="primary"
                    @click="goToRegister"
                    class="text-none ml-2"
                  >
                    Sign Up
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>

          <!-- Footer -->
          <div class="text-center mt-4">
            <p class="text-caption text-medium-emphasis">
              © 2025 GOHR HR Management System. All rights reserved.
            </p>
          </div>
        </v-col>
      </v-row>
    </v-container>

    <!-- Error Snackbar -->
    <v-snackbar
      v-model="showError"
      color="error"
      timeout="5000"
      location="top"
    >
      {{ errorMessage }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="showError = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Form data
const form = reactive({
  email: '',
  password: '',
  remember: false
});

// Form validation
const isFormValid = ref(false);
const emailRules = [
  v => !!v || 'Email is required',
  v => /.+@.+\..+/.test(v) || 'Email must be valid'
];

const passwordRules = [
  v => !!v || 'Password is required',
  v => v.length >= 6 || 'Password must be at least 6 characters'
];

// UI state
const isLoading = ref(false);
const showError = ref(false);
const errorMessage = ref('');

// Methods
const handleLogin = async () => {
  if (!isFormValid.value) return;

  isLoading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember
    });

    // Redirect to dashboard on success
    router.push('/dashboard');
  } catch (error) {
    errorMessage.value = error.message || 'Login failed. Please try again.';
    showError.value = true;
  } finally {
    isLoading.value = false;
  }
};

const forgotPassword = () => {
  router.push('/forgot-password');
};

const goToRegister = () => {
  router.push('/register');
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  border-radius: 16px;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

.v-text-field :deep(.v-field__outline) {
  --v-field-border-opacity: 0.3;
}

.v-btn--variant-elevated {
  box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.39);
}

.v-btn--variant-elevated:hover {
  box-shadow: 0 6px 20px 0 rgba(99, 102, 241, 0.49);
}
</style> 