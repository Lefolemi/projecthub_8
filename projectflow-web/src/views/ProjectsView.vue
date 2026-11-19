<template>
  <div class="projects-layout">
    <!-- Left Column: Create Project + Your Projects -->
    <div class="left-col">
      <div class="card">
        <div class="card-title">
          <FolderPlus :size="18" class="icon-emerald" />
          <h2>Create New Project</h2>
        </div>

        <form @submit.prevent="handleCreateProject" class="form-grid">
          <div class="field">
            <label>Project Name</label>
            <input v-model="newProj.name" type="text" placeholder="e.g. Mobile Banking App" required />
          </div>
          <div class="field">
            <label>Project Key (Short Code)</label>
            <input v-model="newProj.key" type="text" maxlength="10" placeholder="e.g. MBA" required />
          </div>
          <div class="field full">
            <label>Description</label>
            <input v-model="newProj.description" type="text" placeholder="Sprint goals or repository scope..." />
          </div>
          <button type="submit" class="primary-btn" :disabled="creating">
            {{ creating ? 'Creating...' : 'Create Project' }}
          </button>
        </form>
      </div>

      <div class="card">
        <div class="card-title">
          <FolderKanban :size="18" class="icon-emerald" />
          <h2>Your Projects ({{ projects.length }})</h2>
        </div>

        <div v-if="projects.length === 0" class="empty-state">
          You have no projects yet. Create one above or check your notification bell for invitations!
        </div>

        <div v-else class="project-list">
          <div
            v-for="proj in projects"
            :key="proj.id"
            class="project-item"
            :class="{ selected: activeProject?.id === proj.id }"
            @click="selectAndInspect(proj.id)"
          >
            <div>
              <div class="proj-head">
                <span class="key-badge">{{ proj.key }}</span>
                <strong>{{ proj.name }}</strong>
              </div>
              <p class="proj-desc">{{ proj.description || 'No description.' }}</p>
            </div>
            <div class="proj-stats">
              <span>{{ proj.members_count ?? 1 }} members</span>
              <span>{{ proj.tasks_count ?? 0 }} tasks</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Active Project Team & Email Invitation -->
    <div class="right-col">
      <div v-if="projectDetails" class="card">
        <div class="card-title">
          <Users :size="18" class="icon-emerald" />
          <h2>Team — [{{ projectDetails.key }}] {{ projectDetails.name }}</h2>
        </div>

        <!-- Invite Member Form -->
        <form @submit.prevent="handleInvite" class="invite-box">
          <label>Invite Teammate by Registered Email</label>
          <div class="invite-row">
            <input
              v-model="inviteEmail"
              type="email"
              placeholder="e.g. alya@projectflow.test"
              required
            />
            <button type="submit" class="invite-btn" :disabled="inviting">
              <MailPlus :size="15" />
              <span>{{ inviting ? 'Sending...' : 'Send Invite' }}</span>
            </button>
          </div>
          <p v-if="inviteSuccess" class="msg-ok">{{ inviteSuccess }}</p>
          <p v-if="inviteError" class="msg-err">{{ inviteError }}</p>
        </form>

        <!-- Current Members -->
        <h3 class="subhead">Active Members ({{ projectDetails.members?.length || 0 }})</h3>
        <div class="member-list">
          <div v-for="m in projectDetails.members" :key="m.id" class="member-row">
            <div>
              <strong>{{ m.name }}</strong>
              <span>{{ m.email }}</span>
            </div>
            <span class="role-pill">{{ m.pivot?.role || 'member' }}</span>
          </div>
        </div>

        <!-- Pending Invitations -->
        <div v-if="projectDetails.invitations?.length">
          <h3 class="subhead">Pending Invitations</h3>
          <div v-for="inv in projectDetails.invitations" :key="inv.id" class="pending-row">
            <span>{{ inv.invitee?.name }} ({{ inv.invitee?.email }})</span>
            <span class="pending-badge">Pending</span>
          </div>
        </div>
      </div>

      <div v-else class="card empty-state">
        Select or create a project to manage team members and send invitations.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { FolderPlus, FolderKanban, Users, MailPlus } from 'lucide-vue-next';
import { useProject } from '../composables/useProject';
import taskApi from '../services/api';

const { projects, activeProject, selectProject, createProject, loadProjects } = useProject();

const creating = ref(false);
const inviting = ref(false);
const inviteEmail = ref('');
const inviteSuccess = ref('');
const inviteError = ref('');
const projectDetails = ref(null);

const newProj = reactive({
  name: '',
  key: '',
  description: '',
});

async function fetchProjectDetails(id) {
  if (!id) {
    projectDetails.value = null;
    return;
  }
  const res = await taskApi.getProject(id);
  projectDetails.value = res.data.data;
}

function selectAndInspect(id) {
  selectProject(id);
}

async function handleCreateProject() {
  creating.value = true;
  try {
    const created = await createProject(newProj);
    newProj.name = '';
    newProj.key = '';
    newProj.description = '';
    await fetchProjectDetails(created.id);
  } finally {
    creating.value = false;
  }
}

async function handleInvite() {
  if (!activeProject.value) return;
  inviting.value = true;
  inviteSuccess.value = '';
  inviteError.value = '';
  try {
    const res = await taskApi.inviteToProject(activeProject.value.id, inviteEmail.value);
    inviteSuccess.value = res.data.message;
    inviteEmail.value = '';
    await fetchProjectDetails(activeProject.value.id);
  } catch (err) {
    inviteError.value =
      err.response?.data?.errors?.email?.[0] ||
      err.response?.data?.message ||
      'Unable to send invitation.';
  } finally {
    inviting.value = false;
  }
}

watch(
  () => activeProject.value?.id,
  (newId) => {
    fetchProjectDetails(newId);
  }
);

onMounted(async () => {
  if (!projects.value.length) {
    await loadProjects();
  }
  if (activeProject.value?.id) {
    await fetchProjectDetails(activeProject.value.id);
  }
});
</script>

<style scoped>
.projects-layout {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 1.5rem;
}

.card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.4rem;
  margin-bottom: 1.25rem;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin-bottom: 1.15rem;
}

.card-title h2 {
  margin: 0;
  font-size: 1.05rem;
}

.icon-emerald {
  color: #10b981;
}

.form-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 0.85rem;
}

.field.full {
  grid-column: 1 / -1;
}

.field label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.3rem;
}

.field input {
  width: 100%;
  padding: 0.58rem 0.7rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.88rem;
}

.primary-btn {
  grid-column: 1 / -1;
  background: #10b981;
  color: #ffffff;
  border: none;
  padding: 0.65rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.project-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  margin-bottom: 0.65rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.project-item:hover {
  border-color: #10b981;
}

.project-item.selected {
  background: #ecfdf5;
  border-color: #10b981;
}

.proj-head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.key-badge {
  background: #0f172a;
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.15rem 0.45rem;
  border-radius: 5px;
}

.proj-desc {
  margin: 0.3rem 0 0;
  font-size: 0.82rem;
  color: #64748b;
}

.proj-stats {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  font-size: 0.78rem;
  color: #475569;
  gap: 0.2rem;
}

.invite-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.25rem;
}

.invite-box label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 0.45rem;
}

.invite-row {
  display: flex;
  gap: 0.5rem;
}

.invite-row input {
  flex: 1;
  padding: 0.55rem 0.7rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
}

.invite-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #0f172a;
  color: #ffffff;
  border: none;
  padding: 0.55rem 0.95rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.msg-ok { color: #059669; font-size: 0.82rem; margin: 0.5rem 0 0; }
.msg-err { color: #dc2626; font-size: 0.82rem; margin: 0.5rem 0 0; }

.subhead {
  font-size: 0.88rem;
  color: #475569;
  margin: 1rem 0 0.6rem;
}

.member-row,
.pending-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 0.75rem;
  border: 1px solid #f1f5f9;
  border-radius: 8px;
  margin-bottom: 0.45rem;
}

.member-row span {
  display: block;
  font-size: 0.78rem;
  color: #64748b;
}

.role-pill {
  background: #d1fae5;
  color: #065f46;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
  text-transform: uppercase;
}

.pending-badge {
  background: #fef3c7;
  color: #92400e;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
}

.empty-state {
  color: #64748b;
  font-size: 0.88rem;
}
</style>