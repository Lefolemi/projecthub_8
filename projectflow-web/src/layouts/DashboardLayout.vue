<template>
  <div class="app-shell">
    <!-- Left Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo">
          <Layers :size="20" />
        </div>
        <div>
          <span class="brand-name">ProjectFlow</span>
          <span class="brand-sub">Team Workspace</span>
        </div>
      </div>

      <!-- Active Project Switcher -->
      <div class="project-switcher">
        <label class="switcher-label">ACTIVE PROJECT</label>
        <select
          v-if="projects.length > 0"
          :value="activeProject?.id"
          class="switcher-select"
          @change="selectProject($event.target.value)"
        >
          <option v-for="proj in projects" :key="proj.id" :value="proj.id">
            [{{ proj.key }}] {{ proj.name }}
          </option>
        </select>
        <router-link v-else to="/projects" class="empty-project-cta">
          <FolderPlus :size="15" />
          <span>Create First Project</span>
        </router-link>
      </div>

      <div class="nav-scroll">
        <div class="nav-group">
          <div class="nav-label">MAIN WORKSPACE</div>
          <router-link to="/dashboard" class="nav-item">
            <LayoutDashboard :size="18" />
            <span>Dashboard</span>
          </router-link>
          <router-link to="/projects" class="nav-item">
            <FolderKanban :size="18" />
            <span>Projects & Team</span>
            <span class="nav-badge">{{ projects.length }}</span>
          </router-link>
          <router-link to="/tasks" class="nav-item">
            <CheckSquare :size="18" />
            <span>Tasks</span>
          </router-link>
          <router-link to="/schedule" class="nav-item">
            <CalendarDays :size="18" />
            <span>Schedule</span>
          </router-link>
          <router-link to="/reports" class="nav-item">
            <BarChart3 :size="18" />
            <span>Activity & Reports</span>
          </router-link>
        </div>

        <div v-if="isAdmin" class="nav-group">
          <div class="nav-label">ADMINISTRATION</div>
          <router-link to="/admin" class="nav-item">
            <ShieldCheck :size="18" />
            <span>System Admin</span>
          </router-link>
        </div>

        <div class="nav-group">
          <div class="nav-label">ACCOUNT</div>
          <router-link to="/settings" class="nav-item">
            <Settings :size="18" />
            <span>Settings</span>
          </router-link>
        </div>
      </div>

      <!-- User Footer Card -->
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="avatar">{{ userInitials }}</div>
          <div class="user-meta">
            <div class="user-name">{{ currentUser?.name || 'Workspace User' }}</div>
            <div class="user-email">{{ currentUser?.email || '' }}</div>
          </div>
          <span class="role-tag" :class="isAdmin ? 'role-admin' : 'role-member'">
            {{ currentUser?.role || 'member' }}
          </span>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <LogOut :size="15" />
          <span>Sign Out</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-area">
      <header class="topbar">
        <div>
          <div class="title-row">
            <h1 class="page-title">{{ route.meta.title || 'ProjectFlow' }}</h1>
            <span v-if="activeProject" class="project-pill">
              {{ activeProject.key }} • {{ activeProject.name }}
            </span>
          </div>
          <p class="page-subtitle">{{ route.meta.subtitle || '' }}</p>
        </div>

        <div class="topbar-actions">
          <!-- Notification Bell for Project Invitations -->
          <div class="notif-wrapper">
            <button class="bell-btn" @click="showNotifs = !showNotifs" title="Project Invitations">
              <Bell :size="19" />
              <span v-if="invitations.length > 0" class="bell-badge">
                {{ invitations.length }}
              </span>
            </button>

            <div v-if="showNotifs" class="notif-popover">
              <div class="notif-header">
                <strong>Project Invitations</strong>
                <span>{{ invitations.length }} pending</span>
              </div>

              <div v-if="invitations.length === 0" class="notif-empty">
                No pending invitations right now.
              </div>

              <div v-for="inv in invitations" :key="inv.id" class="notif-item">
                <p class="notif-text">
                  <strong>{{ inv.inviter?.name }}</strong> invited you to join
                  <strong>[{{ inv.project?.key }}] {{ inv.project?.name }}</strong>
                </p>
                <div class="notif-btns">
                  <button class="accept-btn" @click="handleAccept(inv.id)">
                    <Check :size="14" /> Accept
                  </button>
                  <button class="decline-btn" @click="handleDecline(inv.id)">
                    <X :size="14" /> Decline
                  </button>
                </div>
              </div>
            </div>
          </div>

          <router-link to="/projects" class="quick-btn">
            <Plus :size="16" />
            <span>New Project</span>
          </router-link>
        </div>
      </header>

      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Layers,
  LayoutDashboard,
  FolderKanban,
  FolderPlus,
  CheckSquare,
  CalendarDays,
  BarChart3,
  ShieldCheck,
  Settings,
  LogOut,
  Bell,
  Check,
  X,
  Plus,
} from 'lucide-vue-next';
import { useAuth } from '../composables/useAuth';
import { useProject } from '../composables/useProject';

const route = useRoute();
const router = useRouter();
const { currentUser, isAdmin, logout } = useAuth();
const {
  projects,
  activeProject,
  invitations,
  selectProject,
  loadProjects,
  loadInvitations,
  acceptInvite,
  declineInvite,
  resetProjectState,
} = useProject();

const showNotifs = ref(false);

const userInitials = computed(() => {
  const name = currentUser.value?.name || 'PF';
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

async function handleAccept(id) {
  await acceptInvite(id);
  showNotifs.value = false;
  router.push('/dashboard');
}

async function handleDecline(id) {
  await declineInvite(id);
}

async function handleLogout() {
  await logout();
  resetProjectState();
  router.push('/login');
}

onMounted(async () => {
  await Promise.all([loadProjects(), loadInvitations()]);
});
</script>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
}

.sidebar {
  width: 268px;
  background: #0f172a;
  color: #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 1.25rem 1rem;
  flex-shrink: 0;
  position: sticky;
  top: 0;
  height: 100vh;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.25rem 0.5rem 1rem;
  border-bottom: 1px solid #1e293b;
}

.brand-logo {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-name {
  display: block;
  font-weight: 700;
  font-size: 1.05rem;
  color: #ffffff;
}

.brand-sub {
  display: block;
  font-size: 0.75rem;
  color: #94a3b8;
}

.project-switcher {
  padding: 0.9rem 0.25rem 0.5rem;
  border-bottom: 1px solid #1e293b;
}

.switcher-label {
  display: block;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  margin-bottom: 0.4rem;
  padding-left: 0.35rem;
}

.switcher-select {
  width: 100%;
  background: #1e293b;
  color: #f8fafc;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 0.55rem 0.65rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.empty-project-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  background: rgba(16, 185, 129, 0.15);
  border: 1px dashed #10b981;
  color: #34d399;
  text-decoration: none;
  padding: 0.55rem;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
}

.nav-scroll {
  flex: 1;
  overflow-y: auto;
  padding-top: 1rem;
}

.nav-group {
  margin-bottom: 1.35rem;
}

.nav-label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  padding: 0 0.75rem 0.45rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.62rem 0.75rem;
  border-radius: 8px;
  color: #cbd5e1;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  margin-bottom: 0.2rem;
  transition: all 0.15s ease;
}

.nav-item:hover {
  background: #1e293b;
  color: #ffffff;
}

.nav-item.router-link-active {
  background: #10b981;
  color: #ffffff;
  font-weight: 600;
}

.nav-badge {
  margin-left: auto;
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.1rem 0.48rem;
  border-radius: 999px;
}

.sidebar-footer {
  border-top: 1px solid #1e293b;
  padding-top: 0.9rem;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 0.7rem;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #1e293b;
  border: 1px solid #334155;
  color: #10b981;
  font-weight: 700;
  font-size: 0.78rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-meta {
  min-width: 0;
  flex: 1;
}

.user-name {
  font-size: 0.84rem;
  font-weight: 600;
  color: #f8fafc;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 0.7rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.role-tag {
  font-size: 0.66rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.14rem 0.42rem;
  border-radius: 6px;
}

.role-admin { background: #fef3c7; color: #92400e; }
.role-member { background: #d1fae5; color: #065f46; }

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.5rem;
  border-radius: 8px;
  border: 1px solid #334155;
  background: transparent;
  color: #cbd5e1;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
}

.logout-btn:hover {
  background: #dc2626;
  border-color: #dc2626;
  color: #ffffff;
}

.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.topbar {
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #0f172a;
}

.project-pill {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
  font-size: 0.76rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
}

.page-subtitle {
  margin: 0.15rem 0 0;
  font-size: 0.84rem;
  color: #64748b;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.notif-wrapper {
  position: relative;
}

.bell-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.bell-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.bell-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 800;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notif-popover {
  position: absolute;
  right: 0;
  top: 48px;
  width: 330px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.14);
  z-index: 50;
  overflow: hidden;
}

.notif-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.84rem;
}

.notif-header span {
  color: #64748b;
  font-size: 0.76rem;
}

.notif-empty {
  padding: 1.25rem 1rem;
  text-align: center;
  font-size: 0.84rem;
  color: #64748b;
}

.notif-item {
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.notif-text {
  margin: 0 0 0.6rem;
  font-size: 0.83rem;
  color: #334155;
  line-height: 1.4;
}

.notif-btns {
  display: flex;
  gap: 0.5rem;
}

.accept-btn,
.decline-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.35rem 0.7rem;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.accept-btn {
  background: #10b981;
  color: #ffffff;
}

.decline-btn {
  background: #f1f5f9;
  color: #475569;
}

.quick-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #0f172a;
  color: #ffffff;
  text-decoration: none;
  padding: 0.55rem 0.95rem;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
}

.quick-btn:hover {
  background: #10b981;
}

.page-content {
  padding: 1.75rem 2rem;
  max-width: 1280px;
  width: 100%;
}
</style>