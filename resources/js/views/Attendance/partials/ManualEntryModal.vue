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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Manual Attendance Entry
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Create a manual attendance record for an employee.
                </p>
              </div>
            </div>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
            <!-- Employee Selection -->
            <div>
              <label for="user_id" class="block text-sm font-medium text-gray-700">
                Employee
              </label>
              <select
                id="user_id"
                v-model="form.user_id"
                required
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="">Select Employee</option>
                <option
                  v-for="user in availableUsers"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>

            <!-- Date -->
            <div>
              <label for="date" class="block text-sm font-medium text-gray-700">
                Date
              </label>
              <input
                type="date"
                id="date"
                v-model="form.date"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- Check In Time -->
            <div>
              <label for="check_in" class="block text-sm font-medium text-gray-700">
                Check In Time
              </label>
              <input
                type="time"
                id="check_in"
                v-model="form.check_in"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- Check Out Time -->
            <div>
              <label for="check_out" class="block text-sm font-medium text-gray-700">
                Check Out Time (Optional)
              </label>
              <input
                type="time"
                id="check_out"
                v-model="form.check_out"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- Notes -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700">
                Notes (Optional)
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Any additional notes about this attendance entry..."
              ></textarea>
            </div>
          </form>
        </div>

        <!-- Modal actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <BaseButton
            type="submit"
            variant="primary"
            :loading="loading"
            @click="handleSubmit"
            class="w-full sm:w-auto sm:ml-3"
          >
            Create Entry
          </BaseButton>
          <BaseButton
            type="button"
            variant="secondary"
            @click="$emit('close')"
            class="mt-3 w-full sm:mt-0 sm:w-auto"
          >
            Cancel
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import BaseButton from '@/components/ui/BaseButton.vue';

const emit = defineEmits(['close', 'saved']);

const authStore = useAuthStore();

// Form data
const form = reactive({
  user_id: '',
  date: new Date().toISOString().split('T')[0],
  check_in: '09:00',
  check_out: '',
  notes: ''
});

// State
const loading = ref(false);
const availableUsers = ref([]);

// Methods
const handleSubmit = async () => {
  if (!form.user_id || !form.date || !form.check_in) {
    return;
  }

  loading.value = true;
  try {
    // TODO: Implement actual API call
    console.log('Creating manual attendance entry:', form);
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Emit success event
    emit('saved');
    
  } catch (error) {
    console.error('Failed to create manual attendance entry:', error);
  } finally {
    loading.value = false;
  }
};

const fetchAvailableUsers = async () => {
  try {
    // TODO: Implement actual API call to fetch users
    // For now, use mock data
    availableUsers.value = [
      { id: 1, name: 'John Doe', email: 'john@example.com' },
      { id: 2, name: 'Jane Smith', email: 'jane@example.com' },
      { id: 3, name: 'Bob Johnson', email: 'bob@example.com' }
    ];
  } catch (error) {
    console.error('Failed to fetch users:', error);
  }
};

// Lifecycle
onMounted(() => {
  fetchAvailableUsers();
});
</script> 