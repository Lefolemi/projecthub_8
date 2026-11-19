<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  tasks: { type: Array, required: true },
  meta: { type: Object, default: null },
  currentPage: { type: Number, default: 1 },
  loading: { type: Boolean, default: false },
  isAdmin: { type: Boolean, default: false },
  filterStatus: { type: String, default: '' },
  filterPriority: { type: String, default: '' },
});

const emit = defineEmits([
  'update:filterStatus',
  'update:filterPriority',
  'resetFilters',
  'statusChange',
  'edit',
  'delete',
  'pageChange',
]);

const searchQuery = ref('');

const filteredTasks = computed(() => {
  if (!searchQuery.value.trim()) return props.tasks;
  const q = searchQuery.value.toLowerCase();
  return props.tasks.filter(
    (t) =>
      t.title.toLowerCase().includes(q) ||
      (t.description && t.description.toLowerCase().includes(q))
  );
});

const formatStatusLabel = (status) => {
  const map = { todo: 'To Do', in_progress: 'In Progress', done: 'Done' };
  return map[status] || status;
};

const handleReset = () => {
  searchQuery.value = '';
  emit('resetFilters');
};
</script>

<template>
  <!-- Toolbar -->
  <section class="toolbar">
    <input
      v-model="searchQuery"
      type="search"
      class="search-input"
      placeholder="Search tasks by title or description..."
    />

    <div class="filter-group">
      <select :value="filterStatus" @change="emit('update:filterStatus', $event.target.value)">
        <option value="">All Statuses</option>
        <option value="todo">To Do</option>
        <option value="in_progress">In Progress</option>
        <option value="done">Done</option>
      </select>

      <select :value="filterPriority" @change="emit('update:filterPriority', $event.target.value)">
        <option value="">All Priorities</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
      </select>

      <button
        v-if="filterStatus || filterPriority || searchQuery"
        @click="handleReset"
        class="btn btn-ghost"
      >
        Reset
      </button>
    </div>
  </section>

  <!-- Task List -->
  <section class="task-section">
    <div v-if="loading" class="empty-state">Loading tasks from API...</div>

    <div v-else-if="filteredTasks.length === 0" class="empty-state">
      No tasks found. Create a new task above or clear your active filters.
    </div>

    <div v-else class="task-list">
      <article
        v-for="task in filteredTasks"
        :key="task.id"
        class="task-card"
        :class="`border-${task.status}`"
      >
        <div class="task-main">
          <div class="task-badges">
            <span class="badge-status" :class="`status-${task.status}`">
              {{ formatStatusLabel(task.status) }}
            </span>
            <span class="badge-priority" :class="`priority-${task.priority}`">
              {{ task.priority.toUpperCase() }}
            </span>
            <span class="meta-chip">⏱ {{ task.estimated_hours }}h</span>
            <span v-if="task.due_date" class="meta-chip">📅 {{ task.due_date }}</span>
          </div>

          <h3 :class="{ 'line-through': task.status === 'done' }">{{ task.title }}</h3>
          <p v-if="task.description" class="task-desc">{{ task.description }}</p>
        </div>

        <div class="task-controls">
          <select
            :value="task.status"
            @change="emit('statusChange', { task, newStatus: $event.target.value })"
            aria-label="Change status"
          >
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
          </select>

          <button @click="emit('edit', task)" class="btn btn-secondary">Edit</button>
          <button v-if="isAdmin" @click="emit('delete', task.id)" class="btn btn-danger">
            Delete
          </button>
        </div>
      </article>
    </div>

    <!-- Pagination -->
    <div v-if="meta && meta.last_page > 1" class="pagination">
      <button
        class="btn btn-secondary"
        :disabled="currentPage <= 1"
        @click="emit('pageChange', currentPage - 1)"
      >
        ← Previous
      </button>
      <span>Page {{ meta.current_page }} of {{ meta.last_page }} ({{ meta.total }} tasks)</span>
      <button
        class="btn btn-secondary"
        :disabled="currentPage >= meta.last_page"
        @click="emit('pageChange', currentPage + 1)"
      >
        Next →
      </button>
    </div>
  </section>
</template>

<style scoped>
.toolbar {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.search-input { flex: 1; min-width: 220px; }
.filter-group { display: flex; gap: 0.5rem; }
.filter-group select { width: auto; }

.task-list { display: flex; flex-direction: column; gap: 0.75rem; }
.task-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left-width: 4px;
  border-radius: 10px;
  padding: 1rem 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}
.border-todo { border-left-color: #64748b; }
.border-in_progress { border-left-color: #2563eb; }
.border-done { border-left-color: #16a34a; }

.task-main { flex: 1; min-width: 240px; }
.task-badges {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  flex-wrap: wrap;
  margin-bottom: 0.4rem;
}
.badge-status, .badge-priority {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.18rem 0.55rem;
  border-radius: 999px;
}
.status-todo { background: #f1f5f9; color: #475569; }
.status-in_progress { background: #dbeafe; color: #1d4ed8; }
.status-done { background: #dcfce7; color: #15803d; }

.priority-low { background: #f1f5f9; color: #475569; }
.priority-medium { background: #fef3c7; color: #b45309; }
.priority-high { background: #fee2e2; color: #b91c1c; }

.meta-chip { font-size: 0.78rem; color: #64748b; }
.task-main h3 { margin: 0 0 0.25rem; font-size: 1.05rem; }
.line-through { text-decoration: line-through; color: #64748b; }
.task-desc { margin: 0; font-size: 0.88rem; color: #475569; }

.task-controls { display: flex; align-items: center; gap: 0.5rem; }
.task-controls select { width: auto; }

.empty-state {
  text-align: center;
  padding: 2.5rem 1rem;
  background: #fff;
  border: 1px dashed #cbd5e1;
  border-radius: 10px;
  color: #64748b;
}
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.25rem;
  font-size: 0.88rem;
  color: #475569;
}
</style>