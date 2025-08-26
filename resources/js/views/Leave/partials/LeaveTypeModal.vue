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
              <TagIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ isEdit ? 'Edit Leave Type' : 'New Leave Type' }}
              </h3>
              <div class="mt-2">
                <form @submit.prevent="submitForm" class="space-y-4">
                  <!-- Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Name *
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      maxlength="100"
                      placeholder="e.g., Annual Leave, Sick Leave"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Description
                    </label>
                    <textarea
                      v-model="form.description"
                      rows="3"
                      maxlength="500"
                      placeholder="Optional description of the leave type..."
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    ></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ form.description.length }}/500 characters
                    </p>
                  </div>

                  <!-- Default Days -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Default Days *
                    </label>
                    <input
                      v-model="form.default_days"
                      type="number"
                      required
                      min="0"
                      max="365"
                      placeholder="0"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                      Number of days allocated by default for this leave type
                    </p>
                  </div>

                  <!-- Color -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Color *
                    </label>
                    <div class="mt-1 flex items-center space-x-3">
                      <input
                        v-model="form.color"
                        type="color"
                        required
                        class="h-10 w-20 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      />
                      <input
                        v-model="form.color"
                        type="text"
                        required
                        pattern="^#[0-9A-Fa-f]{6}$"
                        placeholder="#3B82F6"
                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                      Color used to represent this leave type in calendar and reports
                    </p>
                  </div>

                  <!-- Approval Required -->
                  <div>
                    <div class="flex items-center">
                      <input
                        v-model="form.requires_approval"
                        type="checkbox"
                        id="requires_approval"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      />
                      <label for="requires_approval" class="ml-2 block text-sm text-gray-900">
                        Requires approval
                      </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                      If checked, leave requests of this type will require manager/HR approval
                    </p>
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
            {{ loading ? (isEdit ? 'Updating...' : 'Creating...') : (isEdit ? 'Update Leave Type' : 'Create Leave Type') }}
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
import { TagIcon } from '@heroicons/vue/24/outline';

// Props and emits
const props = defineProps({
  leaveType: {
    type: Object,
    default: null,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'created', 'updated']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const form = ref({
  name: '',
  description: '',
  default_days: 0,
  color: '#3B82F6',
  requires_approval: true,
});

const loading = ref(false);

// Computed
const isFormValid = computed(() => {
  return form.value.name.trim() &&
         form.value.default_days >= 0 &&
         form.value.default_days <= 365 &&
         /^#[0-9A-Fa-f]{6}$/.test(form.value.color);
});

// Methods
const submitForm = async () => {
  if (!isFormValid.value) {
    toast.error('Please fill in all required fields correctly');
    return;
  }

  loading.value = true;

  try {
    let result;
    
    if (props.isEdit) {
      result = await leaveStore.updateLeaveType(props.leaveType.id, form.value);
    } else {
      result = await leaveStore.createLeaveType(form.value);
    }
    
    if (result.success) {
      if (props.isEdit) {
        emit('updated', result.data);
      } else {
        emit('created', result.data);
      }
    } else {
      toast.error(result.error || `Failed to ${props.isEdit ? 'update' : 'create'} leave type`);
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error(`Error ${props.isEdit ? 'updating' : 'creating'} leave type:`, error);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    default_days: 0,
    color: '#3B82F6',
    requires_approval: true,
  };
};

const initializeForm = () => {
  if (props.leaveType) {
    form.value = {
      name: props.leaveType.name,
      description: props.leaveType.description || '',
      default_days: props.leaveType.default_days,
      color: props.leaveType.color,
      requires_approval: props.leaveType.requires_approval,
    };
  } else {
    resetForm();
  }
};

// Lifecycle
onMounted(() => {
  initializeForm();
});

// Watch for prop changes
watch(() => props.leaveType, () => {
  initializeForm();
});
</script> 