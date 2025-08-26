<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <form @submit.prevent="handleSubmit">
          <!-- Header -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Initialize Leave Balances
              </h3>
              <button
                type="button"
                @click="$emit('close')"
                class="text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="h-6 w-6" />
              </button>
            </div>

            <div class="mb-4">
              <p class="text-sm text-gray-600">
                This will create leave balances for all employees and leave types for the specified year.
                Existing balances for the year will be updated.
              </p>
            </div>

            <!-- Form fields -->
            <div class="space-y-4">
              <!-- Year -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Year *
                </label>
                <select
                  v-model="form.year"
                  required
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
                <p v-if="errors.year" class="mt-1 text-sm text-red-600">
                  {{ errors.year }}
                </p>
              </div>

              <!-- Organization -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Organization
                </label>
                <select
                  v-model="form.organization_id"
                  required
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Select Organization</option>
                  <option
                    v-for="org in organizations"
                    :key="org.id"
                    :value="org.id"
                  >
                    {{ org.name }}
                  </option>
                </select>
                <p v-if="errors.organization_id" class="mt-1 text-sm text-red-600">
                  {{ errors.organization_id }}
                </p>
              </div>

              <!-- Override existing -->
              <div class="flex items-center">
                <input
                  v-model="form.override_existing"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 block text-sm text-gray-900">
                  Override existing balances for this year
                </label>
              </div>

              <!-- Summary -->
              <div v-if="summary" class="bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Summary</h4>
                <div class="text-sm text-gray-600 space-y-1">
                  <p>• {{ summary.total_users }} employees</p>
                  <p>• {{ summary.total_leave_types }} leave types</p>
                  <p>• {{ summary.total_balances }} leave balances will be created</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="loading"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Initialize
            </button>
            <button
              type="button"
              @click="$emit('close')"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useUserStore } from '@/stores/user';
import { useToast } from 'vue-toastification';
import { XMarkIcon } from '@heroicons/vue/24/outline';

// Emits
const emit = defineEmits(['close', 'initialized']);

// Stores
const leaveStore = useLeaveStore();
const userStore = useUserStore();
const toast = useToast();

// Reactive data
const loading = ref(false);
const errors = ref({});
const summary = ref(null);

const form = ref({
  year: new Date().getFullYear(),
  organization_id: '',
  override_existing: false,
});

// Computed
const { leaveTypes } = leaveStore;
const { users, organizations } = userStore;

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let i = currentYear - 2; i <= currentYear + 2; i++) {
    years.push(i);
  }
  return years;
});

// Methods
const resetForm = () => {
  form.value = {
    year: new Date().getFullYear(),
    organization_id: '',
    override_existing: false,
  };
  errors.value = {};
  summary.value = null;
};

const calculateSummary = () => {
  if (!form.value.organization_id || !form.value.year) {
    summary.value = null;
    return;
  }

  const orgUsers = users.filter(user => user.organization_id == form.value.organization_id);
  const orgLeaveTypes = leaveTypes.filter(lt => lt.organization_id == form.value.organization_id);

  summary.value = {
    total_users: orgUsers.length,
    total_leave_types: orgLeaveTypes.length,
    total_balances: orgUsers.length * orgLeaveTypes.length,
  };
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.year) {
    errors.value.year = 'Year is required';
  }

  if (!form.value.organization_id) {
    errors.value.organization_id = 'Organization is required';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  loading.value = true;
  errors.value = {};

  try {
    const result = await leaveStore.initializeYear(form.value);

    if (result.success) {
      toast.success('Leave balances initialized successfully');
      emit('initialized', result.data);
    } else {
      if (result.errors) {
        errors.value = result.errors;
      } else {
        toast.error(result.error || 'Failed to initialize leave balances');
      }
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error('Error:', error);
  } finally {
    loading.value = false;
  }
};

// Watchers
watch([() => form.value.year, () => form.value.organization_id], () => {
  calculateSummary();
});

// Lifecycle
onMounted(async () => {
  await Promise.all([
    leaveStore.fetchLeaveTypes(),
    userStore.fetchUsers(),
    userStore.fetchOrganizations(),
  ]);
  
  // Set default organization if available
  if (organizations.length > 0) {
    form.value.organization_id = organizations[0].id;
  }
});
</script> 