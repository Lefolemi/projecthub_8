<template>
    <div class="schedule-grid">
      <div v-for="group in groups" :key="group.title" class="schedule-section">
        <h2>{{ group.title }} ({{ group.items.length }})</h2>
        <div v-if="group.items.length === 0" class="empty">No tasks in this window.</div>
        <div v-for="task in group.items" :key="task.id" class="schedule-row">
          <div>
            <strong>{{ task.title }}</strong>
            <span class="meta">Priority: {{ task.priority }} • Status: {{ task.status }}</span>
          </div>
          <div class="due-pill">{{ task.due_date || 'No due date' }}</div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import taskApi from '../services/api';
  
  const tasks = ref([]);
  const today = new Date().toISOString().slice(0, 10);
  
  const groups = computed(() => [
    {
      title: 'Overdue Tasks',
      items: tasks.value.filter((t) => t.due_date && t.due_date < today && t.status !== 'done'),
    },
    {
      title: 'Upcoming Deadlines',
      items: tasks.value.filter((t) => t.due_date && t.due_date >= today),
    },
    {
      title: 'Unscheduled (No Due Date)',
      items: tasks.value.filter((t) => !t.due_date),
    },
  ]);
  
  onMounted(async () => {
    const res = await taskApi.getTasks({ per_page: 50 });
    tasks.value = res.data.data || [];
  });
  </script>
  
  <style scoped>
  .schedule-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
  }
  .schedule-section h2 { margin: 0 0 1rem; font-size: 1.05rem; }
  .schedule-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background: #f8fafc;
  }
  .meta { display: block; font-size: 0.8rem; color: #64748b; }
  .due-pill {
    font-size: 0.82rem;
    font-weight: 600;
    background: #e2e8f0;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
  }
  .empty { font-size: 0.88rem; color: #94a3b8; }
  </style>