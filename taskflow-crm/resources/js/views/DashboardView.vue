<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard <span>CRM</span></h1>
        <p class="page-subtitle">Visão geral do sistema · {{ today }}</p>
      </div>
      <div style="display:flex;gap:8px;">
        <RouterLink to="/tasks" class="btn btn-ghost btn-sm">Ver Tarefas</RouterLink>
        <RouterLink to="/clients" class="btn btn-sm">+ Novo Cliente</RouterLink>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" v-if="dashboard">
      <div class="stat-card stat-1">
        <div class="stat-label">Total de Tarefas</div>
        <div class="stat-value">{{ dashboard.stats.total_tasks }}</div>
        <div class="stat-meta">{{ dashboard.stats.tasks.done ?? 0 }} concluídas</div>
      </div>
      <div class="stat-card stat-2">
        <div class="stat-label">Em Progresso</div>
        <div class="stat-value">{{ dashboard.stats.tasks.in_progress ?? 0 }}</div>
        <div class="stat-meta">{{ dashboard.stats.tasks.review ?? 0 }} em revisão</div>
      </div>
      <div class="stat-card stat-3">
        <div class="stat-label">Clientes Ativos</div>
        <div class="stat-value">{{ dashboard.stats.active_clients }}</div>
        <div class="stat-meta">{{ dashboard.stats.clients.prospect ?? 0 }} prospects</div>
      </div>
      <div class="stat-card stat-4">
        <div class="stat-label">Taxa de Conclusão</div>
        <div class="stat-value">{{ dashboard.stats.completion_rate }}<small>%</small></div>
        <div class="stat-meta">eficiência geral</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" style="display:flex;justify-content:center;padding:40px;">
      <div class="spinner" style="width:32px;height:32px;"></div>
    </div>

    <div v-if="dashboard" class="content-grid">
      <!-- Recent tasks -->
      <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
          <h2 style="font-family:'Syne',sans-serif;font-size:.95rem;font-weight:700;">Tarefas Recentes</h2>
          <RouterLink to="/tasks" style="font-size:.72rem;color:var(--accent);">Ver todas →</RouterLink>
        </div>
        <div v-for="task in dashboard.recent_tasks" :key="task.id" class="task-row" :class="{ 'disabled badge': task.status === 'done' }">
          <div class="task-row-info">
            <div class="task-row-title">{{ task.title }}</div>
            <div class="task-row-meta">
              <span v-if="task.client">🏢 {{ task.client.name }} ·</span>
              {{ task.due_date ? formatDate(task.due_date) : 'Sem prazo' }}
            </div>
          </div>
          <div style="display:flex;gap:6px;align-items:center;">
            <span class="badge-status" :class="`p-${task.priority}`">{{ labels.priority[task.priority] }}</span>
            <span class="badge-status badge_custom" :class="`s-${task.status}`"><span :class="`material-icons s-${task.status}`">{{labels.icon[task.status]}}</span>{{ labels.status[task.status] }}</span>
          </div>
        </div>
        <div v-if="!dashboard.recent_tasks.length" class="empty">
          <div class="empty-icon">✓</div>
          <div class="empty-text">Nenhuma tarefa ainda</div>
        </div>
      </div>

      <!-- Right col -->
      <div style="display:flex;flex-direction:column;gap:14px;">
        <!-- Overdue -->
        <div class="card" v-if="dashboard.overdue_tasks.length">
          <h2 style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;margin-bottom:14px;color:var(--accent2);">
            ⚠ Tarefas Atrasadas
          </h2>
          <div v-for="task in dashboard.overdue_tasks" :key="task.id" class="overdue-item">
            <div style="font-size:.8rem;font-weight:500;">{{ task.title }}</div>
            <div style="font-size:.68rem;color:var(--accent2);">{{ formatDate(task.due_date) }}</div>
          </div>
        </div>

        <!-- Priority breakdown -->
        <div class="card">
          <h2 style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;margin-bottom:14px;">Por Prioridade</h2>
          <div v-for="(count, p) in dashboard.stats.priority" :key="p" class="priority-bar-row">
            <span class="badge-status" :class="`p-${p}`" style="width:60px;justify-content:center;">{{ labels.priority[p] }}</span>
            <div class="pbar-track">
              <div class="pbar-fill" :class="`pbar-${p}`"
                :style="{ width: maxPriority ? (count / maxPriority * 100) + '%' : '0%' }">
              </div>
            </div>
            <span style="font-size:.75rem;font-weight:600;min-width:20px;text-align:right;">{{ count }}</span>
          </div>
        </div>

        <!-- Completed by day -->
        <div class="card">
          <h2 style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;margin-bottom:14px;">Concluídas / dia</h2>
          <div class="mini-chart">
            <div v-for="d in chartDays" :key="d.date" class="mini-bar-col">
              <div class="mini-bar" :style="{ height: d.height + '%' }"></div>
              <div class="mini-bar-label">{{ d.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const dashboard = ref(null)
const loading   = ref(true)
const today     = new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

const labels = {
  status:   { todo: 'A Fazer', in_progress: 'Em Progresso', review: 'Revisão', done: 'Concluído' },
  icon:   { todo: 'crop_square', in_progress: 'play_arrow', review: 'restore', done: 'done' },
  priority: { urgent: 'Urgente', high: 'Alta', medium: 'Média', low: 'Baixa' },
}

const maxPriority = computed(() => Math.max(...Object.values(dashboard.value?.stats.priority ?? { a: 1 })))

const chartDays = computed(() => {
  const days = []
  for (let i = 6; i >= 0; i--) {
    const d    = new Date(); d.setDate(d.getDate() - i)
    const iso  = d.toISOString().split('T')[0]
    const rec  = dashboard.value?.completed_by_day?.find(r => r.date === iso)
    days.push({ date: iso, label: d.toLocaleDateString('pt-BR', { weekday: 'short' }).slice(0,3), count: rec?.total ?? 0 })
  }
  const max = Math.max(...days.map(d => d.count), 1)
  return days.map(d => ({ ...d, height: Math.max((d.count / max) * 100, 4) }))
})

function formatDate(date) {
  return new Date(date + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/dashboard')
    dashboard.value = data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.task-row.disabled, 
.task-card.disabled {
  opacity: 0.6; /* Deixa o card "apagadinho" */
  background-color: #313131; /* Um fundo levemente cinza */
  transition: all 0.3s ease;
  pointer-events: none; /* Opcional: impede cliques se não quiser edição */
  user-select: none;
}

/* Deixa o texto com efeito de riscado (opcional) */
.disabled .task-title {
  text-decoration: line-through;
  color: #718096;
}

/* Remove sombras para parecer "plano" e sem profundidade */
.disabled {
  box-shadow: none !important;
  transform: none !important;
}
.badge_custom .material-icons{background:none; padding:0 2px 0 0; font-size:12px;}
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;margin:10px 0; }
.stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
.stat-1::before { background: var(--accent); }
.stat-2::before { background: var(--accent4); }
.stat-3::before { background: var(--accent3); }
.stat-4::before { background: var(--accent2); }
.stat-label { font-size: .68rem; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; font-weight: 500; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 2rem; font-weight: 800; margin: 6px 0 4px; letter-spacing: -.04em; }
.stat-value small { font-size: 1rem; }
.stat-meta  { font-size: .7rem; color: var(--muted2); }

.content-grid { display: grid; grid-template-columns: 1fr 300px; gap: 14px; margin:10px 0; }

.task-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid var(--border); gap: 12px; }
.task-row:last-child { border-bottom: none; }
.task-row-title { font-size: .82rem; font-weight: 500; }
.task-row-meta  { font-size: .68rem; color: var(--muted); margin-top: 3px; }

.overdue-item { padding: 8px 0; border-bottom: 1px solid var(--border); }
.overdue-item:last-child { border-bottom: none; }

.priority-bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.pbar-track { flex: 1; height: 4px; background: var(--surface2); border-radius: 2px; overflow: hidden; }
.pbar-fill  { height: 100%; border-radius: 2px; transition: width .6s ease; }
.pbar-urgent { background: var(--accent2); }
.pbar-high   { background: var(--accent4); }
.pbar-medium { background: var(--accent); }
.pbar-low    { background: var(--accent3); }

.mini-chart { display: flex; align-items: flex-end; gap: 4px; height: 60px; }
.mini-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
.mini-bar { width: 100%; background: var(--accent); border-radius: 3px 3px 0 0; min-height: 4px; transition: height .4s ease; opacity: .75; }
.mini-bar-label { font-size: .58rem; color: var(--muted); }
</style>