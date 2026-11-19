<template>
    <div class="settings-card">
      <h2>Authenticated Account Profile</h2>
      <div v-if="currentUser" class="profile-grid">
        <div class="row"><span>Full Name</span><strong>{{ currentUser.name }}</strong></div>
        <div class="row"><span>Email Address</span><strong>{{ currentUser.email }}</strong></div>
        <div class="row"><span>Assigned Role</span><strong>{{ currentUser.role }}</strong></div>
        <div class="row"><span>API Token Status</span><strong class="active-token">Active (Sanctum Bearer)</strong></div>
      </div>
      <button class="signout-btn" @click="handleSignOut">Revoke Token & Sign Out</button>
    </div>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router';
  import { useAuth } from '../composables/useAuth';
  
  const router = useRouter();
  const { currentUser, logout } = useAuth();
  
  async function handleSignOut() {
    await logout();
    router.push('/login');
  }
  </script>
  
  <style scoped>
  .settings-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    max-width: 600px;
  }
  .settings-card h2 { margin: 0 0 1.25rem; font-size: 1.1rem; }
  .row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.92rem;
  }
  .row span { color: #64748b; }
  .active-token { color: #059669; }
  .signout-btn {
    margin-top: 1.5rem;
    background: #dc2626;
    color: #ffffff;
    border: none;
    padding: 0.65rem 1.1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
  }
  </style>