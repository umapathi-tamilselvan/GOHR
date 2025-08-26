<template>
  <v-app>
    <!-- Navigation Drawer -->
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail"
      permanent
      @click="rail = false"
    >
      <!-- Logo Section -->
      <v-list-item
        prepend-avatar="https://ui-avatars.com/api/?name=G&background=6366f1&color=fff&size=40"
        :title="rail ? '' : 'GOHR'"
        subtitle="HR Management System"
        nav
      >
        <template v-slot:append>
          <v-btn
            variant="text"
            icon="mdi-chevron-left"
            @click.stop="rail = !rail"
          />
        </template>
      </v-list-item>

      <v-divider class="my-2" />

      <!-- User Info -->
      <v-list-item
        v-if="userData"
        :prepend-avatar="`https://ui-avatars.com/api/?name=${userData.name}&background=8b5cf6&color=fff&size=40`"
        :title="rail ? '' : userData.name"
        :subtitle="rail ? '' : userData.role"
        nav
      />

      <v-divider class="my-2" />

      <!-- Navigation Menu -->
      <v-list density="compact" nav>
        <v-list-item
          v-for="item in navigationItems"
          :key="item.title"
          :to="item.to"
          :prepend-icon="item.icon"
          :title="rail ? '' : item.title"
          :value="item.title"
          rounded="lg"
          class="mb-1"
        />
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar
      elevation="1"
      color="white"
      class="border-b"
    >
      <v-app-bar-nav-icon @click="drawer = !drawer" />
      
      <v-toolbar-title class="d-flex align-center">
        <v-avatar size="32" color="primary" class="mr-3">
          <span class="text-white font-weight-bold">G</span>
        </v-avatar>
        <span class="text-h6 font-weight-bold primary--text">GOHR</span>
      </v-toolbar-title>

      <v-spacer />

      <!-- Search Bar -->
      <v-text-field
        v-model="searchQuery"
        prepend-inner-icon="mdi-magnify"
        placeholder="Search..."
        variant="outlined"
        density="compact"
        hide-details
        class="mx-4"
        style="max-width: 300px;"
      />

      <!-- Notifications -->
      <v-btn icon class="mr-2">
        <v-badge
          :content="notifications.length"
          :model-value="notifications.length > 0"
          color="error"
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn
            v-bind="props"
            variant="text"
            class="text-none"
          >
            <v-avatar size="32" color="primary" class="mr-2">
              <span class="text-white font-weight-bold">
                {{ userInitials }}
              </span>
            </v-avatar>
            <span class="mr-2">{{ userData?.name || 'User' }}</span>
            <v-icon>mdi-chevron-down</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item
            prepend-icon="mdi-account"
            title="Profile"
            @click="navigateToProfile"
          />
          <v-list-item
            prepend-icon="mdi-cog"
            title="Settings"
            @click="navigateToSettings"
          />
          <v-divider />
          <v-list-item
            prepend-icon="mdi-logout"
            title="Logout"
            @click="logout"
          />
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Main Content -->
    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-6">
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const drawer = ref(true);
const rail = ref(false);
const searchQuery = ref('');

// User data
const userData = computed(() => authStore.user);
const userInitials = computed(() => {
  if (!userData.value?.name) return 'U';
  return userData.value.name.split(' ').map(n => n[0]).join('').toUpperCase();
});

// Navigation items
const navigationItems = computed(() => [
  {
    title: 'Dashboard',
    icon: 'mdi-view-dashboard',
    to: '/dashboard'
  },
  {
    title: 'Attendance',
    icon: 'mdi-clock',
    to: '/attendance'
  },
  {
    title: 'Leave Management',
    icon: 'mdi-calendar',
    to: '/leave'
  },
  {
    title: 'Users',
    icon: 'mdi-account-group',
    to: '/users'
  },
  {
    title: 'Reports',
    icon: 'mdi-chart-bar',
    to: '/reports'
  },
  {
    title: 'Profile',
    icon: 'mdi-account',
    to: '/profile'
  }
]);

// Mock notifications
const notifications = ref([
  { id: 1, message: 'New leave request from John Doe', time: '2 min ago' },
  { id: 2, message: 'Attendance report ready', time: '1 hour ago' }
]);

// Methods
const navigateToProfile = () => {
  router.push('/profile');
};

const navigateToSettings = () => {
  // TODO: Implement settings navigation
  console.log('Navigate to settings');
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

// Lifecycle
onMounted(async () => {
  await authStore.checkAuth();
});
</script>

<style scoped>
.v-navigation-drawer {
  border-right: 1px solid rgba(0, 0, 0, 0.12);
}

.v-app-bar {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.v-list-item--active {
  background-color: rgb(99, 102, 241, 0.1) !important;
  color: rgb(99, 102, 241) !important;
}

.v-list-item--active .v-icon {
  color: rgb(99, 102, 241) !important;
}
</style> 