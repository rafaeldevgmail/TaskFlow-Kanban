<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-logo"><span class="logo-icon">⚡</span><span>TaskFlow</span></div>
      <h1 class="auth-title">Bem-vindo de volta</h1>
      <p class="auth-sub">Faça login para continuar</p>

      <form @submit.prevent="submit" class="auth-form">
        <div class="form-group">
          <label class="label">E-mail</label>
          <input v-model="form.email" type="email" class="input" placeholder="seu@email.com" required />
        </div>
        <div class="form-group">
          <label class="label">Senha</label>
          <input v-model="form.password" type="password" class="input" placeholder="••••••••" required />
        </div>
        <div v-if="error" class="auth-error">{{ error }}</div>
        <button type="submit" class="btn" style="width:100%;justify-content:center;" :disabled="loading">
          <span v-if="loading" class="spinner" style="width:14px;height:14px;border-width:2px;"></span>
          <span v-else>Entrar</span>
        </button>
      </form>

      <p class="auth-link">Não tem conta? <RouterLink to="/register">Criar conta</RouterLink></p>
      <p class="auth-hint">Demo: demo@taskflow.dev / password</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const loading = ref(false)
const error   = ref(null)
const form    = ref({ email: 'demo@taskflow.dev', password: 'password' })

async function submit() {
  loading.value = true
  error.value   = null
  try {
    await auth.login(form.value.email, form.value.password)
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Erro ao fazer login'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: var(--bg); padding: 20px;
  background-image: radial-gradient(ellipse at 20% 50%, #6c63ff11 0%, transparent 60%),
                    radial-gradient(ellipse at 80% 20%, #ff658411 0%, transparent 60%);
}
.auth-card {
  width: 100%; max-width: 400px; background: var(--surface);
  border: 1px solid var(--border2); border-radius: 16px; padding: 40px;
}
.auth-logo {
  font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.3rem;
  display: flex; align-items: center; gap: 10px; margin-bottom: 32px;
}
.auth-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700; }
.auth-sub   { color: var(--muted); font-size: .85rem; margin: 6px 0 24px; }
.auth-form  { display: flex; flex-direction: column; }
.auth-error { background: #ff658422; color: var(--accent2); border-radius: 6px; padding: 8px 12px; font-size: .8rem; margin-bottom: 12px; }
.auth-link  { text-align: center; margin-top: 20px; font-size: .8rem; color: var(--muted2); }
.auth-link a { color: var(--accent); font-weight: 500; }
.auth-hint  { text-align: center; margin-top: 8px; font-size: .7rem; color: var(--muted); }
</style>