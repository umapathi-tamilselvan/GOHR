import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('token'));
  const isAuthenticated = ref(false);
  const loading = ref(false);

  // Getters
  const userRole = computed(() => user.value?.role);
  const userOrganization = computed(() => user.value?.organization);
  const userName = computed(() => user.value?.name);
  const userEmail = computed(() => user.value?.email);

  // Actions
  const login = async (credentials) => {
    loading.value = true;
    try {
      const response = await axios.post('/api/auth/login', credentials);
      
      if (response.data.success) {
        user.value = response.data.data.user;
        token.value = response.data.data.token;
        isAuthenticated.value = true;
        
        // Set token in localStorage
        localStorage.setItem('token', response.data.data.token);
        
        // Set default authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;
        
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed. Please try again.';
      return { success: false, message };
    } finally {
      loading.value = false;
    }
  };

  const logout = async () => {
    try {
      if (token.value) {
        await axios.post('/api/auth/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Clear state regardless of API call success
      user.value = null;
      token.value = null;
      isAuthenticated.value = false;
      
      // Remove token from localStorage
      localStorage.removeItem('token');
      
      // Remove authorization header
      delete axios.defaults.headers.common['Authorization'];
    }
  };

  const checkAuth = async () => {
    if (token.value) {
      try {
        // Set authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        
        const response = await axios.get('/api/auth/user');
        
        if (response.data.success) {
          user.value = response.data.data;
          isAuthenticated.value = true;
          return true;
        } else {
          throw new Error('Invalid token');
        }
      } catch (error) {
        console.error('Auth check failed:', error);
        logout();
        return false;
      }
    }
    return false;
  };

  const updateProfile = async (profileData) => {
    try {
      const response = await axios.put('/api/profile', profileData);
      
      if (response.data.success) {
        user.value = { ...user.value, ...response.data.data };
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Profile update failed.';
      return { success: false, message };
    }
  };

  const changePassword = async (passwordData) => {
    try {
      const response = await axios.put('/api/profile/password', passwordData);
      
      if (response.data.success) {
        return { success: true, message: response.data.message };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Password change failed.';
      return { success: false, message };
    }
  };

  // Initialize auth state
  const initialize = async () => {
    if (token.value) {
      await checkAuth();
    }
  };

  return {
    // State
    user,
    token,
    isAuthenticated,
    loading,
    
    // Getters
    userRole,
    userOrganization,
    userName,
    userEmail,
    
    // Actions
    login,
    logout,
    checkAuth,
    updateProfile,
    changePassword,
    initialize
  };
}); 