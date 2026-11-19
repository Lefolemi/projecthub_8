<script setup>
import { ref, watch } from 'vue';
import taskApi from '../services/api';

const props = defineProps({
  task: { type: Object, default: null },
});

const emit = defineEmits(['close', 'updated', 'unauthorized']);

const form = ref(null);
const errors = ref({});
const updating = ref(false);

watch(
  () => props.task,
  (val) => {
    errors.value = {};
    form.value = val ? { ...val } : null;
  },
  { immediate: true }
);

const handleSubmit = async () => {
  if (!form.value) return;
  errors.value = {};
  updating.value = true;
  try {
    const response = await taskApi.replaceTask(form.value.id, {
      title: form.value.title,
      description: form.value.description,
      status: form.value.status,
      priority: form.value.priority,
      due_date: form.value.due_date || null,
      estimated_hours: form.value.estimated_hours,
    });
    emit('updated', response.data.data);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else if (error.response?.status === 401) {
      emit('unauthorized');
    }
  } finally {
    updating.value = false;
  }
};
</script>

<template>
  <div v-if="form" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Edit Task #{{ form.id }}</h2>
        <button @click="emit('close')" class="close-btn">✕</button>
      </div>

      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="col-span-2">
          <label>Title <span class="req">*</span></label>
          <input v-model="form.title" type="text" />
          <span v-if="errors.title" class="field-error">{{ errors.title[0] }}</span>
        </div>

        <div class="col-span-2">
          <label>Description</label>
          <input v-model="form.description" type="text" />
        </div>

        <div>
          <label>Status</label>
          <select v-model="form.status">
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
          </select>
        </div>

        <div>
          <label>Priority</label>
          <select v-model="form.priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>

        <div>
          <label>Due Date</label>
          <input v-model="form.due_date" type="date" />
        </div>

        <div>
          <label>Estimated Hours <span class="req">*</span></label>
          <input v-model="form.estimated_hours" type="number" step="0.5" />
          <span v-if="errors.estimated_hours" class="field-error">{{ errors.estimated_hours[0] }}</span>
        </div>

        <div class="col-span-2 modal-footer">
          <button type="button" @click="emit('close')" class="btn btn-ghost">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="updating">
            {{ updating ? 'Updating...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
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