<script setup>
import { ref } from 'vue';
import taskApi from '../services/api';

const emit = defineEmits(['created', 'unauthorized', 'error']);

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
    await taskApi.createTask(form.value);
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
  <section class="card">
    <h2>+ New Task</h2>
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
        <button type="submit" class="btn btn-primary" :disabled="creating">
          {{ creating ? 'Saving...' : 'Create Task' }}
        </button>
      </div>
    </form>
  </section>
</template>