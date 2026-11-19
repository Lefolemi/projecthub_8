<template>
    <div class="login-page">
      <div class="login-card">
        <div class="login-header">
          <div class="logo-badge">PF</div>
          <h1>ProjectFlow</h1>
          <p>Sign in or create a workspace account to manage tasks.</p>
        </div>
  
        <div class="tabs">
          <button :class="{ active: mode === 'login' }" @click="mode = 'login'">Login</button>
          <button :class="{ active: mode === 'register' }" @click="mode = 'register'">Register</button>
        </div>
  
        <form @submit.prevent="submit" class="auth-form">
          <div v-if="mode === 'register'" class="field">
            <label>Full Name</label>
            <input v-model="form.name" type="text" placeholder="e.g. Alya Rahma" required />
          </div>
  
          <div class="field">
            <label>Email Address</label>
            <input v-model="form.email" type="email" placeholder="admin@projectflow.test" required />
          </div>
  
          <div class="field">
            <label>Password</label>
            <input v-model="form.password" type="password" placeholder="••••••••" required />
          </div>
  
          <div v-if="mode === 'register'" class="field">
            <label>Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required />
          </div>
  
          <p v-if="errorMessage" class="error-box">{{ errorMessage }}</p>
  
          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? 'Authenticating...' : mode === 'login' ? 'Sign In to Workspace' : 'Create Account' }}
          </button>
        </form>
  
        <div class="demo-accounts">
          <div class="demo-title">Quick Fill Demo Accounts:</div>
          <div class="demo-row">
            <button type="button" class="demo-btn" @click="fillDemo('admin@projectflow.test')">
              Admin Account
            </button>
            <button type="button" class="demo-btn" @click="fillDemo('alya@projectflow.test')">
              Member Account
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuth } from '../composables/useAuth';
  
  const router = useRouter();
  const { login, register } = useAuth();
  
  const mode = ref('login');
  const loading = ref(false);
  const errorMessage = ref('');
  
  const form = reactive({
    name: '',
    email: 'admin@projectflow.test',
    password: 'password123',
    password_confirmation: '',
  });
  
  function fillDemo(email) {
    mode.value = 'login';
    form.email = email;
    form.password = 'password123';
  }
  
  async function submit() {
    loading.value = true;
    errorMessage.value = '';
    try {
      if (mode.value === 'login') {
        await login({ email: form.email, password: form.password });
      } else {
        await register(form);
      }
      router.push('/dashboard');
    } catch (err) {
      errorMessage.value =
        err.response?.data?.message || 'Authentication failed. Please check your credentials.';
    } finally {
      loading.value = false;
    }
  }
  </script>
  
  <style scoped>
  .login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    padding: 1.5rem;
  }
  
  .login-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
  }
  
  .login-header {
    text-align: center;
    margin-bottom: 1.5rem;
  }
  
  .logo-badge {
    width: 48px;
    height: 48px;
    margin: 0 auto 0.75rem;
    border-radius: 12px;
    background: #10b981;
    color: #fff;
    font-weight: 800;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .login-header h1 {
    margin: 0;
    font-size: 1.5rem;
    color: #0f172a;
  }
  
  .login-header p {
    margin: 0.35rem 0 0;
    font-size: 0.88rem;
    color: #64748b;
  }
  
  .tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 10px;
    margin-bottom: 1.25rem;
  }
  
  .tabs button {
    border: none;
    background: transparent;
    padding: 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
  }
  
  .tabs button.active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
  
  .field {
    margin-bottom: 1rem;
  }
  
  .field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.35rem;
  }
  
  .field input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.92rem;
    color: #0f172a;
    background: #ffffff;
  }
  
  .error-box {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-bottom: 1rem;
  }
  
  .submit-btn {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    background: #10b981;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
  }
  
  .submit-btn:hover {
    background: #059669;
  }
  
  .demo-accounts {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
  }
  
  .demo-title {
    font-size: 0.78rem;
    color: #64748b;
    margin-bottom: 0.5rem;
    text-align: center;
  }
  
  .demo-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
  }
  
  .demo-btn {
    padding: 0.45rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    color: #334155;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
  }
  
  .demo-btn:hover {
    border-color: #10b981;
    color: #059669;
  }
  </style>