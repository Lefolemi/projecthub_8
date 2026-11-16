import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

export default {
  getTasks(params = {}) {
    return api.get('/tasks', { params });
  },
  getTask(id) {
    return api.get(`/tasks/${id}`);
  },
  createTask(data) {
    return api.post('/tasks', data);
  },
  updateTask(id, data) {
    return api.patch(`/tasks/${id}`, data);
  },
  replaceTask(id, data) {
    return api.put(`/tasks/${id}`, data);
  },
  deleteTask(id) {
    return api.delete(`/tasks/${id}`);
  },
};