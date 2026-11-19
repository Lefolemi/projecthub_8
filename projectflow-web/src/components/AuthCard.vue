<script setup>
import { ref } from 'vue';
import taskApi from '../services/api';

const emit = defineEmits(['authenticated']);

const authMode = ref('login');
const authForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const authError = ref('');
const authLoading = ref(false);

const fillDemo = (role) => {
  authMode.value = 'login';
  authError.value = '';
  authForm.value.email =
    role === 'admin' ? 'admin@projectflow.test' : 'alya@projectflow.test';
  authForm.value.password = 'password123';
};

const handleSubmit = async () => {
  authLoading.value = true;
  authError.value = '';
  try {
    const res =
      authMode.value === 'login'
        ? await taskApi.login({
            email: authForm.value.email,
            password: authForm.value.password,
          })
        : await taskApi.register(authForm.value);

    emit('authenticated', {
      token: res.data.token,
      user: res.data.user,
    });
  } catch (error) {
    if (error.response?.status === 422 && error.response.data.errors) {
      const firstField = Object.keys(error.response.data.errors)[0];
      authError.value = error.response.data.errors[firstField][0];
    } else {
      authError.value =
        error.response?.data?.message || 'Authentication failed. Check your credentials.';
    }
  } finally {
    authLoading.value = false;
  }
};
</script>

<template>
  <main class="auth-container">
    <section class="card">
      <div class="auth-tabs">
        <button
          type="button"
          :class="['tab-btn', { active: authMode === 'login' }]"
          @click="authMode = 'login'; authError = ''"
        >
          Login
        </button>
        <button
          type="button"
          :class="['tab-btn', { active: authMode === 'register' }]"
          @click="authMode = 'register'; authError = ''"
        >
          Register (Member)
        </button>
      </div>

      <div class="demo-bar">
        <span>Demo Accounts:</span>
        <button type="button" class="chip-btn" @click="fillDemo('admin')">Admin</button>
        <button type="button" class="chip-btn" @click="fillDemo('member')">Member (Alya)</button>
      </div>

      <div v-if="authError" class="alert-error">{{ authError }}</div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div v-if="authMode === 'register'">
          <label>Full Name <span class="req">*</span></label>
          <input v-model="authForm.name" type="text" required placeholder="e.g., Alya Putri" />
        </div>

        <div>
          <label>Email <span class="req">*</span></label>
          <input v-model="authForm.email" type="email" required placeholder="you@projectflow.test" />
        </div>

        <div>
          <label>Password <span class="req">*</span></label>
          <input v-model="authForm.password" type="password" required placeholder="••••••••" />
        </div>

        <div v-if="authMode === 'register'">
          <label>Confirm Password <span class="req">*</span></label>
          <input
            v-model="authForm.password_confirmation"
            type="password"
            required
            placeholder="••••••••"
          />
        </div>

        <button type="submit" class="btn btn-primary w-full" :disabled="authLoading">
          {{
            authLoading
              ? 'Authenticating...'
              : authMode === 'login'
              ? 'Sign In & Get Token'
              : 'Create Member Account'
          }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.auth-container {
  max-width: 440px;
  margin: 3rem auto;
  padding: 0 1rem;
}
.card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.35rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.auth-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}
.tab-btn {
  flex: 1;
  padding: 0.55rem;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  border-radius: 7px;
  font-weight: 600;
  cursor: pointer;
}
.tab-btn.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}
.demo-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: #475569;
}
.chip-btn {
  padding: 0.22rem 0.6rem;
  border-radius: 999px;
  border: 1px solid #cbd5e1;
  background: #f1f5f9;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}
.chip-btn:hover { background: #e2e8f0; }
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}
.w-full { width: 100%; }
</style>