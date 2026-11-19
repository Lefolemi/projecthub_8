import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Automatically attach the Sanctum Bearer token if logged in
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pf_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default {
  // Authentication endpoints
  register(data) {
    return api.post('/register', data);
  },
  login(data) {
    return api.post('/login', data);
  },
  getMe() {
    return api.get('/me');
  },
  logout() {
    return api.post('/logout');
  },

  // Admin-only endpoints
  getAdminSummary() {
    return api.get('/admin/summary');
  },

  // Task CRUD endpoints
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