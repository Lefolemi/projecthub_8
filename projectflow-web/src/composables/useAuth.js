import { ref, computed } from 'vue';
import taskApi from '../services/api';

const token = ref(localStorage.getItem('pf_token') || '');
const currentUser = ref(null);
const authChecked = ref(false);

export function useAuth() {
  const isAuthenticated = computed(() => Boolean(token.value));
  const isAdmin = computed(() => currentUser.value?.role === 'admin');

  function setSession(newToken, user) {
    token.value = newToken;
    currentUser.value = user;
    authChecked.value = true;
    localStorage.setItem('pf_token', newToken);
  }

  function clearSession() {
    token.value = '';
    currentUser.value = null;
    authChecked.value = true;
    localStorage.removeItem('pf_token');
  }

  async function fetchMe() {
    if (!token.value) {
      authChecked.value = true;
      return null;
    }
    try {
      const response = await taskApi.getMe();
      currentUser.value = response.data.user || response.data;
      authChecked.value = true;
      return currentUser.value;
    } catch (error) {
      clearSession();
      return null;
    }
  }

  async function login(credentials) {
    const response = await taskApi.login(credentials);
    setSession(response.data.token, response.data.user);
    return response.data;
  }

  async function register(payload) {
    const response = await taskApi.register(payload);
    setSession(response.data.token, response.data.user);
    return response.data;
  }

  async function logout() {
    try {
      await taskApi.logout();
    } catch (error) {
      // Ignore network/401 errors during token revocation
    } finally {
      clearSession();
    }
  }

  return {
    token,
    currentUser,
    authChecked,
    isAuthenticated,
    isAdmin,
    fetchMe,
    login,
    register,
    logout,
  };
}