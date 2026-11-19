<template>
    <div>
      <div class="view-controls">
        <div class="mode-switch">
          <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
            List View
          </button>
          <button :class="{ active: viewMode === 'board' }" @click="viewMode = 'board'">
            Kanban Board
          </button>
        </div>
        <button class="create-btn" @click="showCreate = !showCreate">
          {{ showCreate ? 'Close Form' : '+ New Task' }}
        </button>
      </div>
  
      <TaskFormCard v-if="showCreate" @created="handleCreated" />
  
      <!-- List View (reuses your existing TaskListSection) -->
      <TaskListSection
        v-if="viewMode === 'list'"
        :tasks="tasks"
        :meta="meta"
        :loading="loading"
        :filters="filters"
        @filter-change="fetchTasks"
        @edit="editingTask = $event"
        @delete="deleteTask"
        @status-change="quickStatusChange"
      />
  
      <!-- Kanban Board View -->
      <div v-else class="kanban-grid">
        <div v-for="col in columns" :key="col.key" class="kanban-col">
          <div class="col-header">
            <span>{{ col.label }}</span>
            <span class="col-count">{{ tasksByStatus(col.key).length }}</span>
          </div>
          <div class="col-body">
            <div v-for="task in tasksByStatus(col.key)" :key="task.id" class="k-card">
              <div class="k-top">
                <span class="prio" :class="'prio-' + task.priority">{{ task.priority }}</span>
                <span class="k-hours">{{ task.estimated_hours ?? 0 }}h</span>
              </div>
              <h4>{{ task.title }}</h4>
              <p>{{ task.description || 'No description.' }}</p>
              <div class="k-actions">
                <button @click="editingTask = task">Edit</button>
                <button class="del" @click="deleteTask(task.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <EditTaskModal
        v-if="editingTask"
        :task="editingTask"
        @close="editingTask = null"
        @updated="handleUpdated"
      />
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted } from 'vue';
  import taskApi from '../services/api';
  import TaskFormCard from '../components/TaskFormCard.vue';
  import TaskListSection from '../components/TaskListSection.vue';
  import EditTaskModal from '../components/EditTaskModal.vue';
  
  const emit = defineEmits(['tasks-updated']);
  
  const viewMode = ref('list');
  const showCreate = ref(false);
  const tasks = ref([]);
  const meta = ref({});
  const loading = ref(false);
  const editingTask = ref(null);
  
  const filters = reactive({
    search: '',
    status: '',
    priority: '',
    page: 1,
  });
  
  const columns = [
    { key: 'todo', label: 'To Do' },
    { key: 'in_progress', label: 'In Progress' },
    { key: 'done', label: 'Done' },
  ];
  
  const tasksByStatus = (status) => tasks.value.filter((t) => t.status === status);
  
  async function fetchTasks(newFilters = {}) {
    Object.assign(filters, newFilters);
    loading.value = true;
    try {
      const res = await taskApi.getTasks({
        search: filters.search || undefined,
        status: filters.status || undefined,
        priority: filters.priority || undefined,
        page: filters.page,
      });
      tasks.value = res.data.data || [];
      meta.value = res.data.meta || {};
      emit('tasks-updated');
    } finally {
      loading.value = false;
    }
  }
  
  async function handleCreated() {
    showCreate.value = false;
    await fetchTasks({ page: 1 });
  }
  
  async function handleUpdated() {
    editingTask.value = null;
    await fetchTasks();
  }
  
  async function quickStatusChange({ id, status }) {
    await taskApi.updateTask(id, { status });
    await fetchTasks();
  }
  
  async function deleteTask(id) {
    if (!confirm('Delete this task?')) return;
    await taskApi.deleteTask(id);
    await fetchTasks();
  }
  
  onMounted(() => {
    fetchTasks();
  });
  </script>
  
  <style scoped>
  .view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
  }
  
  .mode-switch {
    display: inline-flex;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 10px;
  }
  
  .mode-switch button {
    border: none;
    background: transparent;
    padding: 0.45rem 0.95rem;
    border-radius: 7px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
  }
  
  .mode-switch button.active {
    background: #ffffff;
    color: #0f172a;
  }
  
  .create-btn {
    background: #10b981;
    color: #ffffff;
    border: none;
    padding: 0.6rem 1.1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
  }
  
  .kanban-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
  }
  
  .kanban-col {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
  }
  
  .col-header {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    margin-bottom: 0.85rem;
  }
  
  .col-count {
    background: #cbd5e1;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
    font-size: 0.78rem;
  }
  
  .k-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.9rem;
    margin-bottom: 0.75rem;
  }
  
  .k-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
  }
  
  .prio {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.45rem;
    border-radius: 5px;
  }
  .prio-high { background: #fee2e2; color: #b91c1c; }
  .prio-medium { background: #fef3c7; color: #92400e; }
  .prio-low { background: #e0f2fe; color: #0369a1; }
  
  .k-card h4 { margin: 0 0 0.35rem; font-size: 0.95rem; }
  .k-card p { margin: 0 0 0.75rem; font-size: 0.82rem; color: #64748b; }
  
  .k-actions {
    display: flex;
    gap: 0.5rem;
  }
  
  .k-actions button {
    padding: 0.3rem 0.65rem;
    font-size: 0.78rem;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    border-radius: 6px;
    cursor: pointer;
  }
  
  .k-actions button.del {
    color: #b91c1c;
    border-color: #fecaca;
  }
  </style>