<template>
    <div class="reports-grid">
      <div class="report-card">
        <h2>Status Distribution</h2>
        <div class="bar-row" v-for="item in statusStats" :key="item.label">
          <div class="bar-label">
            <span>{{ item.label }}</span>
            <strong>{{ item.count }} ({{ item.pct }}%)</strong>
          </div>
          <div class="bar-track">
            <div class="bar-fill" :style="{ width: item.pct + '%' }"></div>
          </div>
        </div>
      </div>
  
      <div class="report-card">
        <h2>Workload & Estimated Hours</h2>
        <p class="big-stat">{{ totalHours }} hrs</p>
        <p class="sub-stat">Total estimated effort across {{ tasks.length }} tasks</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import taskApi from '../services/api';
  
  const tasks = ref([]);
  
  const totalHours = computed(() =>
    tasks.value.reduce((sum, t) => sum + Number(t.estimated_hours || 0), 0)
  );
  
  const statusStats = computed(() => {
    const total = tasks.value.length || 1;
    return [
      { label: 'To Do', count: tasks.value.filter((t) => t.status === 'todo').length },
      { label: 'In Progress', count: tasks.value.filter((t) => t.status === 'in_progress').length },
      { label: 'Done', count: tasks.value.filter((t) => t.status === 'done').length },
    ].map((s) => ({ ...s, pct: Math.round((s.count / total) * 100) }));
  });
  
  onMounted(async () => {
    const res = await taskApi.getTasks({ per_page: 50 });
    tasks.value = res.data.data || [];
  });
  </script>
  
  <style scoped>
  .reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.25rem;
  }
  .report-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
  }
  .report-card h2 { margin: 0 0 1.25rem; font-size: 1.05rem; }
  .bar-row { margin-bottom: 1rem; }
  .bar-label { display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem; }
  .bar-track { height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
  .bar-fill { height: 100%; background: #10b981; border-radius: 999px; }
  .big-stat { font-size: 2.25rem; font-weight: 800; color: #059669; margin: 0.5rem 0; }
  .sub-stat { color: #64748b; font-size: 0.88rem; margin: 0; }
  </style>