<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
      <li v-for="(item, index) in items" :key="index">
        <div class="flex items-center">
          <!-- Separator -->
          <svg
            v-if="index > 0"
            class="flex-shrink-0 h-5 w-5 text-gray-400 mr-4"
            fill="currentColor"
            viewBox="0 0 20 20"
            aria-hidden="true"
          >
            <path
              fill-rule="evenodd"
              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
              clip-rule="evenodd"
            />
          </svg>
          
          <!-- Breadcrumb item -->
          <router-link
            v-if="!item.current && item.path"
            :to="item.path"
            class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200"
          >
            {{ item.name }}
          </router-link>
          
          <span
            v-else
            class="text-sm font-medium text-gray-900"
            aria-current="page"
          >
            {{ item.name }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  }
});
</script> 