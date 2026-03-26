<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Tarefas</h1>
        <p class="page-subtitle">Gerencie e acompanhe todas as suas tarefas</p>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <input v-model="search" class="input" style="width:180px;" placeholder="🔍 Buscar..." @input="debouncedFetch" />
        <select v-model="filterStatus" class="input" style="width:130px;" @change="fetchTasks">
          <option value="">Todos status</option>
          <option value="todo">A Fazer</option>
          <option value="in_progress">Em Progresso</option>
          <option value="review">Revisão</option>
          <option value="done">Concluído</option>
        </select>
        <button class="btn" @click="openModal()">+ Nova Tarefa</button>
      </div>
    </div>

    <!-- Kanban -->
    <div v-if="!store.loading" class="kanban">
      <div v-for="col in columns" :key="col.status" class="kanban-col">
        <div class="col-header">
          <div :class="`col-label s-${col.status}`">
            <span class="material-icons">{{ col.icon }}</span>{{ col.label }}
          </div>
          <span class="col-count">{{ store.byStatus[col.status]?.length ?? 0 }}</span>
        </div>

        <div
          v-for="task in store.byStatus[col.status]"
          :key="task.id"
          class="task-card"
          @click="openModal(task)"
        >
          <div class="task-title">{{ task.title }}</div>
          <div v-if="task.client" class="task-client">🏢 {{ task.client.name }}</div>
          <div class="task-footer">
            <span class="badge-status" :class="`p-${task.priority}`">{{ labels.priority[task.priority] }}</span>
            <span class="task-date" :class="{ overdue: isOverdue(task) }">
              {{ task.due_date ? formatDate(task.due_date) : '' }}
            </span>
          </div>
          <div class="task-actions">
            <button class="task-action-btn danger" @click.stop="confirmDelete(task)" title="Excluir"><span class="material-icons">delete</span></button>
            <button
              v-for="s in nextStatuses(task.status)"
              :key="s.value"
              :class="['task-action-btn', s.value]"
              @click.stop="store.updateStatus(task.id, s.value)"
              :title="s.label"
            >
            <span :class="s.iconClass">{{s.icon}}</span>
          </button>
            
          </div>
        </div>

        <div v-if="!store.byStatus[col.status]?.length" class="col-empty">
          Nenhuma tarefa
        </div>

        <button class="add-task-btn" @click="openModal({ status: col.status })">+ Adicionar</button>
      </div>
    </div>

    <div v-else style="display:flex;justify-content:center;padding:60px;">
      <div class="spinner" style="width:36px;height:36px;"></div>
    </div>

    <!-- Modal -->
    <div v-if="modal.show" class="modal-overlay" @click.self="modal.show = false">
      <div class="modal">
        <h2 class="modal-title">{{ modal.task?.id ? 'Editar Tarefa' : 'Nova Tarefa' }}</h2>
        <form @submit.prevent="saveTask">
          <div class="form-group">
            <label class="label">Título *</label>
            <input v-model="form.title" class="input" placeholder="Título da tarefa" required />
          </div>
          <div class="form-group">
            <label class="label">Descrição</label>
            <textarea v-model="form.description" class="input" rows="3" placeholder="Detalhes opcionais..." style="resize:vertical;"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="label">Status</label>
              <select v-model="form.status" class="input">
                <option value="todo">A Fazer</option>
                <option value="in_progress">Em Progresso</option>
                <option value="review">Revisão</option>
                <option value="done">Concluído</option>
              </select>
            </div>
            <div class="form-group">
              <label class="label">Prioridade</label>
              <select v-model="form.priority" class="input">
                <option value="low">Baixa</option>
                <option value="medium">Média</option>
                <option value="high">Alta</option>
                <option value="urgent">Urgente</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="label">Prazo</label>
              <input v-model="form.due_date" type="date" class="input" />
            </div>
            <div class="form-group">
              <label class="label">Cliente</label>
              <select v-model="form.client_id" class="input">
                <option :value="null">Sem cliente</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>
          <div v-if="formError" class="auth-error" style="margin-bottom:12px;">{{ formError }}</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="modal.show = false">Cancelar</button>
            <button type="submit" class="btn" :disabled="saving">
              <span v-if="saving" class="spinner" style="width:12px;height:12px;border-width:2px;"></span>
              <span v-else>{{ modal.task?.id ? 'Salvar' : 'Criar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTaskStore }   from '@/stores/tasks'
import { useClientStore } from '@/stores/clients'

const store   = useTaskStore()
const cStore  = useClientStore()
const clients = ref([])

const search       = ref('')
const filterStatus = ref('')
const modal        = ref({ show: false, task: null })
const form         = ref({})
const saving       = ref(false)
const formError    = ref(null)
const toast        = ref(null)

const labels = {
  priority: { urgent: 'Urgente', high: 'Alta', medium: 'Média', low: 'Baixa' },
}

const columns = [
  { status: 'todo',        label: 'A Fazer',      icon: 'crop_square'},
  { status: 'in_progress', label: 'Em Progresso', icon: 'play_arrow'},
  { status: 'review',      label: 'Revisão',      icon: 'restore'},
  { status: 'done',        label: 'Concluído',    icon: 'done'},
]

const nextStatuses = (current) => {
  const all = [
    { value: 'todo',        iconClass: 'material-icons', icon: 'crop_square', label: 'A Fazer' },
    { value: 'in_progress', iconClass: 'material-icons', icon: 'play_arrow', label: 'Em Progresso' },
    { value: 'review',      iconClass: 'material-icons', icon: 'restore', label: 'Revisão' },
    { value: 'done',        iconClass: 'material-icons', icon: 'done', label: 'Concluído' },
  ] 
  return all.filter(s => s.value !== current)
}

const isOverdue = (task) => task.due_date && new Date(task.due_date) < new Date() && task.status !== 'done'

function formatDate(date) {
  return new Date(date + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

let debounceTimer
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchTasks, 400)
}

async function fetchTasks() {
  await store.fetchTasks({ search: search.value, status: filterStatus.value })
}

function openModal(task = null) {
  modal.value.task = task
  form.value = task?.id
    ? { title: task.title, description: task.description, status: task.status, priority: task.priority, due_date: task.due_date, client_id: task.client_id }
    : { title: '', description: '', status: task?.status ?? 'todo', priority: 'medium', due_date: '', client_id: null }
  formError.value  = null
  modal.value.show = true
}

async function saveTask() {
  saving.value    = true
  formError.value = null
  try {
    if (modal.value.task?.id) {
      await store.updateTask(modal.value.task.id, form.value)
      showToast('✓ Tarefa atualizada!')
    } else {
      await store.createTask(form.value)
      showToast('✓ Tarefa criada!')
    }
    modal.value.show = false
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

async function confirmDelete(task) {
  if (!confirm(`Excluir "${task.title}"?`)) return
  await store.deleteTask(task.id)
  showToast('🗑 Tarefa excluída')
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => toast.value = null, 3000)
}

onMounted(async () => {

  await fetchTasks();
  // Em vez de extrair { data }, apenas chame a função
  await cStore.fetchClients();
  // Garanta que a sua ref local receba os dados da store
  clients.value = cStore.clients;
})
</script>

<style scoped>
.kanban { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; align-items: start;margin:10px 0; }
.kanban-col {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 14px;
  display: flex; flex-direction: column; gap: 8px;
}
.col-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
.col-label { background:none; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; display: flex; align-items: center; gap: 6px; }
.col-dot { width: 6px; height: 6px; border-radius: 50%; }
.col-count { font-size: .65rem; background: var(--surface2); border: 1px solid var(--border2); padding: 2px 7px; border-radius: 20px; color: var(--muted2); }
.col-empty { font-size: .75rem; color: var(--muted); text-align: center; padding: 16px 0; }

.task-card {
  background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px;
  padding: 11px 12px; cursor: pointer; transition: border-color .15s, transform .15s;
  position: relative;
}
.task-card:hover { border-color: var(--accent); transform: translateY(-1px); }
.task-card:hover .task-actions { opacity: 1; }
.task-title  { font-size: .8rem; font-weight: 500; margin-bottom: 4px; line-height: 1.3; }
.task-client { font-size: .65rem; color: var(--muted2); margin-bottom: 6px; }
.task-footer { margin-top:10px; display: flex; align-items: center; justify-content: space-between; }
.task-date   { font-size: .65rem; color: var(--muted); }
.task-date.overdue { color: var(--accent2); font-weight: 600; }

.task-actions {
  position: absolute; top: 6px; right: 6px;
  display: flex; gap: 2px; opacity: 0; transition: opacity .15s;
}
.task-action-btn {
  width: 28px;
  height: 28px;
  background: var(--surface); border: 1px solid var(--border2); border-radius: 4px;
  font-size: .7rem; color: var(--muted2); transition: all .1s;
}
.task-action-btn:hover { color: var(--text); border-color: var(--accent); }
.task-action-btn.danger:hover { color: var(--accent2); border-color: var(--accent2); }
.task-action-btn.todo:hover { color: var(--accent5); border-color: var(--accent5); }
.task-action-btn.in_progress:hover { color: var(--accent4); border-color: var(--accent4 ); }
.task-action-btn.review:hover { color: var(--accent); border-color: var(--accent); }
.task-action-btn.done:hover { color: var(--accent3); border-color: var(--accent3); }

.add-task-btn {
  background: none; border: 1px dashed var(--border2); border-radius: 6px;
  padding: 7px; width: 100%; color: var(--muted); font-size: .75rem;
  transition: all .15s; margin-top: 2px;
}
.add-task-btn:hover { border-color: var(--accent); color: var(--accent); background: #6c63ff08; }

.auth-error { background: #ff658422; color: var(--accent2); border-radius: 6px; padding: 8px 12px; font-size: .8rem; }
</style>