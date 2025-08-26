<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="id"
      :class="[
        'block text-sm font-medium text-gray-700 mb-2',
        { 'text-red-600': hasError }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <!-- Input Container -->
    <div class="relative">
      <!-- Left Icon -->
      <div
        v-if="leftIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component
          :is="leftIcon"
          :class="[
            'h-5 w-5',
            hasError ? 'text-red-400' : 'text-gray-400'
          ]"
        />
      </div>
      
      <!-- Input Field -->
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :class="[
          'block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-offset-0 transition-colors duration-200',
          {
            'pl-10': leftIcon,
            'pr-10': rightIcon || clearable,
            'border-red-300 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 focus:border-green-500 focus:ring-green-500': isValid,
            'focus:border-blue-500 focus:ring-blue-500': !hasError && !isValid,
            'bg-gray-50 cursor-not-allowed': disabled,
            'bg-gray-50 cursor-default': readonly
          }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        v-bind="$attrs"
      />
      
      <!-- Right Icon -->
      <div
        v-if="rightIcon && !clearable"
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
      >
        <component
          :is="rightIcon"
          :class="[
            'h-5 w-5',
            hasError ? 'text-red-400' : 'text-gray-400'
          ]"
        />
      </div>
      
      <!-- Clear Button -->
      <button
        v-if="clearable && modelValue"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
        @click="clearInput"
      >
        <svg
          class="h-5 w-5 text-gray-400 hover:text-gray-600"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
      
      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <svg
          class="animate-spin h-5 w-5 text-gray-400"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
      </div>
    </div>
    
    <!-- Error Message -->
    <p
      v-if="hasError && errorMessage"
      class="mt-2 text-sm text-red-600"
    >
      {{ errorMessage }}
    </p>
    
    <!-- Help Text -->
    <p
      v-if="helpText && !hasError"
      class="mt-2 text-sm text-gray-500"
    >
      {{ helpText }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substr(2, 9)}`
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  leftIcon: {
    type: [String, Object],
    default: null
  },
  rightIcon: {
    type: [String, Object],
    default: null
  },
  errorMessage: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  autocomplete: {
    type: String,
    default: 'off'
  }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'input']);

const hasError = computed(() => !!props.errorMessage);
const isValid = computed(() => !hasError.value && props.modelValue && props.modelValue.toString().length > 0);

const handleInput = (event) => {
  emit('update:modelValue', event.target.value);
  emit('input', event);
};

const handleBlur = (event) => {
  emit('blur', event);
};

const handleFocus = (event) => {
  emit('focus', event);
};

const clearInput = () => {
  emit('update:modelValue', '');
};
</script> 