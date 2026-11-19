import { shallowRef, ref, computed } from 'vue';
import taskApi from '../services/api';

const projects = shallowRef([]);
const activeProjectId = ref(Number(localStorage.getItem('pf_active_project')) || null);
const invitations = shallowRef([]);
const loadingProjects = ref(false);

export function useProject() {
  const activeProject = computed(() => {
    const list = projects.value;
    if (!list || !list.length) return null;
    return (
      list.find((p) => Number(p.id) === Number(activeProjectId.value)) || list[0]
    );
  });

  function selectProject(id) {
    const numericId = Number(id);
    if (activeProjectId.value === numericId) return;
    activeProjectId.value = numericId;
    localStorage.setItem('pf_active_project', String(numericId));
  }

  async function loadProjects() {
    if (loadingProjects.value) return;
    loadingProjects.value = true;
    try {
      const res = await taskApi.getProjects();
      const rawList = Array.isArray(res.data?.data)
        ? res.data.data
        : Array.isArray(res.data)
        ? res.data
        : [];

      const list = rawList.map((p) => ({
        id: p.id,
        name: p.name,
        key: p.key,
        description: p.description,
        owner_id: p.owner_id,
        tasks_count: p.tasks_count ?? 0,
        members_count: p.members_count ?? 0,
        members: Array.isArray(p.members) ? [...p.members] : [],
      }));

      projects.value = list;

      if (list.length > 0) {
        const exists = list.some((p) => Number(p.id) === Number(activeProjectId.value));
        if (!exists) {
          selectProject(list[0].id);
        }
      } else if (activeProjectId.value !== null) {
        activeProjectId.value = null;
        localStorage.removeItem('pf_active_project');
      }
    } finally {
      loadingProjects.value = false;
    }
  }

  async function loadInvitations() {
    try {
      const res = await taskApi.getInvitations();
      const raw = Array.isArray(res.data?.data)
        ? res.data.data
        : Array.isArray(res.data)
        ? res.data
        : [];
      invitations.value = [...raw];
    } catch {
      invitations.value = [];
    }
  }

  async function createProject(payload) {
    const res = await taskApi.createProject(payload);
    const created = res.data?.data || res.data;
    await loadProjects();
    if (created?.id) {
      selectProject(created.id);
    }
    return created;
  }

  async function acceptInvite(invitationId) {
    const res = await taskApi.acceptInvitation(invitationId);
    await loadInvitations();
    await loadProjects();
    if (res.data?.project?.id) {
      selectProject(res.data.project.id);
    }
  }

  async function declineInvite(invitationId) {
    await taskApi.declineInvitation(invitationId);
    await loadInvitations();
  }

  function resetProjectState() {
    projects.value = [];
    invitations.value = [];
    activeProjectId.value = null;
    localStorage.removeItem('pf_active_project');
  }

  return {
    projects,
    activeProject,
    activeProjectId,
    invitations,
    loadingProjects,
    selectProject,
    loadProjects,
    loadInvitations,
    createProject,
    acceptInvite,
    declineInvite,
    resetProjectState,
  };
}