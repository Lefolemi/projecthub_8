<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import taskApi from './services/api';
import AuthCard from './components/AuthCard.vue';
import AdminOverview from './components/AdminOverview.vue';
import TaskFormCard from './components/TaskFormCard.vue';
import TaskListSection from './components/TaskListSection.vue';
import EditTaskModal from './components/EditTaskModal.vue';

// Auth & Role State
const token = ref(localStorage.getItem('pf_token') || '');
const currentUser = ref(null);
const adminSummary = ref(null);
const isAdmin = computed(() => currentUser.value?.role === 'admin');

// Tasks State
const tasks = ref([]);
const meta = ref(null);
const currentPage = ref(1);
const loading = ref(false);
const apiError = ref('');
const filterStatus = ref('');
const filterPriority = ref('');
const editingTask = ref(null);

const stats = computed(() => ({
  total: tasks.value.length,
  todo: tasks.value.filter((t) => t.status === 'todo').length,
  in_progress: tasks.value.filter((t) => t.status === 'in_progress').length,
  done: tasks.value.filter((t) => t.status === 'done').length,
}));

const onAuthenticated = async ({ token: newToken, user }) => {
  token.value = newToken;
  localStorage.setItem('pf_token', newToken);
  currentUser.value = user;
  await initWorkspace();
};

const handleLogout = async () => {
  try {
    if (token.value) await taskApi.logout();
  } catch (_) {
    // Ignore if token was already revoked
  } finally {
    localStorage.removeItem('pf_token');
    token.value = '';
    currentUser.value = null;
    adminSummary.value = null;
    tasks.value = [];
    meta.value = null;
  }
};

const fetchAdminSummary = async () => {
  if (!isAdmin.value) {
    adminSummary.value = null;
    return;
  }
  try {
    const res = await taskApi.getAdminSummary();
    adminSummary.value = res.data;
  } catch (_) {
    adminSummary.value = null;
  }
};

const fetchTasks = async (page = 1) => {
  if (!token.value) return;
  loading.value = true;
  apiError.value = '';
  try {
    const params = { page };
    if (filterStatus.value) params.status = filterStatus.value;
    if (filterPriority.value) params.priority = filterPriority.value;

    const response = await taskApi.getTasks(params);
    tasks.value = response.data.data;
    meta.value = response.data.meta || null;
    currentPage.value = page;
  } catch (error) {
    if (error.response?.status === 401) {
      await handleLogout();
    } else {
      apiError.value =
        'Unable to connect to Laravel API. Make sure "php artisan serve" is running on port 8000.';
    }
  } finally {
    loading.value = false;
  }
};

const initWorkspace = async () => {
  if (!token.value) return;
  try {
    if (!currentUser.value) {
      const meRes = await taskApi.getMe();
      currentUser.value = meRes.data.user || meRes.data;
    }
    await Promise.all([fetchTasks(1), fetchAdminSummary()]);
  } catch (_) {
    await handleLogout();
  }
};

const refreshAll = async () => {
  await Promise.all([fetchTasks(1), fetchAdminSummary()]);
};

const handleStatusChange = async ({ task, newStatus }) => {
  const previousStatus = task.status;
  task.status = newStatus;
  try {
    await taskApi.updateTask(task.id, { status: newStatus });
  } catch (error) {
    task.status = previousStatus;
    if (error.response?.status === 401) await handleLogout();
    else alert('Failed to update task status.');
  }
};

const handleTaskUpdated = (updated) => {
  const index = tasks.value.findIndex((t) => t.id === updated.id);
  if (index !== -1) tasks.value[index] = updated;
  editingTask.value = null;
};

const handleDeleteTask = async (id) => {
  if (!confirm('Are you sure you want to delete this task?')) return;
  try {
    await taskApi.deleteTask(id);
    tasks.value = tasks.value.filter((t) => t.id !== id);
    await fetchAdminSummary();
  } catch (error) {
    alert(error.response?.data?.message || 'Forbidden: Only admins can delete tasks.');
  }
};

const resetFilters = () => {
  filterStatus.value = '';
  filterPriority.value = '';
};

watch([filterStatus, filterPriority], () => fetchTasks(1));
onMounted(() => {
  if (token.value) initWorkspace();
});
</script>

<template>
  <div class="app-shell">
    <!-- Top Header -->
    <header class="topbar">
      <div class="topbar-inner">
        <div>
          <span class="badge-brand">PROJECTFLOW</span>
          <h1>Task Management Workspace</h1>
        </div>

        <div v-if="currentUser" class="topbar-right">
          <div class="stats-bar">
            <div class="stat-pill"><span>To Do</span><strong>{{ stats.todo }}</strong></div>
            <div class="stat-pill"><span>In Progress</span><strong>{{ stats.in_progress }}</strong></div>
            <div class="stat-pill"><span>Done</span><strong>{{ stats.done }}</strong></div>
          </div>

          <div class="user-pill">
            <div class="user-info">
              <span class="user-name">{{ currentUser.name }}</span>
              <span class="role-badge" :class="`role-${currentUser.role}`">
                {{ currentUser.role.toUpperCase() }}
              </span>
            </div>
            <button @click="handleLogout" class="btn btn-logout">Logout</button>
          </div>
        </div>
      </div>
    </header>

    <!-- Unauthenticated View -->
    <AuthCard v-if="!token" @authenticated="onAuthenticated" />

    <!-- Authenticated Workspace -->
    <main v-else class="container">
      <div v-if="apiError" class="alert-error">{{ apiError }}</div>

      <AdminOverview
        v-if="isAdmin && adminSummary"
        :summary="adminSummary"
        :fallback-total="meta?.total ?? stats.total"
      />

      <TaskFormCard
        @created="refreshAll"
        @unauthorized="handleLogout"
        @error="apiError = $event"
      />

      <TaskListSection
        :tasks="tasks"
        :meta="meta"
        :current-page="currentPage"
        :loading="loading"
        :is-admin="isAdmin"
        v-model:filter-status="filterStatus"
        v-model:filter-priority="filterPriority"
        @reset-filters="resetFilters"
        @status-change="handleStatusChange"
        @edit="editingTask = $event"
        @delete="handleDeleteTask"
        @page-change="fetchTasks"
      />
    </main>

    <EditTaskModal
      :task="editingTask"
      @close="editingTask = null"
      @updated="handleTaskUpdated"
      @unauthorized="handleLogout"
    />
  </div>
</template>

<style>
* { box-sizing: border-box; }
body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: #f1f5f9;
  color: #0f172a;
}

/* Shared Design System Classes across components */
.card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.35rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  margin-bottom: 1.5rem;
}
.card h2 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
}
.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
  padding: 0.85rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-weight: 500;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.9rem;
}
.col-span-2 { grid-column: span 2; }
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  .col-span-2 { grid-column: span 1; }
}
label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 0.3rem;
  color: #334155;
}
.req { color: #dc2626; }
input, select {
  width: 100%;
  padding: 0.55rem 0.7rem;
  border: 1px solid #cbd5e1;
  border-radius: 7px;
  font-size: 0.92rem;
  background: #fff;
}
input:focus, select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.input-invalid { border-color: #dc2626; }
.field-error {
  display: block;
  color: #dc2626;
  font-size: 0.78rem;
  margin-top: 0.25rem;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 0.25rem;
}
.btn {
  padding: 0.5rem 0.9rem;
  border-radius: 7px;
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1d4ed8; }
.btn-secondary { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
.btn-secondary:hover:not(:disabled) { background: #f1f5f9; }
.btn-danger { background: #fee2e2; color: #b91c1c; }
.btn-danger:hover { background: #fecaca; }
.btn-ghost { background: transparent; color: #475569; border-color: #cbd5e1; }
</style>

<style scoped>
.topbar {
  background: #0f172a;
  color: #ffffff;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #1e293b;
}
.topbar-inner {
  max-width: 960px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}
.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}
.badge-brand {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  background: #2563eb;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
}
.topbar h1 { margin: 0.35rem 0 0; font-size: 1.35rem; }
.stats-bar { display: flex; gap: 0.55rem; }
.stat-pill {
  background: #1e293b;
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 72px;
}
.stat-pill span { font-size: 0.7rem; color: #94a3b8; }
.stat-pill strong { font-size: 1rem; color: #f8fafc; }

.user-pill {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  background: #1e293b;
  padding: 0.45rem 0.75rem;
  border-radius: 8px;
}
.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.15rem;
}
.user-name { font-size: 0.85rem; font-weight: 600; }
.role-badge {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
}
.role-admin { background: #fef3c7; color: #92400e; }
.role-member { background: #dbeafe; color: #1e40af; }
.btn-logout {
  background: #334155;
  color: #f8fafc;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
}
.btn-logout:hover { background: #475569; }

.container {
  max-width: 960px;
  margin: 1.75rem auto;
  padding: 0 1rem;
}
</style>