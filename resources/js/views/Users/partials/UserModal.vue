<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>
      
      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ isEdit ? 'Edit User' : 'Create New User' }}
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  {{ isEdit ? 'Update user information and permissions.' : 'Add a new user to the system.' }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
            <!-- Name -->
            <div>
              <BaseInput
                v-model="form.name"
                label="Full Name"
                placeholder="Enter full name"
                required
                :error-message="errors.name"
              />
            </div>
            
            <!-- Email -->
            <div>
              <BaseInput
                v-model="form.email"
                type="email"
                label="Email Address"
                placeholder="Enter email address"
                required
                :error-message="errors.email"
              />
            </div>
            
            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Role <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.role"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.role }"
              >
                <option value="">Select a role</option>
                <option value="employee">Employee</option>
                <option value="manager">Manager</option>
                <option value="hr">HR Manager</option>
                <option value="super-admin" v-if="canAssignSuperAdmin">Super Admin</option>
              </select>
              <p v-if="errors.role" class="mt-2 text-sm text-red-600">{{ errors.role }}</p>
            </div>
            
            <!-- Organization -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Organization <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.organization_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.organization_id }"
              >
                <option value="">Select an organization</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.name }}
                </option>
              </select>
              <p v-if="errors.organization_id" class="mt-2 text-sm text-red-600">{{ errors.organization_id }}</p>
            </div>
            
            <!-- Password (only for new users) -->
            <div v-if="!isEdit">
              <BaseInput
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="Enter password"
                required
                :error-message="errors.password"
                help-text="Password must be at least 8 characters long"
              />
            </div>
            
            <!-- Password confirmation (only for new users) -->
            <div v-if="!isEdit">
              <BaseInput
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                placeholder="Confirm password"
                required
                :error-message="errors.password_confirmation"
              />
            </div>
          </form>
        </div>
        
        <!-- Modal actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <BaseButton
            @click="handleSubmit"
            variant="primary"
            size="lg"
            :loading="loading"
            class="w-full sm:w-auto sm:ml-3"
          >
            {{ isEdit ? 'Update User' : 'Create User' }}
          </BaseButton>
          <BaseButton
            @click="$emit('close')"
            variant="secondary"
            size="lg"
            class="w-full sm:w-auto mt-3 sm:mt-0"
          >
            Cancel
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'saved']);

const userStore = useUserStore();
const authStore = useAuthStore();

// Reactive state
const loading = ref(false);
const form = reactive({
  name: '',
  email: '',
  role: '',
  organization_id: '',
  password: '',
  password_confirmation: ''
});

const errors = reactive({
  name: '',
  email: '',
  role: '',
  organization_id: '',
  password: '',
  password_confirmation: ''
});

const organizations = ref([
  { id: 1, name: 'Main Office' },
  { id: 2, name: 'Branch Office' }
]);

// Computed properties
const canAssignSuperAdmin = computed(() => {
  return authStore.userRole === 'super-admin';
});

// Methods
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '');
  
  // Name validation
  if (!form.name.trim()) {
    errors.name = 'Name is required';
    isValid = false;
  }
  
  // Email validation
  if (!form.email.trim()) {
    errors.email = 'Email is required';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address';
    isValid = false;
  }
  
  // Role validation
  if (!form.role) {
    errors.role = 'Role is required';
    isValid = false;
  }
  
  // Organization validation
  if (!form.organization_id) {
    errors.organization_id = 'Organization is required';
    isValid = false;
  }
  
  // Password validation (only for new users)
  if (!props.isEdit) {
    if (!form.password) {
      errors.password = 'Password is required';
      isValid = false;
    } else if (form.password.length < 8) {
      errors.password = 'Password must be at least 8 characters long';
      isValid = false;
    }
    
    if (!form.password_confirmation) {
      errors.password_confirmation = 'Password confirmation is required';
      isValid = false;
    } else if (form.password !== form.password_confirmation) {
      errors.password_confirmation = 'Passwords do not match';
      isValid = false;
    }
  }
  
  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }
  
  loading.value = true;
  
  try {
    let result;
    
    if (props.isEdit) {
      result = await userStore.updateUser(props.user.id, {
        name: form.name,
        email: form.email,
        role: form.role,
        organization_id: form.organization_id
      });
    } else {
      result = await userStore.createUser({
        name: form.name,
        email: form.email,
        role: form.role,
        organization_id: form.organization_id,
        password: form.password,
        password_confirmation: form.password_confirmation
      });
    }
    
    if (result.success) {
      emit('saved', result.user);
    } else {
      // Handle validation errors from API
      if (result.errors) {
        Object.keys(result.errors).forEach(key => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = result.errors[key][0];
          }
        });
      }
    }
  } catch (error) {
    console.error('Form submission error:', error);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  Object.keys(form).forEach(key => {
    form[key] = '';
  });
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
};

// Watch for user prop changes
watch(() => props.user, (newUser) => {
  if (newUser && props.isEdit) {
    form.name = newUser.name || '';
    form.email = newUser.email || '';
    form.role = newUser.role || '';
    form.organization_id = newUser.organization_id || '';
  } else {
    resetForm();
  }
}, { immediate: true });

// Lifecycle
onMounted(() => {
  if (props.user && props.isEdit) {
    form.name = props.user.name || '';
    form.email = props.user.email || '';
    form.role = props.user.role || '';
    form.organization_id = props.user.organization_id || '';
  }
});
</script> 