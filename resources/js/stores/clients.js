import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useClientStore = defineStore('clients', () => {
  const clients = ref([])
  const loading = ref(false)

  async function fetchClients(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/clients', { params })
      clients.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createClient(payload) {
    const { data } = await api.post('/clients', payload)
    clients.value.unshift(data)
    return data
  }

  async function updateClient(id, payload) {
    const { data } = await api.put(`/clients/${id}`, payload)
    const idx = clients.value.findIndex(c => c.id === id)
    if (idx !== -1) clients.value[idx] = data
    return data
  }

  async function deleteClient(id) {
    await api.delete(`/clients/${id}`)
    clients.value = clients.value.filter(c => c.id !== id)
  }

  async function getClient(id) {
    const { data } = await api.get(`/clients/${id}`)
    return data
  }

  return { clients, loading, fetchClients, createClient, updateClient, deleteClient, getClient }
})