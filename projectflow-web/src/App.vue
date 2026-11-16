<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import taskApi from './services/api';

const tasks = ref([]);
const meta = ref(null);
const currentPage = ref(1);
const loading = ref(false);
const apiError = ref('');

// Filters (Challenge A)
const filterStatus = ref('');
const filterPriority = ref('');
const searchQuery = ref('');

// Create Form State
const createForm = ref({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
  estimated_hours: 1,
});
const createErrors = ref({});
const creating = ref(false);

// Edit Modal State
const editingTask = ref(null);
const editErrors = ref({});
const updating = ref(false);

// Fetch tasks from Laravel API
const fetchTasks = async (page = 1) => {
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
    apiError.value = 'Unable to connect to Laravel API. Make sure "php artisan serve" is running on port 8000.';
    console.error(error);
  } finally {
    loading.value = false;
  }
};

// Client-side quick search on loaded page
const filteredTasks = computed(() => {
  if (!searchQuery.value.trim()) return tasks.value;
  const q = searchQuery.value.toLowerCase();
  return tasks.value.filter(
    (t) =>
      t.title.toLowerCase().includes(q) ||
      (t.description && t.description.toLowerCase().includes(q))
  );
});

// Summary counts
const stats = computed(() => ({
  total: tasks.value.length,
  todo: tasks.value.filter((t) => t.status === 'todo').length,
  in_progress: tasks.value.filter((t) => t.status === 'in_progress').length,
  done: tasks.value.filter((t) => t.status === 'done').length,
}));

// POST /api/tasks
const handleCreateTask = async () => {
  createErrors.value = {};
  creating.value = true;
  try {
    await taskApi.createTask(createForm.value);
    createForm.value = {
      title: '',
      description: '',
      status: 'todo',
      priority: 'medium',
      due_date: '',
      estimated_hours: 1,
    };
    await fetchTasks(1);
  } catch (error) {
    if (error.response && error.response.status === 422) {
      createErrors.value = error.response.data.errors;
    } else {
      apiError.value = 'Unexpected error while creating task.';
    }
  } finally {
    creating.value = false;
  }
};

// PATCH /api/tasks/{id} (Quick status change)
const handleStatusChange = async (task, newStatus) => {
  const previousStatus = task.status;
  task.status = newStatus;
  try {
    await taskApi.updateTask(task.id, { status: newStatus });
  } catch (error) {
    task.status = previousStatus;
    alert('Failed to update task status.');
  }
};

// Open Edit Modal
const openEditModal = (task) => {
  editErrors.value = {};
  editingTask.value = { ...task };
};

// PUT /api/tasks/{id} (Full edit save)
const handleUpdateTask = async () => {
  if (!editingTask.value) return;
  editErrors.value = {};
  updating.value = true;
  try {
    const response = await taskApi.replaceTask(editingTask.value.id, {
      title: editingTask.value.title,
      description: editingTask.value.description,
      status: editingTask.value.status,
      priority: editingTask.value.priority,
      due_date: editingTask.value.due_date || null,
      estimated_hours: editingTask.value.estimated_hours,
    });
    const updated = response.data.data;
    const index = tasks.value.findIndex((t) => t.id === updated.id);
    if (index !== -1) tasks.value[index] = updated;
    editingTask.value = null;
  } catch (error) {
    if (error.response && error.response.status === 422) {
      editErrors.value = error.response.data.errors;
    }
  } finally {
    updating.value = false;
  }
};

// DELETE /api/tasks/{id}
const handleDeleteTask = async (id) => {
  if (!confirm('Are you sure you want to delete this task?')) return;
  try {
    await taskApi.deleteTask(id);
    tasks.value = tasks.value.filter((t) => t.id !== id);
  } catch (error) {
    alert('Failed to delete task.');
  }
};

const formatStatusLabel = (status) => {
  const map = { todo: 'To Do', in_progress: 'In Progress', done: 'Done' };
  return map[status] || status;
};

watch([filterStatus, filterPriority], () => fetchTasks(1));
onMounted(() => fetchTasks(1));
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
        <div class="stats-bar">
          <div class="stat-pill"><span>To Do</span><strong>{{ stats.todo }}</strong></div>
          <div class="stat-pill"><span>In Progress</span><strong>{{ stats.in_progress }}</strong></div>
          <div class="stat-pill"><span>Done</span><strong>{{ stats.done }}</strong></div>
        </div>
      </div>
    </header>

    <main class="container">
      <!-- Connection Alert -->
      <div v-if="apiError" class="alert-error">
        {{ apiError }}
      </div>

      <!-- Create Task Card -->
      <section class="card">
        <h2>+ New Task</h2>
        <form @submit.prevent="handleCreateTask" class="form-grid">
          <div class="col-span-2">
            <label>Title <span class="req">*</span></label>
            <input
              v-model="createForm.title"
              type="text"
              placeholder="e.g., Implement RESTful API endpoints"
              :class="{ 'input-invalid': createErrors.title }"
            />
            <span v-if="createErrors.title" class="field-error">{{ createErrors.title[0] }}</span>
          </div>

          <div class="col-span-2">
            <label>Description</label>
            <input
              v-model="createForm.description"
              type="text"
              placeholder="Optional task details or acceptance criteria..."
            />
            <span v-if="createErrors.description" class="field-error">{{ createErrors.description[0] }}</span>
          </div>

          <div>
            <label>Status</label>
            <select v-model="createForm.status">
              <option value="todo">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="done">Done</option>
            </select>
            <span v-if="createErrors.status" class="field-error">{{ createErrors.status[0] }}</span>
          </div>

          <div>
            <label>Priority</label>
            <select v-model="createForm.priority">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
            <span v-if="createErrors.priority" class="field-error">{{ createErrors.priority[0] }}</span>
          </div>

          <div>
            <label>Due Date</label>
            <input v-model="createForm.due_date" type="date" />
            <span v-if="createErrors.due_date" class="field-error">{{ createErrors.due_date[0] }}</span>
          </div>

          <div>
            <label>Estimated Hours <span class="req">*</span></label>
            <input
              v-model="createForm.estimated_hours"
              type="number"
              step="0.5"
              :class="{ 'input-invalid': createErrors.estimated_hours }"
            />
            <span v-if="createErrors.estimated_hours" class="field-error">{{ createErrors.estimated_hours[0] }}</span>
          </div>

          <div class="col-span-2 form-actions">
            <button type="submit" class="btn btn-primary" :disabled="creating">
              {{ creating ? 'Saving...' : 'Create Task' }}
            </button>
          </div>
        </form>
      </section>

      <!-- Toolbar: Search & API Filters -->
      <section class="toolbar">
        <input
          v-model="searchQuery"
          type="search"
          class="search-input"
          placeholder="Search tasks by title or description..."
        />

        <div class="filter-group">
          <select v-model="filterStatus">
            <option value="">All Statuses</option>
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
          </select>

          <select v-model="filterPriority">
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>

          <button
            v-if="filterStatus || filterPriority || searchQuery"
            @click="filterStatus = ''; filterPriority = ''; searchQuery = ''"
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
                @change="handleStatusChange(task, $event.target.value)"
                aria-label="Change status"
              >
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>

              <button @click="openEditModal(task)" class="btn btn-secondary">Edit</button>
              <button @click="handleDeleteTask(task.id)" class="btn btn-danger">Delete</button>
            </div>
          </article>
        </div>

        <!-- Pagination Controls (Challenge B) -->
        <div v-if="meta && meta.last_page > 1" class="pagination">
          <button
            class="btn btn-secondary"
            :disabled="currentPage <= 1"
            @click="fetchTasks(currentPage - 1)"
          >
            ← Previous
          </button>
          <span>Page {{ meta.current_page }} of {{ meta.last_page }} ({{ meta.total }} tasks)</span>
          <button
            class="btn btn-secondary"
            :disabled="currentPage >= meta.last_page"
            @click="fetchTasks(currentPage + 1)"
          >
            Next →
          </button>
        </div>
      </section>
    </main>

    <!-- Edit Task Modal -->
    <div v-if="editingTask" class="modal-backdrop" @click.self="editingTask = null">
      <div class="modal-card">
        <div class="modal-header">
          <h2>Edit Task #{{ editingTask.id }}</h2>
          <button @click="editingTask = null" class="close-btn">✕</button>
        </div>

        <form @submit.prevent="handleUpdateTask" class="form-grid">
          <div class="col-span-2">
            <label>Title <span class="req">*</span></label>
            <input v-model="editingTask.title" type="text" />
            <span v-if="editErrors.title" class="field-error">{{ editErrors.title[0] }}</span>
          </div>

          <div class="col-span-2">
            <label>Description</label>
            <input v-model="editingTask.description" type="text" />
          </div>

          <div>
            <label>Status</label>
            <select v-model="editingTask.status">
              <option value="todo">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="done">Done</option>
            </select>
          </div>

          <div>
            <label>Priority</label>
            <select v-model="editingTask.priority">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <div>
            <label>Due Date</label>
            <input v-model="editingTask.due_date" type="date" />
          </div>

          <div>
            <label>Estimated Hours <span class="req">*</span></label>
            <input v-model="editingTask.estimated_hours" type="number" step="0.5" />
            <span v-if="editErrors.estimated_hours" class="field-error">{{ editErrors.estimated_hours[0] }}</span>
          </div>

          <div class="col-span-2 modal-footer">
            <button type="button" @click="editingTask = null" class="btn btn-ghost">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="updating">
              {{ updating ? 'Updating...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
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
.badge-brand {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  background: #2563eb;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
}
.topbar h1 {
  margin: 0.35rem 0 0;
  font-size: 1.35rem;
}
.stats-bar {
  display: flex;
  gap: 0.65rem;
}
.stat-pill {
  background: #1e293b;
  padding: 0.45rem 0.85rem;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 78px;
}
.stat-pill span { font-size: 0.72rem; color: #94a3b8; }
.stat-pill strong { font-size: 1.05rem; color: #f8fafc; }

.container {
  max-width: 960px;
  margin: 1.75rem auto;
  padding: 0 1rem;
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

.toolbar {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.search-input {
  flex: 1;
  min-width: 220px;
}
.filter-group {
  display: flex;
  gap: 0.5rem;
}
.filter-group select { width: auto; }

.task-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
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

.meta-chip {
  font-size: 0.78rem;
  color: #64748b;
}
.task-main h3 {
  margin: 0 0 0.25rem;
  font-size: 1.05rem;
}
.line-through {
  text-decoration: line-through;
  color: #64748b;
}
.task-desc {
  margin: 0;
  font-size: 0.88rem;
  color: #475569;
}

.task-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.task-controls select { width: auto; }

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

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 50;
}
.modal-card {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 540px;
  padding: 1.4rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.modal-header h2 { margin: 0; font-size: 1.15rem; }
.close-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: #64748b;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 0.5rem;
}
</style>