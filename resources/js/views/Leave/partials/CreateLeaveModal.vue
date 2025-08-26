<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
              <CalendarIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                New Leave Request
              </h3>
              <div class="mt-2">
                <form @submit.prevent="submitForm" class="space-y-4">
                  <!-- Leave Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Leave Type *
                    </label>
                    <select
                      v-model="form.leave_type_id"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
                  </div>

                  <!-- Start Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Start Date *
                    </label>
                    <input
                      v-model="form.start_date"
                      type="date"
                      required
                      :min="today"
                      @change="calculateDays"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- End Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      End Date *
                    </label>
                    <input
                      v-model="form.end_date"
                      type="date"
                      required
                      :min="form.start_date"
                      @change="calculateDays"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Total Days -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Total Days
                    </label>
                    <input
                      v-model="form.total_days"
                      type="number"
                      readonly
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm"
                    />
                  </div>

                  <!-- Reason -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Reason *
                    </label>
                    <textarea
                      v-model="form.reason"
                      rows="3"
                      required
                      maxlength="1000"
                      placeholder="Please provide a reason for your leave request..."
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    ></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ form.reason.length }}/1000 characters
                    </p>
                  </div>

                  <!-- Leave Balance Info -->
                  <div v-if="selectedLeaveType && leaveBalance" class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <InformationCircleIcon class="h-5 w-5 text-blue-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                          Leave Balance Information
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                          <p>Available: {{ leaveBalance.remaining_days }} days</p>
                          <p>Used: {{ leaveBalance.used_days }} days</p>
                          <p>Total: {{ leaveBalance.total_days }} days</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Warning if insufficient balance -->
                  <div v-if="showInsufficientBalanceWarning" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                          Insufficient Leave Balance
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                          <p>You only have {{ leaveBalance?.remaining_days || 0 }} days available for this leave type.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="submit"
            @click="submitForm"
            :disabled="loading || !isFormValid"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Creating...' : 'Create Leave Request' }}
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import {
  CalendarIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

// Props and emits
const emit = defineEmits(['close', 'created']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const form = ref({
  leave_type_id: '',
  start_date: '',
  end_date: '',
  total_days: 0,
  reason: '',
});

const loading = ref(false);

// Computed
const { leaveTypes, leaveBalances } = leaveStore;

const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const selectedLeaveType = computed(() => {
  return leaveTypes.find(lt => lt.id == form.value.leave_type_id);
});

const leaveBalance = computed(() => {
  if (!form.value.leave_type_id) return null;
  
  const currentYear = new Date().getFullYear();
  return leaveBalances.find(lb => 
    lb.leave_type_id == form.value.leave_type_id && 
    lb.year == currentYear
  );
});

const showInsufficientBalanceWarning = computed(() => {
  if (!leaveBalance.value || !form.value.total_days) return false;
  return form.value.total_days > leaveBalance.value.remaining_days;
});

const isFormValid = computed(() => {
  return form.value.leave_type_id &&
         form.value.start_date &&
         form.value.end_date &&
         form.value.reason.trim() &&
         form.value.total_days > 0 &&
         !showInsufficientBalanceWarning.value;
});

// Methods
const calculateDays = () => {
  if (form.value.start_date && form.value.end_date) {
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    form.value.total_days = diffDays + 1; // Include both start and end dates
  }
};

const submitForm = async () => {
  if (!isFormValid.value) {
    toast.error('Please fill in all required fields correctly');
    return;
  }

  loading.value = true;

  try {
    const result = await leaveStore.createLeave(form.value);
    
    if (result.success) {
      toast.success('Leave request created successfully');
      emit('created', result.data);
    } else {
      toast.error(result.error || 'Failed to create leave request');
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error('Error creating leave:', error);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = {
    leave_type_id: '',
    start_date: '',
    end_date: '',
    total_days: 0,
    reason: '',
  };
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    leaveStore.fetchLeaveTypes(),
    leaveStore.fetchLeaveBalances({ year: new Date().getFullYear() }),
  ]);
});

// Watch for form changes
watch(() => form.value.leave_type_id, () => {
  if (form.value.leave_type_id) {
    // Refresh leave balances for the selected leave type
    leaveStore.fetchLeaveBalances({ 
      year: new Date().getFullYear(),
      leave_type_id: form.value.leave_type_id 
    });
  }
});

// Reset form when modal closes
watch(() => emit, () => {
  resetForm();
});
</script> 