<template>
  <div id="app" :data-theme="theme">
    <!-- Layout com sidebar para rotas autenticadas -->
    <template v-if="auth.isAuthenticated">
      <div class="app-layout">
        <aside class="sidebar">
          <div class="logo">
            <span class="logo-icon">⚡</span>
            TaskFlow
          </div>

          <nav>
            <span class="nav-label">Principal</span>
            <RouterLink to="/dashboard" class="nav-item">
              <span>◼</span> Dashboard
            </RouterLink>
            <RouterLink to="/tasks" class="nav-item">
              <span>✓</span> Tarefas
              <span v-if="taskStore.byStatus.todo?.length" class="badge">
                {{ taskStore.byStatus.todo.length }}
              </span>
            </RouterLink>
            <RouterLink to="/clients" class="nav-item">
              <span>◎</span> Clientes
            </RouterLink>
          </nav>

          <div class="theme-toggle" @click="toggleTheme">
            <span class="theme-toggle-icon">{{ theme === 'dark' ? '☀️' : '🌙' }}</span>
            <span class="theme-toggle-label">{{ theme === 'dark' ? 'Tema Claro' : 'Tema Escuro' }}</span>
            <div class="toggle-track">
              <div class="toggle-thumb" :class="{ active: theme === 'light' }"></div>
            </div>
          </div>

          <div class="sidebar-footer">
            <div class="avatar">{{ initials }}</div>
            <div class="user-info">
              <div class="user-name">{{ auth.user?.name }}</div>
              <div class="user-role">{{ auth.user?.email }}</div>
            </div>
            <button class="logout-btn" @click="auth.logout()" title="Sair">⏏</button>
          </div>
        </aside>

        <main class="main-content">
          <RouterView />
        </main>
      </div>
    </template>

    <!-- Layout simples para login/register -->
    <template v-else>
      <RouterView />
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTaskStore } from '@/stores/tasks'

const auth      = useAuthStore()
const taskStore = useTaskStore()
const initials  = computed(() =>
  auth.user?.name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? 'U'
)

const theme = ref(localStorage.getItem('theme') ?? 'dark')

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  localStorage.setItem('theme', theme.value)
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap');
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
@import url('https://cdn.tailwindcss.com');
/* ── TEMA ESCURO (padrão) ── */
:root, [data-theme="dark"] {
  --bg:       #0a0a0f;
  --surface:  #111118;
  --surface2: #1a1a24;
  --surface3: #22222f;
  --border:   #ffffff0f;
  --border2:  #ffffff18;
  --text:     #97979e;
  --muted:    #6b6b85;
  --muted2:   #9999b3;
  --shadow:   0 4px 24px #00000055;
}

/* ── TEMA CLARO ── */
[data-theme="light"] {
  --bg:       #f0f0f7;
  --surface:  #ffffff;
  --surface2: #f5f5fc;
  --surface3: #ebebf5;
  --border:   #e0e0ef;
  --border2:  #d0d0e8;
  --text:     #1a1a2e;
  --muted:    #8888aa;
  --muted2:   #6666aa;
  --shadow:   0 4px 24px #0000001a;
}

/* Accent colors — iguais nos dois temas */
:root {
  --accent:  #6c63ff;
  --accent2: #ff6584;
  --accent3: #43e97b;
  --accent4: #f7971e;
  --accent5: #1eb2f7;
  --radius:    10px;
  --radius-sm: 6px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  transition: background .3s, color .3s;
}
a { color: inherit; text-decoration: none; }
button { cursor: pointer; font-family: inherit; }

/* ── LAYOUT ── */
.app-layout { display: flex; min-height: 100vh; }

/* ── SIDEBAR ── */
.sidebar {
  width: 220px; min-height: 100vh; flex-shrink: 0;
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  padding: 24px 0;
  position: sticky; top: 0; height: 100vh;
  transition: background .3s, border-color .3s;
}
.logo {
  font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.2rem;
  letter-spacing: -.03em; padding: 0 20px 28px;
  display: flex; align-items: center; gap: 10px;
}
.logo-icon {
  width: 30px; height: 30px; background: var(--accent); border-radius: 8px;
  display: flex; align-items: center; justify-content: center; font-size: .9rem;
  box-shadow: 0 0 16px #6c63ff55;
}
nav { flex: 1; display: flex; flex-direction: column; }
.nav-label {
  font-size: .65rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase;
  color: var(--muted); padding: 0 20px 8px; margin-top: 8px;
}
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 9px 20px;
  color: var(--muted2); font-size: .875rem; border-left: 2px solid transparent;
  transition: all .15s; position: relative;
}
.nav-item:hover { color: var(--text); background: var(--border); }
.nav-item.router-link-active { color: var(--accent); border-left-color: var(--accent); background: #6c63ff12; }

[data-theme="light"] .nav-item.router-link-active { color: var(--accent); background: #6c63ff18; }

.badge {
  margin-left: auto; background: var(--accent); color: #fff;
  font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 20px;
}

/* ── THEME TOGGLE ── */
.theme-toggle {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 20px; margin: 8px 0;
  cursor: pointer; border-top: 1px solid var(--border);
  transition: background .15s;
}
.theme-toggle:hover { background: var(--surface2); }
.theme-toggle-icon  { font-size: 1rem; }
.theme-toggle-label { font-size: .78rem; color: var(--muted2); flex: 1; }
.toggle-track {
  width: 32px; height: 18px; background: var(--surface3);
  border: 1px solid var(--border2); border-radius: 20px;
  position: relative; transition: background .3s;
}
.toggle-thumb {
  position: absolute; top: 2px; left: 2px;
  width: 12px; height: 12px; border-radius: 50%;
  background: var(--muted); transition: all .3s;
}
.toggle-thumb.active {
  left: 16px;
  background: var(--accent);
}
.theme-toggle:hover .toggle-track { border-color: var(--accent); }

/* ── SIDEBAR FOOTER ── */
.sidebar-footer {
  padding: 16px 20px; border-top: 1px solid var(--border);
  display: flex; align-items: center; gap: 10px;
}
.avatar {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .75rem; color: #fff;
}
.user-info { flex: 1; overflow: hidden; }
.user-name { font-size: .8rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: .65rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.logout-btn {
  background: none; border: none; color: var(--muted); font-size: 1rem; padding: 4px;
  transition: color .15s;
}
.logout-btn:hover { color: var(--accent2); }

/* ── MAIN ── */
.main-content {
  flex: 1; overflow-y: auto; padding: 32px;
  display: flex; flex-direction: column; gap: 24px;
  background: var(--bg); transition: background .3s;
}

/* ── SHARED COMPONENTS ── */
.page-header    { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.page-title     { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 700; letter-spacing: -.03em; }
.page-title span { color: var(--accent); }
.page-subtitle  { font-size: .75rem; color: var(--muted); margin-top: 4px; }

.btn {
  display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px;
  background: var(--accent); color: #fff; border: none; border-radius: var(--radius-sm);
  font-family: 'Syne', sans-serif; font-weight: 600; font-size: .8rem;
  box-shadow: 0 0 16px #6c63ff33; transition: opacity .15s;
}
.btn:hover    { opacity: .85; }
.btn-ghost    { background: var(--surface2); border: 1px solid var(--border2); box-shadow: none; color: var(--text); }
.btn-danger   { background: var(--accent2); box-shadow: 0 0 12px #ff658433; }
.btn-sm       { padding: 6px 12px; font-size: .75rem; }

.card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 20px;
  transition: background .3s, border-color .3s;
  box-shadow: var(--shadow);
}

.input {
  width: 100%; background: var(--surface2); border: 1px solid var(--border2);
  border-radius: var(--radius-sm); padding: 9px 12px; color: var(--text);
  font-family: inherit; font-size: .875rem; transition: border-color .15s; outline: none;
}
.input:focus       { border-color: var(--accent); }
.input::placeholder { color: var(--muted); }
select.input       { appearance: none; cursor: pointer; }

.label {
  display: block; font-size: .72rem; font-weight: 600; color: var(--muted2);
  margin-bottom: 6px; text-transform: uppercase; letter-spacing: .06em;
}

/* ── STATUS BADGES ── */
.badge-status {
  display: inline-flex; font-size: .65rem; font-weight: 700;
  padding: 2px 8px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: .05em;
  align-items: center;
}
.s-todo        { background: #88888822; color: var(--accent5); }
.s-in_progress { background: #f7971e22; color: var(--accent4); }
.s-review      { background: #6c63ff22; color: var(--accent); }
.s-done        { background: #43e97b22; color: var(--accent3); }
.s-active      { background: #43e97b22; color: var(--accent3); }
.s-prospect    { background: #6c63ff22; color: var(--accent); }
.s-inactive    { background: #88888822; color: var(--muted); }
.p-urgent      { background: #ff658422; color: var(--accent2); }
.p-high        { background: #f7971e22; color: var(--accent4); }
.p-medium      { background: #6c63ff22; color: var(--accent); }
.p-low         { background: #43e97b22; color: var(--accent3); }

/* ── MODAL ── */
.modal-overlay {
  position: fixed; inset: 0; background: #00000066;
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 100; padding: 20px;
}
.modal {
  background: var(--surface); border: 1px solid var(--border2);
  border-radius: 14px; padding: 28px;
  width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto;
  box-shadow: var(--shadow);
}
.modal-title  { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 24px; }
.form-row     { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group   { margin-bottom: 14px; }

/* ── TOAST ── */
.toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 200;
  background: var(--surface); border: 1px solid var(--border2);
  border-radius: var(--radius-sm); padding: 12px 16px;
  font-size: .8rem; display: flex; align-items: center; gap: 8px;
  box-shadow: var(--shadow); animation: slideIn .2s ease;
}
@keyframes slideIn { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── EMPTY STATE ── */
.empty      { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .4; }
.empty-text { font-size: .9rem; }

/* ── SPINNER ── */
.spinner {
  width: 24px; height: 24px; border: 2px solid var(--border2);
  border-top-color: var(--accent); border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── SCROLLBAR ── */
::-webkit-scrollbar       { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 2px; }
</style>