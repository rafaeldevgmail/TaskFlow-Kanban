import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user') ?? 'null'))
  const token = ref(localStorage.getItem('token') ?? null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email, password) {
    const { data } = await api.post('/auth/login', { email, password })
    setSession(data)
    router.push('/dashboard')
  }

  async function register(name, email, password, password_confirmation) {
    const { data } = await api.post('/auth/register', { name, email, password, password_confirmation })
    setSession(data)
    router.push('/dashboard')
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch {}
    clearSession()
    router.push('/login')
  }

  function setSession({ user: u, token: t }) {
    user.value  = u
    token.value = t
    localStorage.setItem('user', JSON.stringify(u))
    localStorage.setItem('token', t)
  }

  function clearSession() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, login, register, logout }
})