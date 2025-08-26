<template>
  <AppLayout>
    <template #default>
      <!-- Page header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
            <p class="mt-2 text-sm text-gray-600">
              Manage user accounts, roles, and permissions across your organization.
            </p>
          </div>
          <BaseButton
            @click="showCreateModal = true"
            variant="primary"
            size="lg"
            v-if="canCreateUsers"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add User
          </BaseButton>
        </div>
      </div>
      
      <!-- Search and filters -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
            <BaseInput
              v-model="searchQuery"
              placeholder="Search by name or email..."
              left-icon="MagnifyingGlassIcon"
              clearable
              @input="handleSearch"
            />
          </div>
          
          <!-- Role filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Role</label>
            <select
              v-model="selectedRole"
              @change="handleRoleFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Roles</option>
              <option value="super-admin">Super Admin</option>
              <option value="hr">HR Manager</option>
              <option value="manager">Manager</option>
              <option value="employee">Employee</option>
            </select>
          </div>
          
          <!-- Organization filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Organization</label>
            <select
              v-model="selectedOrganization"
              @change="handleOrganizationFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Organizations</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.name }}
              </option>
            </select>
          </div>
        </div>
        
        <!-- Clear filters -->
        <div class="mt-4 flex justify-end">
          <BaseButton
            @click="clearFilters"
            variant="secondary"
            size="sm"
          >
            Clear Filters
          </BaseButton>
        </div>
      </div>
      
      <!-- Users table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">
            Users ({{ userStore.totalUsers }})
          </h3>
        </div>
        
        <!-- Loading state -->
        <div v-if="userStore.loading" class="p-8 text-center">
          <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading users...
          </div>
        </div>
        
        <!-- Users table -->
        <div v-else-if="userStore.hasUsers" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  User
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Organization
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Created
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in userStore.filteredUsers" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-blue-600 font-medium text-sm">
                        {{ getUserInitials(user.name) }}
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getRoleBadgeClasses(user.role)">
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.organization?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                    {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <BaseButton
                      @click="viewUser(user)"
                      variant="secondary"
                      size="sm"
                    >
                      View
                    </BaseButton>
                    <BaseButton
                      v-if="canEditUser(user)"
                      @click="editUser(user)"
                      variant="primary"
                      size="sm"
                    >
                      Edit
                    </BaseButton>
                    <BaseButton
                      v-if="canDeleteUser(user)"
                      @click="deleteUser(user)"
                      variant="danger"
                      size="sm"
                    >
                      Delete
                    </BaseButton>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty state -->
        <div v-else class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ searchQuery || selectedRole || selectedOrganization ? 'Try adjusting your filters.' : 'Get started by creating a new user.' }}
          </p>
        </div>
        
        <!-- Pagination -->
        <div v-if="userStore.hasUsers" class="px-6 py-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ (userStore.pagination.current_page - 1) * userStore.pagination.per_page + 1 }} to 
              {{ Math.min(userStore.pagination.current_page * userStore.pagination.per_page, userStore.pagination.total) }} of 
              {{ userStore.pagination.total }} results
            </div>
            <div class="flex space-x-2">
              <BaseButton
                @click="previousPage"
                :disabled="userStore.pagination.current_page === 1"
                variant="secondary"
                size="sm"
              >
                Previous
              </BaseButton>
              <BaseButton
                @click="nextPage"
                :disabled="userStore.pagination.current_page === userStore.pagination.last_page"
                variant="secondary"
                size="sm"
              >
                Next
              </BaseButton>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Create/Edit User Modal -->
      <UserModal
        v-if="showCreateModal || showEditModal"
        :user="editingUser"
        :is-edit="showEditModal"
        @close="closeModal"
        @saved="handleUserSaved"
      />
      
      <!-- Delete Confirmation Modal -->
      <DeleteConfirmationModal
        v-if="showDeleteModal"
        :user="deletingUser"
        @close="showDeleteModal = false"
        @confirm="confirmDelete"
      />
    </template>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useUserStore } from '@/stores/user';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import UserModal from './partials/UserModal.vue';
import DeleteConfirmationModal from './partials/DeleteConfirmationModal.vue';

const userStore = useUserStore();
const authStore = useAuthStore();

// Reactive state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref(null);
const deletingUser = ref(null);
const searchQuery = ref('');
const selectedRole = ref('');
const selectedOrganization = ref('');
const organizations = ref([
  { id: 1, name: 'Main Office' },
  { id: 2, name: 'Branch Office' }
]);

// Computed properties
const canCreateUsers = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});

const canEditUser = (user) => {
  if (authStore.userRole === 'super-admin') return true;
  if (authStore.userRole === 'hr') {
    return user.organization_id === authStore.userOrganization;
  }
  return false;
};

const canDeleteUser = (user) => {
  if (authStore.userRole === 'super-admin') return true;
  if (authStore.userRole === 'hr') {
    return user.organization_id === authStore.userOrganization && user.role !== 'super-admin';
  }
  return false;
};

// Methods
const handleSearch = () => {
  userStore.searchUsers(searchQuery.value);
};

const handleRoleFilter = () => {
  userStore.filterByRole(selectedRole.value);
};

const handleOrganizationFilter = () => {
  userStore.filterByOrganization(selectedOrganization.value);
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedRole.value = '';
  selectedOrganization.value = '';
  userStore.clearFilters();
};

const viewUser = (user) => {
  // TODO: Navigate to user detail view
  console.log('View user:', user);
};

const editUser = (user) => {
  editingUser.value = user;
  showEditModal.value = true;
};

const deleteUser = (user) => {
  deletingUser.value = user;
  showDeleteModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingUser.value = null;
};

const handleUserSaved = (user) => {
  closeModal();
  // Refresh user list
  userStore.fetchUsers();
};

const confirmDelete = async () => {
  if (deletingUser.value) {
    await userStore.deleteUser(deletingUser.value.id);
    showDeleteModal.value = false;
    deletingUser.value = null;
  }
};

const previousPage = () => {
  if (userStore.pagination.current_page > 1) {
    userStore.fetchUsers({ page: userStore.pagination.current_page - 1 });
  }
};

const nextPage = () => {
  if (userStore.pagination.current_page < userStore.pagination.last_page) {
    userStore.fetchUsers({ page: userStore.pagination.current_page + 1 });
  }
};

const getUserInitials = (name) => {
  if (!name) return 'U';
  return name
    .split(' ')
    .map(n => n.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const getRoleBadgeClasses = (role) => {
  const classes = {
    'super-admin': 'bg-purple-100 text-purple-800',
    'hr': 'bg-blue-100 text-blue-800',
    'manager': 'bg-green-100 text-green-800',
    'employee': 'bg-gray-100 text-gray-800'
  };
  return classes[role] || classes.employee;
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

// Lifecycle
onMounted(async () => {
  await userStore.fetchUsers();
});
</script> 