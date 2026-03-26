<template>
  <div>
    <div v-if="loading" style="display:flex;justify-content:center;padding:60px;">
      <div class="spinner" style="width:36px;height:36px;"></div>
    </div>

    <template v-else-if="client">
      <div class="page-header">
        <div style="display:flex;align-items:center;gap:14px;">
          <button class="btn btn-ghost btn-sm" @click="router.back()">← Voltar</button>
          <div>
            <h1 class="page-title">{{ client.name }}</h1>
            <p class="page-subtitle">{{ client.company ?? 'Sem empresa' }}</p>
          </div>
        </div>
        <div style="display:flex;gap:8px;">
          <span class="badge-status" :class="`s-${client.status}`" style="font-size:.75rem;padding:5px 12px;">
            {{ statusLabels[client.status] }}
          </span>
          <button class="btn btn-ghost btn-sm" @click="editModal = true">Editar</button>
        </div>
      </div>

      <div class="detail-grid">
        <!-- Info card -->
        <div class="card">
          <h2 class="card-title">Informações</h2>
          <div class="info-list">
            <div class="info-item" v-if="client.email">
              <span class="info-label">E-mail</span>
              <span class="info-value">{{ client.email }}</span>
            </div>
            <div class="info-item" v-if="client.phone">
              <span class="info-label">Telefone</span>
              <span class="info-value">{{ client.phone }}</span>
            </div>
            <div class="info-item" v-if="client.company">
              <span class="info-label">Empresa</span>
              <span class="info-value">{{ client.company }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Cliente desde</span>
              <span class="info-value">{{ formatDate(client.created_at) }}</span>
            </div>
            <div class="info-item" v-if="client.notes">
              <span class="info-label">Observações</span>
              <span class="info-value" style="white-space:pre-wrap;">{{ client.notes }}</span>
            </div>
          </div>
        </div>

        <!-- Tasks card -->
        <div class="card" style="grid-column:span 2;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h2 class="card-title" style="margin:0;">Tarefas ({{ client.tasks_count }})</h2>
            <RouterLink :to="`/tasks`" class="btn btn-sm">+ Nova Tarefa</RouterLink>
          </div>

          <div v-if="client.tasks?.length">
            <div v-for="task in client.tasks" :key="task.id" class="task-row-detail">
              <div class="task-row-left">
                <span class="badge-status" :class="`s-${task.status}`">{{ taskStatusLabels[task.status] }}</span>
                <div class="task-row-title">{{ task.title }}</div>
              </div>
              <div style="display:flex;gap:6px;align-items:center;">
                <span class="badge-status" :class="`p-${task.priority}`">{{ priorityLabels[task.priority] }}</span>
                <span style="font-size:.68rem;color:var(--muted);">{{ task.due_date ? formatDate(task.due_date) : '' }}</span>
              </div>
            </div>
          </div>
          <div v-else class="empty">
            <div class="empty-icon">📋</div>
            <div class="empty-text">Nenhuma tarefa para este cliente</div>
          </div>
        </div>
      </div>

      <!-- Edit modal -->
      <div v-if="editModal" class="modal-overlay" @click.self="editModal = false">
        <div class="modal">
          <h2 class="modal-title">Editar Cliente</h2>
          <form @submit.prevent="saveEdit">
            <div class="form-row">
              <div class="form-group">
                <label class="label">Nome *</label>
                <input v-model="editForm.name" class="input" required />
              </div>
              <div class="form-group">
                <label class="label">Empresa</label>
                <input v-model="editForm.company" class="input" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="label">E-mail</label>
                <input v-model="editForm.email" type="email" class="input" />
              </div>
              <div class="form-group">
                <label class="label">Telefone</label>
                <input v-model="editForm.phone" class="input" />
              </div>
            </div>
            <div class="form-group">
              <label class="label">Status</label>
              <select v-model="editForm.status" class="input">
                <option value="prospect">Prospect</option>
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
            </div>
            <div class="form-group">
              <label class="label">Observações</label>
              <textarea v-model="editForm.notes" class="input" rows="3" style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ghost" @click="editModal = false">Cancelar</button>
              <button type="submit" class="btn" :disabled="saving">
                <span v-if="saving" class="spinner" style="width:12px;height:12px;border-width:2px;"></span>
                <span v-else>Salvar</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted }  from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useClientStore }  from '@/stores/clients'

const route   = useRoute()
const router  = useRouter()
const cStore  = useClientStore()

const client    = ref(null)
const loading   = ref(true)
const editModal = ref(false)
const editForm  = ref({})
const saving    = ref(false)

const statusLabels     = { active: 'Ativo', prospect: 'Prospect', inactive: 'Inativo' }
const taskStatusLabels = { todo: 'A Fazer', in_progress: 'Em Progresso', review: 'Revisão', done: 'Concluído' }
const priorityLabels   = { urgent: 'Urgente', high: 'Alta', medium: 'Média', low: 'Baixa' }

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function openEdit() {
  editForm.value = { name: client.value.name, company: client.value.company, email: client.value.email, phone: client.value.phone, status: client.value.status, notes: client.value.notes }
  editModal.value = true
}

async function saveEdit() {
  saving.value = true
  try {
    const updated = await cStore.updateClient(client.value.id, editForm.value)
    client.value  = { ...client.value, ...updated }
    editModal.value = false
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    client.value = await cStore.getClient(route.params.id)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.detail-grid { display: grid; grid-template-columns: 280px 1fr 1fr; gap: 14px; align-items: start; margin:10px 0;}
.card-title  { font-family: 'Syne', sans-serif; font-size: .9rem; font-weight: 700; margin-bottom: 16px; }
.info-list   { display: flex; flex-direction: column; gap: 12px; }
.info-item   { display: flex; flex-direction: column; gap: 3px; }
.info-label  { font-size: .65rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; }
.info-value  { font-size: .82rem; color: var(--text); }

.task-row-detail {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 0; border-bottom: 1px solid var(--border);
}
.task-row-detail:last-child { border-bottom: none; }
.task-row-left  { display: flex; align-items: center; gap: 10px; }
.task-row-title { font-size: .82rem; font-weight: 500; }
</style>