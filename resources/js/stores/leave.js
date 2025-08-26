import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useLeaveStore = defineStore('leave', () => {
  // State
  const leaves = ref([]);
  const leaveTypes = ref([]);
  const leaveBalances = ref([]);
  const currentLeave = ref(null);
  const loading = ref(false);
  const error = ref(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  
  // Additional state for calendar and reports
  const calendarData = ref({});
  const reportData = ref([]);
  const summary = ref({});
  const leaveTypeData = ref([]);
  const statusData = ref([]);

  // Getters
  const pendingLeaves = computed(() => 
    leaves.value.filter(leave => leave.status === 'pending')
  );

  const approvedLeaves = computed(() => 
    leaves.value.filter(leave => leave.status === 'approved')
  );

  const rejectedLeaves = computed(() => 
    leaves.value.filter(leave => leave.status === 'rejected')
  );

  const cancelledLeaves = computed(() => 
    leaves.value.filter(leave => leave.status === 'cancelled')
  );

  const canApproveLeaves = computed(() => {
    // This should be based on user role from auth store
    return true; // Placeholder - implement based on actual auth logic
  });

  const canManageLeaveTypes = computed(() => {
    // This should be based on user role from auth store
    return true; // Placeholder - implement based on actual auth logic
  });

  // Actions
  const fetchLeaves = async (filters = {}) => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();
      
      // Add filters
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          params.append(key, filters[key]);
        }
      });
      
      // Add pagination
      params.append('page', pagination.value.current_page);
      
      const response = await axios.get(`/api/leaves?${params.toString()}`);
      
      if (response.data.success) {
        leaves.value = response.data.data.data;
        pagination.value = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          per_page: response.data.data.per_page,
          total: response.data.data.total,
        };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leaves';
      console.error('Error fetching leaves:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchLeaveTypes = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/leave-types');
      
      if (response.data.success) {
        leaveTypes.value = response.data.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leave types';
      console.error('Error fetching leave types:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchLeaveBalances = async (filters = {}) => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();
      
      // Add filters
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          params.append(key, filters[key]);
        }
      });
      
      const response = await axios.get(`/api/leave-balances?${params.toString()}`);
      
      if (response.data.success) {
        leaveBalances.value = response.data.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leave balances';
      console.error('Error fetching leave balances:', err);
    } finally {
      loading.value = false;
    }
  };

  const createLeave = async (leaveData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/leaves', leaveData);
      
      if (response.data.success) {
        // Add the new leave to the list
        leaves.value.unshift(response.data.data);
        
        // Update pagination total
        pagination.value.total += 1;
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create leave request';
      console.error('Error creating leave:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const updateLeave = async (leaveId, updateData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.put(`/api/leaves/${leaveId}`, updateData);
      
      if (response.data.success) {
        // Update the leave in the list
        const index = leaves.value.findIndex(leave => leave.id === leaveId);
        if (index !== -1) {
          leaves.value[index] = response.data.data;
        }
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update leave request';
      console.error('Error updating leave:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const deleteLeave = async (leaveId) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.delete(`/api/leaves/${leaveId}`);
      
      if (response.data.success) {
        // Remove the leave from the list
        const index = leaves.value.findIndex(leave => leave.id === leaveId);
        if (index !== -1) {
          leaves.value.splice(index, 1);
        }
        
        // Update pagination total
        pagination.value.total -= 1;
        
        return { success: true };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete leave request';
      console.error('Error deleting leave:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const approveLeave = async (leaveId) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.patch(`/api/leaves/${leaveId}/approve`);
      
      if (response.data.success) {
        // Update the leave in the list
        const index = leaves.value.findIndex(leave => leave.id === leaveId);
        if (index !== -1) {
          leaves.value[index] = response.data.data;
        }
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to approve leave request';
      console.error('Error approving leave:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const rejectLeave = async (leaveId, rejectionReason) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.patch(`/api/leaves/${leaveId}/reject`, {
        rejection_reason: rejectionReason
      });
      
      if (response.data.success) {
        // Update the leave in the list
        const index = leaves.value.findIndex(leave => leave.id === leaveId);
        if (index !== -1) {
          leaves.value[index] = response.data.data;
        }
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to reject leave request';
      console.error('Error rejecting leave:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const fetchLeaveCalendar = async (month, year) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/leaves-calendar', {
        params: { month, year }
      });
      
      if (response.data.success) {
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leave calendar';
      console.error('Error fetching leave calendar:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const fetchLeaveReport = async (startDate, endDate) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/leaves-report', {
        params: { start_date: startDate, end_date: endDate }
      });
      
      if (response.data.success) {
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leave report';
      console.error('Error fetching leave report:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const createLeaveType = async (leaveTypeData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/leave-types', leaveTypeData);
      
      if (response.data.success) {
        // Add the new leave type to the list
        leaveTypes.value.push(response.data.data);
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create leave type';
      console.error('Error creating leave type:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const updateLeaveType = async (leaveTypeId, updateData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.put(`/api/leave-types/${leaveTypeId}`, updateData);
      
      if (response.data.success) {
        // Update the leave type in the list
        const index = leaveTypes.value.findIndex(lt => lt.id === leaveTypeId);
        if (index !== -1) {
          leaveTypes.value[index] = response.data.data;
        }
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update leave type';
      console.error('Error updating leave type:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const deleteLeaveType = async (leaveTypeId) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.delete(`/api/leave-types/${leaveTypeId}`);
      
      if (response.data.success) {
        // Remove the leave type from the list
        const index = leaveTypes.value.findIndex(lt => lt.id === leaveTypeId);
        if (index !== -1) {
          leaveTypes.value.splice(index, 1);
        }
        
        return { success: true };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete leave type';
      console.error('Error deleting leave type:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const initializeLeaveBalances = async (year) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/leave-balances/initialize-year', { year });
      
      if (response.data.success) {
        // Refresh leave balances
        await fetchLeaveBalances({ year });
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to initialize leave balances';
      console.error('Error initializing leave balances:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const fetchLeaveBalanceSummary = async (userId = null, year = null) => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = {};
      if (userId) params.user_id = userId;
      if (year) params.year = year;
      
      const response = await axios.get('/api/leave-balances/summary', { params });
      
      if (response.data.success) {
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch leave balance summary';
      console.error('Error fetching leave balance summary:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  // Calendar and Report methods
  const fetchCalendarData = async (filters = {}) => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();
      
      // Add filters
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          params.append(key, filters[key]);
        }
      });
      
      const response = await axios.get(`/api/leaves-calendar?${params.toString()}`);
      
      if (response.data.success) {
        calendarData.value = response.data.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch calendar data';
      console.error('Error fetching calendar data:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchReportData = async (filters = {}) => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();
      
      // Add filters
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          params.append(key, filters[key]);
        }
      });
      
      const response = await axios.get(`/api/leaves-report?${params.toString()}`);
      
      if (response.data.success) {
        reportData.value = response.data.data.leaves || [];
        summary.value = response.data.data.summary || {};
        leaveTypeData.value = response.data.data.leave_type_distribution || [];
        statusData.value = response.data.data.status_distribution || [];
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch report data';
      console.error('Error fetching report data:', err);
    } finally {
      loading.value = false;
    }
  };

  // Leave Balance management methods
  const createLeaveBalance = async (balanceData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/leave-balances', balanceData);
      
      if (response.data.success) {
        // Add the new balance to the list
        leaveBalances.value.push(response.data.data);
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create leave balance';
      console.error('Error creating leave balance:', err);
      return { success: false, error: error.value, errors: err.response?.data?.errors };
    } finally {
      loading.value = false;
    }
  };

  const updateLeaveBalance = async (balanceId, updateData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.put(`/api/leave-balances/${balanceId}`, updateData);
      
      if (response.data.success) {
        // Update the balance in the list
        const index = leaveBalances.value.findIndex(balance => balance.id === balanceId);
        if (index !== -1) {
          leaveBalances.value[index] = response.data.data;
        }
        
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update leave balance';
      console.error('Error updating leave balance:', err);
      return { success: false, error: error.value, errors: err.response?.data?.errors };
    } finally {
      loading.value = false;
    }
  };

  const deleteLeaveBalance = async (balanceId) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.delete(`/api/leave-balances/${balanceId}`);
      
      if (response.data.success) {
        // Remove the balance from the list
        const index = leaveBalances.value.findIndex(balance => balance.id === balanceId);
        if (index !== -1) {
          leaveBalances.value.splice(index, 1);
        }
        
        return { success: true };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete leave balance';
      console.error('Error deleting leave balance:', err);
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  };

  const initializeYear = async (data) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/leave-balances/initialize-year', data);
      
      if (response.data.success) {
        return { success: true, data: response.data.data };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to initialize year';
      console.error('Error initializing year:', err);
      return { success: false, error: error.value, errors: err.response?.data?.errors };
    } finally {
      loading.value = false;
    }
  };

  const setCurrentLeave = (leave) => {
    currentLeave.value = leave;
  };

  const clearCurrentLeave = () => {
    currentLeave.value = null;
  };

  const clearError = () => {
    error.value = null;
  };

  const resetPagination = () => {
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    };
  };

  return {
    // State
    leaves,
    leaveTypes,
    leaveBalances,
    currentLeave,
    loading,
    error,
    pagination,
    calendarData,
    reportData,
    summary,
    leaveTypeData,
    statusData,
    
    // Getters
    pendingLeaves,
    approvedLeaves,
    rejectedLeaves,
    cancelledLeaves,
    canApproveLeaves,
    canManageLeaveTypes,
    
    // Actions
    fetchLeaves,
    fetchLeaveTypes,
    fetchLeaveBalances,
    createLeave,
    updateLeave,
    deleteLeave,
    approveLeave,
    rejectLeave,
    fetchLeaveCalendar,
    fetchLeaveReport,
    createLeaveType,
    updateLeaveType,
    deleteLeaveType,
    initializeLeaveBalances,
    fetchLeaveBalanceSummary,
    fetchCalendarData,
    fetchReportData,
    createLeaveBalance,
    updateLeaveBalance,
    deleteLeaveBalance,
    initializeYear,
    setCurrentLeave,
    clearCurrentLeave,
    clearError,
    resetPagination,
  };
}); 