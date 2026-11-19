<script setup>
import { ref } from 'vue';
import { PlusCircle } from 'lucide-vue-next';
import taskApi from '../services/api';
import { useProject } from '../composables/useProject';

const props = defineProps({
  projectId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['created', 'unauthorized', 'error']);
const { activeProject } = useProject();

const form = ref({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
  estimated_hours: 1,
});
const errors = ref({});
const creating = ref(false);

const handleSubmit = async () => {
  errors.value = {};
  creating.value = true;
  try {
    const targetProjectId = props.projectId ?? activeProject.value?.id ?? null;

    await taskApi.createTask({
      ...form.value,
      project_id: targetProjectId,
    });

    form.value = {
      title: '',
      description: '',
      status: 'todo',
      priority: 'medium',
      due_date: '',
      estimated_hours: 1,
    };
    emit('created');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else if (error.response?.status === 401) {
      emit('unauthorized');
    } else {
      emit('error', 'Unexpected error while creating task.');
    }
  } finally {
    creating.value = false;
  }
};
</script>

<template>
  <section class="task-form-card">
    <div class="form-head">
      <PlusCircle :size="18" class="icon-emerald" />
      <h2>New Task <span v-if="activeProject" class="proj-tag">in {{ activeProject.key }}</span></h2>
    </div>

    <form @submit.prevent="handleSubmit" class="form-grid">
      <div class="col-span-2">
        <label>Title <span class="req">*</span></label>
        <input
          v-model="form.title"
          type="text"
          placeholder="e.g., Implement RESTful API endpoints"
          :class="{ 'input-invalid': errors.title }"
        />
        <span v-if="errors.title" class="field-error">{{ errors.title[0] }}</span>
      </div>

      <div class="col-span-2">
        <label>Description</label>
        <input
          v-model="form.description"
          type="text"
          placeholder="Optional task details or acceptance criteria..."
        />
        <span v-if="errors.description" class="field-error">{{ errors.description[0] }}</span>
      </div>

      <div>
        <label>Status</label>
        <select v-model="form.status">
          <option value="todo">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
        </select>
        <span v-if="errors.status" class="field-error">{{ errors.status[0] }}</span>
      </div>

      <div>
        <label>Priority</label>
        <select v-model="form.priority">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <span v-if="errors.priority" class="field-error">{{ errors.priority[0] }}</span>
      </div>

      <div>
        <label>Due Date</label>
        <input v-model="form.due_date" type="date" />
        <span v-if="errors.due_date" class="field-error">{{ errors.due_date[0] }}</span>
      </div>

      <div>
        <label>Estimated Hours <span class="req">*</span></label>
        <input
          v-model="form.estimated_hours"
          type="number"
          step="0.5"
          :class="{ 'input-invalid': errors.estimated_hours }"
        />
        <span v-if="errors.estimated_hours" class="field-error">{{ errors.estimated_hours[0] }}</span>
      </div>

      <div class="col-span-2 form-actions">
        <button type="submit" class="btn-primary" :disabled="creating">
          {{ creating ? 'Saving...' : 'Create Task' }}
        </button>
      </div>
    </form>
  </section>
</template>

<style scoped>
.task-form-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.35rem;
  margin-bottom: 1.25rem;
}

.form-head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.form-head h2 {
  margin: 0;
  font-size: 1.05rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-emerald {
  color: #10b981;
}

.proj-tag {
  background: #ecfdf5;
  color: #065f46;
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.85rem;
}

.col-span-2 {
  grid-column: span 2;
}

label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.3rem;
}

.req {
  color: #dc2626;
}

input,
select {
  width: 100%;
  padding: 0.55rem 0.7rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
}

.input-invalid {
  border-color: #dc2626;
}

.field-error {
  display: block;
  font-size: 0.78rem;
  color: #dc2626;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 0.25rem;
}

.btn-primary {
  background: #10b981;
  color: #ffffff;
  border: none;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:hover {
  background: #059669;
}
</style>