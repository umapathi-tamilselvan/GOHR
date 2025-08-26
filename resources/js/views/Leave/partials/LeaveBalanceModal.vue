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
                {{ isEdit ? 'Edit Leave Balance' : 'New Leave Balance' }}
              </h3>
              <button
                type="button"
                @click="$emit('close')"
                class="text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="h-6 w-6" />
              </button>
            </div>

            <!-- Form fields -->
            <div class="space-y-4">
              <!-- User -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Employee *
                </label>
                <select
                  v-model="form.user_id"
                  :disabled="isEdit"
                  required
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Select Employee</option>
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <p v-if="errors.user_id" class="mt-1 text-sm text-red-600">
                  {{ errors.user_id }}
                </p>
              </div>

              <!-- Leave Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Leave Type *
                </label>
                <select
                  v-model="form.leave_type_id"
                  :disabled="isEdit"
                  required
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Select Leave Type</option>
                  <option
                    v-for="leaveType in leaveTypes"
                    :key="leaveType.id"
                    :value="leaveType.id"
                  >
                    {{ leaveType.name }} ({{ leaveType.default_days }} days)
                  </option>
                </select>
                <p v-if="errors.leave_type_id" class="mt-1 text-sm text-red-600">
                  {{ errors.leave_type_id }}
                </p>
              </div>

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

              <!-- Total Days -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Total Days *
                </label>
                <input
                  v-model.number="form.total_days"
                  type="number"
                  step="0.5"
                  min="0"
                  required
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Enter total days"
                />
                <p v-if="errors.total_days" class="mt-1 text-sm text-red-600">
                  {{ errors.total_days }}
                </p>
              </div>

              <!-- Used Days -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Used Days
                </label>
                <input
                  v-model.number="form.used_days"
                  type="number"
                  step="0.5"
                  min="0"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Enter used days"
                />
                <p v-if="errors.used_days" class="mt-1 text-sm text-red-600">
                  {{ errors.used_days }}
                </p>
              </div>

              <!-- Remaining Days (calculated) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Remaining Days
                </label>
                <input
                  :value="remainingDays"
                  type="text"
                  readonly
                  class="block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm sm:text-sm"
                />
                <p class="mt-1 text-sm text-gray-500">
                  Automatically calculated based on total and used days
                </p>
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
              {{ isEdit ? 'Update' : 'Create' }}
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

// Props
const props = defineProps({
  balance: {
    type: Object,
    default: null,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
});

// Emits
const emit = defineEmits(['close', 'created', 'updated']);

// Stores
const leaveStore = useLeaveStore();
const userStore = useUserStore();
const toast = useToast();

// Reactive data
const loading = ref(false);
const errors = ref({});

const form = ref({
  user_id: '',
  leave_type_id: '',
  year: new Date().getFullYear(),
  total_days: 0,
  used_days: 0,
});

// Computed
const { leaveTypes } = leaveStore;
const { users } = userStore;

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let i = currentYear - 2; i <= currentYear + 2; i++) {
    years.push(i);
  }
  return years;
});

const remainingDays = computed(() => {
  const total = parseFloat(form.value.total_days) || 0;
  const used = parseFloat(form.value.used_days) || 0;
  return Math.max(0, total - used);
});

// Methods
const resetForm = () => {
  form.value = {
    user_id: '',
    leave_type_id: '',
    year: new Date().getFullYear(),
    total_days: 0,
    used_days: 0,
  };
  errors.value = {};
};

const populateForm = () => {
  if (props.balance) {
    form.value = {
      user_id: props.balance.user_id,
      leave_type_id: props.balance.leave_type_id,
      year: props.balance.year,
      total_days: props.balance.total_days,
      used_days: props.balance.used_days,
    };
  }
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.user_id) {
    errors.value.user_id = 'Employee is required';
  }

  if (!form.value.leave_type_id) {
    errors.value.leave_type_id = 'Leave type is required';
  }

  if (!form.value.year) {
    errors.value.year = 'Year is required';
  }

  if (form.value.total_days < 0) {
    errors.value.total_days = 'Total days cannot be negative';
  }

  if (form.value.used_days < 0) {
    errors.value.used_days = 'Used days cannot be negative';
  }

  if (form.value.used_days > form.value.total_days) {
    errors.value.used_days = 'Used days cannot exceed total days';
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
    let result;

    if (props.isEdit) {
      result = await leaveStore.updateLeaveBalance(props.balance.id, form.value);
    } else {
      result = await leaveStore.createLeaveBalance(form.value);
    }

    if (result.success) {
      if (props.isEdit) {
        emit('updated', result.data);
      } else {
        emit('created', result.data);
      }
    } else {
      if (result.errors) {
        errors.value = result.errors;
      } else {
        toast.error(result.error || 'An error occurred');
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
watch(() => props.balance, () => {
  if (props.balance) {
    populateForm();
  } else {
    resetForm();
  }
}, { immediate: true });

// Lifecycle
onMounted(async () => {
  await Promise.all([
    leaveStore.fetchLeaveTypes(),
    userStore.fetchUsers(),
  ]);
});
</script> 