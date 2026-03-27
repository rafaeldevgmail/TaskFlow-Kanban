<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Clientes</h1>
        <p class="page-subtitle">Gerencie sua base de clientes e prospects</p>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <input v-model="search" class="input" style="width:180px;" placeholder="🔍 Buscar..." @input="debouncedFetch" />
        <select v-model="filterStatus" class="input" style="width:130px;" @change="fetchClients">
          <option value="">Todos</option>
          <option value="active">Ativo</option>
          <option value="prospect">Prospect</option>
          <option value="inactive">Inativo</option>
        </select>
        <button class="btn" @click="openModal()">+ Novo Cliente</button>
      </div>
    </div>

    <!-- Table -->
    <div class="card" style="padding:0;overflow:hidden;">
      <div v-if="store.loading" style="display:flex;justify-content:center;padding:48px;">
        <div class="spinner" style="width:32px;height:32px;"></div>
      </div>

      <table v-else class="clients-table">
        <thead>
          <tr>
            <th>Nome / Empresa</th>
            <th>Contato</th>
            <th>Status</th>
            <th>Tarefas</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="client in store.clients" :key="client.id" @click="router.push(`/clients/${client.id}`)" style="cursor:pointer;">
            <td>
              <div class="client-name-cell">
                <div class="client-avatar-sm">{{ initials(client.name) }}</div>
                <div>
                  <div class="client-name-text">{{ client.name }}</div>
                  <div class="client-company-text">{{ client.company ?? '—' }}</div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:.78rem;">{{ client.email ?? '—' }}</div>
              <div style="font-size:.7rem;color:var(--muted);">{{ client.phone ?? '' }}</div>
            </td>
            <td>
              <span class="badge-status" :class="`s-${client.status}`">{{ labels[client.status] }}</span>
            </td>
            <td style="font-size:.8rem;font-weight:600;">{{ client.tasks_count ?? 0 }}</td>
            <td @click.stop>
              <div style="display:flex;gap:6px;">
                <button class="btn btn-ghost btn-sm" @click="openModal(client)">Editar</button>
                <button class="btn btn-sm" style="background:var(--surface3);color:var(--accent2);box-shadow:none;" @click="confirmDelete(client)"><span class="material-icons">delete</span></button>
              </div>
            </td>
          </tr>
          <tr v-if="!store.clients.length">
            <td colspan="5" style="text-align:center;padding:48px;color:var(--muted);">
              Nenhum cliente encontrado
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modal.show" class="modal-overlay" @click.self="modal.show = false">
      <div class="modal">
        <h2 class="modal-title">{{ modal.client?.id ? 'Editar Cliente' : 'Novo Cliente' }}</h2>
        <form @submit.prevent="saveClient">
          <div class="form-row">
            <div class="form-group">
              <label class="label">Nome *</label>
              <input v-model="form.name" class="input" placeholder="Nome completo" required />
            </div>
            <div class="form-group">
              <label class="label">Empresa</label>
              <input v-model="form.company" class="input" placeholder="Nome da empresa" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="label">E-mail</label>
              <input v-model="form.email" type="email" class="input" placeholder="email@empresa.com" />
            </div>
            <div class="form-group">
              <label class="label">Telefone</label>
              <input v-model="form.phone" class="input" placeholder="(11) 99999-9999" />
            </div>
          </div>
          <div class="form-group">
            <label class="label">Status</label>
            <select v-model="form.status" class="input">
              <option value="prospect">Prospect</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
          <div class="form-group">
            <label class="label">Observações</label>
            <textarea v-model="form.notes" class="input" rows="3" placeholder="Notas internas..." style="resize:vertical;"></textarea>
          </div>
          <div v-if="formError" class="auth-error" style="margin-bottom:12px;">{{ formError }}</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="modal.show = false">Cancelar</button>
            <button type="submit" class="btn" :disabled="saving">
              <span v-if="saving" class="spinner" style="width:12px;height:12px;border-width:2px;"></span>
              <span v-else>{{ modal.client?.id ? 'Salvar' : 'Criar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter }       from 'vue-router'
import { useClientStore }  from '@/stores/clients'

const store  = useClientStore()
const router = useRouter()

const search       = ref('')
const filterStatus = ref('')
const modal        = ref({ show: false, client: null })
const form         = ref({})
const saving       = ref(false)
const formError    = ref(null)
const toast        = ref(null)

const labels = { active: 'Ativo', prospect: 'Prospect', inactive: 'Inativo' }

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()

let debounceTimer
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchClients, 400)
}

async function fetchClients() {
  await store.fetchClients({ search: search.value, status: filterStatus.value })
}

function openModal(client = null) {
  modal.value.client = client
  form.value = client
    ? { name: client.name, company: client.company, email: client.email, phone: client.phone, status: client.status, notes: client.notes }
    : { name: '', company: '', email: '', phone: '', status: 'prospect', notes: '' }
  formError.value  = null
  modal.value.show = true
}

async function saveClient() {
  saving.value    = true
  formError.value = null
  try {
    if (modal.value.client?.id) {
      await store.updateClient(modal.value.client.id, form.value)
      showToast('✓ Cliente atualizado!')
    } else {
      await store.createClient(form.value)
      showToast('✓ Cliente criado!')
    }
    modal.value.show = false
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(' ') : 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

async function confirmDelete(client) {
  if (!confirm(`Excluir cliente "${client.name}"? As tarefas vinculadas serão desvinculadas.`)) return
  await store.deleteClient(client.id)
  showToast('🗑 Cliente excluído')
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => toast.value = null, 3000)
}

onMounted(fetchClients)
</script>

<style scoped>
.card{margin:10px 0;}
.clients-table { width: 100%; border-collapse: collapse; }
.clients-table th {
  text-align: left; padding: 12px 16px; font-size: .68rem;
  font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
  color: var(--muted); border-bottom: 1px solid var(--border); background: var(--surface2);
}
.clients-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
.clients-table tr:last-child td { border-bottom: none; }
.clients-table tr:hover td { background: var(--surface2); }

.client-name-cell { display: flex; align-items: center; gap: 10px; }
.client-avatar-sm {
  width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
  background: #6c63ff22; color: var(--accent);
  display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700;
}
.client-name-text    { font-size: .82rem; font-weight: 500; }
.client-company-text { font-size: .7rem; color: var(--muted); }

.auth-error { background: #ff658422; color: var(--accent2); border-radius: 6px; padding: 8px 12px; font-size: .8rem; }
</style>