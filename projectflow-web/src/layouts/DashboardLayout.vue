<template>
    <div class="app-shell">
      <!-- Left Sidebar -->
      <aside class="sidebar">
        <div class="brand">
          <div class="brand-logo">PF</div>
          <div>
            <span class="brand-name">ProjectFlow</span>
            <span class="brand-sub">Task & Sprint Hub</span>
          </div>
        </div>
  
        <div class="nav-scroll">
          <div class="nav-group">
            <div class="nav-label">MAIN WORKSPACE</div>
            <router-link to="/dashboard" class="nav-item">
              <span class="nav-icon">◫</span>
              <span>Dashboard</span>
            </router-link>
            <router-link to="/tasks" class="nav-item">
              <span class="nav-icon">☑</span>
              <span>Tasks</span>
              <span v-if="taskCount !== null" class="nav-badge">{{ taskCount }}</span>
            </router-link>
            <router-link to="/schedule" class="nav-item">
              <span class="nav-icon">📅</span>
              <span>Schedule</span>
            </router-link>
            <router-link to="/reports" class="nav-item">
              <span class="nav-icon">📊</span>
              <span>Activity & Reports</span>
            </router-link>
          </div>
  
          <div v-if="isAdmin" class="nav-group">
            <div class="nav-label">ADMINISTRATION</div>
            <router-link to="/admin" class="nav-item">
              <span class="nav-icon">🛡️</span>
              <span>Admin & Team</span>
            </router-link>
          </div>
  
          <div class="nav-group">
            <div class="nav-label">ACCOUNT</div>
            <router-link to="/settings" class="nav-item">
              <span class="nav-icon">⚙️</span>
              <span>Settings</span>
            </router-link>
          </div>
        </div>
  
        <!-- User Footer Card -->
        <div class="sidebar-footer">
          <div class="user-card">
            <div class="avatar">{{ userInitials }}</div>
            <div class="user-meta">
              <div class="user-name">{{ currentUser?.name || 'Workspace User' }}</div>
              <div class="user-email">{{ currentUser?.email || '' }}</div>
            </div>
            <span class="role-tag" :class="isAdmin ? 'role-admin' : 'role-member'">
              {{ currentUser?.role || 'member' }}
            </span>
          </div>
          <button class="logout-btn" @click="handleLogout">Sign Out</button>
        </div>
      </aside>
  
      <!-- Main Content Area -->
      <div class="main-area">
        <header class="topbar">
          <div>
            <h1 class="page-title">{{ route.meta.title || 'ProjectFlow' }}</h1>
            <p class="page-subtitle">{{ route.meta.subtitle || '' }}</p>
          </div>
          <div class="topbar-actions">
            <router-link to="/tasks" class="quick-btn">+ Manage Tasks</router-link>
          </div>
        </header>
  
        <main class="page-content">
          <router-view @tasks-updated="loadTaskCount" />
        </main>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAuth } from '../composables/useAuth';
  import taskApi from '../services/api';
  
  const route = useRoute();
  const router = useRouter();
  const { currentUser, isAdmin, logout } = useAuth();
  const taskCount = ref(null);
  
  const userInitials = computed(() => {
    const name = currentUser.value?.name || 'PF';
    return name
      .split(' ')
      .map((part) => part[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  });
  
  async function loadTaskCount() {
    try {
      const res = await taskApi.getTasks({ per_page: 1 });
      taskCount.value = res.data?.meta?.total ?? res.data?.total ?? null;
    } catch {
      taskCount.value = null;
    }
  }
  
  async function handleLogout() {
    await logout();
    router.push('/login');
  }
  
  onMounted(() => {
    loadTaskCount();
  });
  </script>
  
  <style scoped>
  .app-shell {
    display: flex;
    min-height: 100vh;
    background: #f8fafc;
    color: #0f172a;
  }
  
  .sidebar {
    width: 264px;
    background: #0f172a;
    color: #e2e8f0;
    display: flex;
    flex-direction: column;
    padding: 1.25rem 1rem;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    height: 100vh;
  }
  
  .brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.25rem 0.5rem 1.25rem;
    border-bottom: 1px solid #1e293b;
  }
  
  .brand-logo {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
  }
  
  .brand-name {
    display: block;
    font-weight: 700;
    font-size: 1.05rem;
    color: #ffffff;
  }
  
  .brand-sub {
    display: block;
    font-size: 0.75rem;
    color: #94a3b8;
  }
  
  .nav-scroll {
    flex: 1;
    overflow-y: auto;
    padding-top: 1.25rem;
  }
  
  .nav-group {
    margin-bottom: 1.5rem;
  }
  
  .nav-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #64748b;
    padding: 0 0.75rem 0.5rem;
  }
  
  .nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.75rem;
    border-radius: 8px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.2rem;
    transition: all 0.15s ease;
  }
  
  .nav-item:hover {
    background: #1e293b;
    color: #ffffff;
  }
  
  .nav-item.router-link-active {
    background: #10b981;
    color: #ffffff;
    font-weight: 600;
  }
  
  .nav-icon {
    width: 20px;
    text-align: center;
    font-size: 0.95rem;
  }
  
  .nav-badge {
    margin-left: auto;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
  }
  
  .sidebar-footer {
    border-top: 1px solid #1e293b;
    padding-top: 1rem;
  }
  
  .user-card {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.75rem;
  }
  
  .avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #1e293b;
    border: 1px solid #334155;
    color: #10b981;
    font-weight: 700;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  
  .user-meta {
    min-width: 0;
    flex: 1;
  }
  
  .user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #f8fafc;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .user-email {
    font-size: 0.72rem;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .role-tag {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
  }
  
  .role-admin {
    background: #fef3c7;
    color: #92400e;
  }
  
  .role-member {
    background: #d1fae5;
    color: #065f46;
  }
  
  .logout-btn {
    width: 100%;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid #334155;
    background: transparent;
    color: #cbd5e1;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
  }
  
  .logout-btn:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: #ffffff;
  }
  
  .main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  
  .topbar {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.15rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .page-title {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
  }
  
  .page-subtitle {
    margin: 0.15rem 0 0;
    font-size: 0.85rem;
    color: #64748b;
  }
  
  .quick-btn {
    background: #0f172a;
    color: #ffffff;
    text-decoration: none;
    padding: 0.55rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
  }
  
  .quick-btn:hover {
    background: #10b981;
  }
  
  .page-content {
    padding: 1.75rem 2rem;
    max-width: 1280px;
    width: 100%;
  }
  </style>