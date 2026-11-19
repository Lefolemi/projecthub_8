<template>
    <div>
      <div v-if="error" class="error-banner">{{ error }}</div>
  
      <div v-if="data" class="summary-cards">
        <div class="stat-box">
          <span>Registered Users</span>
          <strong>{{ data.summary.total_users }}</strong>
        </div>
        <div class="stat-box">
          <span>Total System Tasks</span>
          <strong>{{ data.summary.total_tasks }}</strong>
        </div>
        <div class="stat-box">
          <span>In Progress Tasks</span>
          <strong>{{ data.summary.tasks_by_status.in_progress }}</strong>
        </div>
      </div>
  
      <div v-if="data?.users" class="team-table-card">
        <h2>Team Members & Roles</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in data.users" :key="user.id">
              <td>#{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td><span class="role-badge">{{ user.role }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import taskApi from '../services/api';
  
  const data = ref(null);
  const error = ref('');
  
  onMounted(async () => {
    try {
      const res = await taskApi.getAdminSummary();
      data.value = res.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Admin access required.';
    }
  });
  </script>
  
  <style scoped>
  .summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  .stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
  }
  .stat-box span { display: block; font-size: 0.82rem; color: #64748b; }
  .stat-box strong { display: block; font-size: 1.75rem; margin-top: 0.3rem; }
  .team-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
  }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
  .role-badge {
    background: #0f172a;
    color: #ffffff;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    font-size: 0.75rem;
    text-transform: uppercase;
  }
  .error-banner {
    background: #fef2f2;
    color: #b91c1c;
    padding: 1rem;
    border-radius: 8px;
  }
  </style>