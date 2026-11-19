import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';

import LoginView from '../views/LoginView.vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import DashboardView from '../views/DashboardView.vue';
import ProjectsView from '../views/ProjectsView.vue';
import TasksView from '../views/TasksView.vue';
import ScheduleView from '../views/ScheduleView.vue';
import ReportsView from '../views/ReportsView.vue';
import AdminTeamView from '../views/AdminTeamView.vue';
import SettingsView from '../views/SettingsView.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard', subtitle: 'Overview of your project momentum & deadlines' },
      },
      {
        path: 'projects',
        name: 'projects',
        component: ProjectsView,
        meta: { title: 'Projects & Invites', subtitle: 'Create projects, manage team members & send email invitations' },
      },
      {
        path: 'tasks',
        name: 'tasks',
        component: TasksView,
        meta: { title: 'Tasks Workspace', subtitle: 'Manage, filter, and track tasks in List or Kanban Board view' },
      },
      {
        path: 'schedule',
        name: 'schedule',
        component: ScheduleView,
        meta: { title: 'Schedule & Deadlines', subtitle: 'Chronological timeline of upcoming and overdue tasks' },
      },
      {
        path: 'reports',
        name: 'reports',
        component: ReportsView,
        meta: { title: 'Activity & Reports', subtitle: 'Workload distribution, priority breakdown, and completion metrics' },
      },
      {
        path: 'admin',
        name: 'admin',
        component: AdminTeamView,
        meta: {
          title: 'Admin & Team',
          subtitle: 'System-wide telemetry and registered workspace members',
          requiresAdmin: true,
        },
      },
      {
        path: 'settings',
        name: 'settings',
        component: SettingsView,
        meta: { title: 'Profile & Settings', subtitle: 'Account identity, role permissions, and session controls' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const { isAuthenticated, currentUser, authChecked, fetchMe, isAdmin } = useAuth();

  if (isAuthenticated.value && !authChecked.value) {
    await fetchMe();
  }

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'login' };
  }

  if (to.meta.guestOnly && isAuthenticated.value) {
    return { name: 'dashboard' };
  }

  if (to.meta.requiresAdmin && currentUser.value && !isAdmin.value) {
    return { name: 'dashboard' };
  }

  return true;
});

export default router;