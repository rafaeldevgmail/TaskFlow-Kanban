import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useTaskStore = defineStore('tasks', () => {
  const tasks   = ref([])
  const loading = ref(false)
  const error   = ref(null)

  const byStatus = computed(() => ({
    todo:        tasks.value.filter(t => t.status === 'todo'),
    in_progress: tasks.value.filter(t => t.status === 'in_progress'),
    review:      tasks.value.filter(t => t.status === 'review'),
    done:        tasks.value.filter(t => t.status === 'done'),
  }))

  async function fetchTasks(params = {}) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.get('/tasks', { params })
      tasks.value = data.data
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Erro ao carregar tarefas'
    } finally {
      loading.value = false
    }
  }

  async function createTask(payload) {
    const { data } = await api.post('/tasks', payload)
    tasks.value.unshift(data)
    return data
  }

  async function updateTask(id, payload) {
    const { data } = await api.put(`/tasks/${id}`, payload)
    const idx = tasks.value.findIndex(t => t.id === id)
    if (idx !== -1) tasks.value[idx] = data
    return data
  }

  async function updateStatus(id, status) {
    const { data } = await api.patch(`/tasks/${id}/status`, { status })
    const idx = tasks.value.findIndex(t => t.id === id)
    if (idx !== -1) tasks.value[idx] = data
    return data
  }

  async function deleteTask(id) {
    await api.delete(`/tasks/${id}`)
    tasks.value = tasks.value.filter(t => t.id !== id)
  }

  return { tasks, loading, error, byStatus, fetchTasks, createTask, updateTask, updateStatus, deleteTask }
})