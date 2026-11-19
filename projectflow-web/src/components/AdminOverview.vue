<script setup>
defineProps({
  summary: { type: Object, required: true },
  fallbackTotal: { type: Number, default: 0 },
});
</script>

<template>
  <section class="card admin-card">
    <div class="admin-header">
      <h2>🛡️ Admin Overview</h2>
      <span class="admin-tag">role:admin</span>
    </div>
    <div class="admin-metrics">
      <div class="metric-box">
        <span>Total Tasks</span>
        <strong>{{ summary.total_tasks ?? fallbackTotal }}</strong>
      </div>
      <div class="metric-box">
        <span>Total Users</span>
        <strong>{{ summary.total_users ?? '-' }}</strong>
      </div>
      <div class="metric-box">
        <span>Status</span>
        <strong>{{ summary.message || 'Admin access granted' }}</strong>
      </div>
    </div>
  </section>
</template>

<style scoped>
.admin-card {
  border-left: 4px solid #f59e0b;
  background: #fffbeb;
}
.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.85rem;
}
.admin-header h2 { margin: 0; font-size: 1.1rem; }
.admin-tag {
  font-size: 0.72rem;
  font-weight: 700;
  background: #fef3c7;
  color: #92400e;
  padding: 0.2rem 0.55rem;
  border-radius: 6px;
}
.admin-metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}
.metric-box {
  background: #fff;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 0.65rem 0.85rem;
  display: flex;
  flex-direction: column;
}
.metric-box span { font-size: 0.75rem; color: #78350f; }
.metric-box strong { font-size: 1rem; color: #0f172a; margin-top: 0.15rem; }
@media (max-width: 640px) {
  .admin-metrics { grid-template-columns: 1fr; }
}
</style>