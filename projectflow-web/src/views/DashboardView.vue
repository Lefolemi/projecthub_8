<template>
    <div>
      <!-- KPI Row -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <span class="kpi-label">Total Tasks</span>
          <strong class="kpi-value">{{ tasks.length }}</strong>
        </div>
        <div class="kpi-card">
          <span class="kpi-label">To Do</span>
          <strong class="kpi-value text-slate">{{ countByStatus('todo') }}</strong>
        </div>
        <div class="kpi-card">
          <span class="kpi-label">In Progress</span>
          <strong class="kpi-value text-amber">{{ countByStatus('in_progress') }}</strong>
        </div>
        <div class="kpi-card">
          <span class="kpi-label">Completed</span>
          <strong class="kpi-value text-emerald">{{ countByStatus('done') }}</strong>
        </div>
      </div>
  
      <!-- Urgent / Upcoming Deadlines -->
      <div class="panel">
        <div class="panel-header">
          <h2>Upcoming & High-Priority Tasks</h2>
          <router-link to="/tasks" class="link-btn">View All Tasks →</router-link>
        </div>
  
        <div v-if="loading" class="empty">Loading workspace summary...</div>
        <div v-else-if="priorityTasks.length === 0" class="empty">No active tasks right now.</div>
  
        <div v-else class="task-cards">
          <div v-for="task in priorityTasks" :key="task.id" class="mini-card">
            <div class="mini-top">
              <span class="badge" :class="'prio-' + task.priority">{{ task.priority }}</span>
              <span class="status-pill">{{ formatStatus(task.status) }}</span>
            </div>
            <h3>{{ task.title }}</h3>
            <p>{{ task.description || 'No description provided.' }}</p>
            <div class="mini-footer">
              <span>Due: {{ task.due_date || 'No date' }}</span>
              <span>{{ task.estimated_hours ?? 0 }} hrs</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import taskApi from '../services/api';
  
  const tasks = ref([]);
  const loading = ref(true);
  
  const countByStatus = (status) => tasks.value.filter((t) => t.status === status).length;
  
  const priorityTasks = computed(() =>
    tasks.value.filter((t) => t.status !== 'done').slice(0, 6)
  );
  
  const formatStatus = (s) =>
    s === 'in_progress' ? 'In Progress' : s === 'done' ? 'Done' : 'To Do';
  
  onMounted(async () => {
    try {
      const res = await taskApi.getTasks({ per_page: 50 });
      tasks.value = res.data.data || [];
    } finally {
      loading.value = false;
    }
  });
  </script>
  
  <style scoped>
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
  }
  
  .kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
  }
  
  .kpi-label {
    display: block;
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 600;
  }
  
  .kpi-value {
    display: block;
    font-size: 1.85rem;
    margin-top: 0.35rem;
    color: #0f172a;
  }
  
  .text-amber { color: #d97706; }
  .text-emerald { color: #059669; }
  
  .panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
  }
  
  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
  }
  
  .panel-header h2 {
    margin: 0;
    font-size: 1.1rem;
  }
  
  .link-btn {
    color: #059669;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.88rem;
  }
  
  .task-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
  }
  
  .mini-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    background: #f8fafc;
  }
  
  .mini-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.65rem;
  }
  
  .badge {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
  }
  
  .prio-high { background: #fee2e2; color: #b91c1c; }
  .prio-medium { background: #fef3c7; color: #92400e; }
  .prio-low { background: #e0f2fe; color: #0369a1; }
  
  .status-pill {
    font-size: 0.75rem;
    color: #475569;
    font-weight: 600;
  }
  
  .mini-card h3 {
    margin: 0 0 0.4rem;
    font-size: 1rem;
  }
  
  .mini-card p {
    margin: 0 0 0.85rem;
    font-size: 0.85rem;
    color: #64748b;
  }
  
  .mini-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: #475569;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.6rem;
  }
  </style>