import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useUserStore = defineStore('user', () => {
  // State
  const users = ref([]);
  const currentUser = ref(null);
  const loading = ref(false);
  const searchQuery = ref('');
  const selectedRole = ref('');
  const selectedOrganization = ref('');
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1
  });
  
  // Additional state for organizations and departments
  const organizations = ref([]);
  const departments = ref([]);

  // Getters
  const filteredUsers = computed(() => {
    let filtered = users.value;
    
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      filtered = filtered.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
      );
    }
    
    if (selectedRole.value) {
      filtered = filtered.filter(user => user.role === selectedRole.value);
    }
    
    if (selectedOrganization.value) {
      filtered = filtered.filter(user => user.organization_id === selectedOrganization.value);
    }
    
    return filtered;
  });

  const totalUsers = computed(() => pagination.value.total);
  const hasUsers = computed(() => users.value.length > 0);
  const isLoading = computed(() => loading.value);

  // Actions
  const fetchUsers = async (params = {}) => {
    loading.value = true;
    try {
      const response = await axios.get('/api/users', { params });
      
      if (response.data.success) {
        users.value = response.data.data;
        pagination.value = response.data.meta;
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to fetch users.';
      return { success: false, message };
    } finally {
      loading.value = false;
    }
  };

  const fetchUser = async (id) => {
    try {
      const response = await axios.get(`/api/users/${id}`);
      
      if (response.data.success) {
        currentUser.value = response.data.data;
        return { success: true, user: response.data.data };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to fetch user.';
      return { success: false, message };
    }
  };

  const createUser = async (userData) => {
    try {
      const response = await axios.post('/api/users', userData);
      
      if (response.data.success) {
        users.value.unshift(response.data.data);
        pagination.value.total += 1;
        return { success: true, user: response.data.data, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to create user.';
      return { success: false, message };
    }
  };

  const updateUser = async (id, userData) => {
    try {
      const response = await axios.put(`/api/users/${id}`, userData);
      
      if (response.data.success) {
        const index = users.value.findIndex(u => u.id === id);
        if (index !== -1) {
          users.value[index] = response.data.data;
        }
        
        if (currentUser.value?.id === id) {
          currentUser.value = response.data.data;
        }
        
        return { success: true, user: response.data.data, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to update user.';
      return { success: false, message };
    }
  };

  const deleteUser = async (id) => {
    try {
      const response = await axios.delete(`/api/users/${id}`);
      
      if (response.data.success) {
        users.value = users.value.filter(u => u.id !== id);
        pagination.value.total -= 1;
        
        if (currentUser.value?.id === id) {
          currentUser.value = null;
        }
        
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to delete user.';
      return { success: false, message };
    }
  };

  const searchUsers = async (query) => {
    searchQuery.value = query;
    if (query) {
      return await fetchUsers({ search: query });
    } else {
      return await fetchUsers();
    }
  };

  const filterByRole = async (role) => {
    selectedRole.value = role;
    return await fetchUsers({ role });
  };

  const filterByOrganization = async (organizationId) => {
    selectedOrganization.value = organizationId;
    return await fetchUsers({ organization_id: organizationId });
  };

  const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    selectedOrganization.value = '';
  };

  const resetState = () => {
    users.value = [];
    currentUser.value = null;
    loading.value = false;
    searchQuery.value = '';
    selectedRole.value = '';
    selectedOrganization.value = '';
    pagination.value = {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1
    };
  };

  // Organization and Department methods
  const fetchOrganizations = async () => {
    try {
      const response = await axios.get('/api/organizations');
      
      if (response.data.success) {
        organizations.value = response.data.data;
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to fetch organizations.';
      return { success: false, message };
    }
  };

  const fetchDepartments = async () => {
    try {
      const response = await axios.get('/api/departments');
      
      if (response.data.success) {
        departments.value = response.data.data;
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to fetch departments.';
      return { success: false, message };
    }
  };

  return {
    // State
    users,
    currentUser,
    loading,
    searchQuery,
    selectedRole,
    selectedOrganization,
    pagination,
    organizations,
    departments,
    
    // Getters
    filteredUsers,
    totalUsers,
    hasUsers,
    isLoading,
    
    // Actions
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    searchUsers,
    filterByRole,
    filterByOrganization,
    clearFilters,
    resetState,
    fetchOrganizations,
    fetchDepartments
  };
}); 