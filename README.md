# ⚡ TaskFlow Kanban

> Sistema de Gerenciamento de Tarefas e Clientes construído com **Laravel 11**, **Vue 3** e **PostgreSQL** (Neon.tech)

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue-3-42b883?style=flat-square&logo=vue.js&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-336791?style=flat-square&logo=postgresql&logoColor=white)
![JWT](https://img.shields.io/badge/Auth-JWT-d63aff?style=flat-square)
![Tests](https://img.shields.io/badge/Tests-Pest-f7971e?style=flat-square)

---

## 📸 Preview

| Dashboard | Kanban | Clientes |
|-----------|--------|----------|
| Stats, gráficos e tarefas recentes | Board com 4 colunas drag-and-drop | Tabela com busca e filtros |

---

## ✨ Funcionalidades

- 🔐 **Autenticação JWT** — Register, Login, Logout e Refresh token
- ✅ **Gestão de Tarefas** — Kanban board com 4 status: A Fazer, Em Progresso, Revisão e Concluído
- 👥 **Gestão de Clientes** — CRUD completo com busca, filtros e página de detalhe
- 📊 **Dashboard** — Métricas em tempo real, tarefas atrasadas e gráfico de produtividade
- 🌙 **Tema Claro/Escuro** — Alternância com persistência no localStorage
- 📚 **API Documentada** — OpenAPI 3.1 gerada automaticamente via Scramble (`/docs/api`)
- 🧪 **Testes Automatizados** — 16+ testes com Pest cobrindo toda a API

---

## 🏗️ Stack Tecnológica

### Backend
| Tecnologia | Versão | Uso |
|------------|--------|-----|
| Laravel | 11 | Framework principal |
| PHP | 8.3 | Linguagem |
| PostgreSQL | 17 | Banco de dados (Neon.tech) |
| JWT Auth | tymon/jwt-auth | Autenticação stateless |
| Scramble | dedoc/scramble | Documentação OpenAPI automática |
| Pest | 2.x | Testes modernos |
| Laravel Pint | — | Code style (PSR-12) |

### Frontend
| Tecnologia | Versão | Uso |
|------------|--------|-----|
| Vue | 3 | Framework reativo |
| Pinia | 2 | Gerenciamento de estado |
| Vue Router | 4 | Roteamento SPA |
| Axios | — | HTTP client com interceptors JWT |
| Vite | 5 | Build tool |

---

## 🚀 Como Rodar

### Pré-requisitos
- PHP 8.3+
- Node 20+
- Composer
- Conta no [Neon.tech](https://neon.tech) (PostgreSQL gratuito)

### 1. Clone o repositório
```bash
git clone https://github.com/rafaeldevgmail/TaskFlow-Kanban.git
```

### 2. Instale as dependências
```bash
composer install
npm install
```

### 3. Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configure o banco (Neon.tech)
Edite o `.env` com suas credenciais do Neon.tech:
```env
DB_CONNECTION=pgsql
DB_HOST=ep-xxxx-xxxx.us-east-2.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=sua_senha
DB_SSLMODE=require

VITE_API_URL=http://localhost:8000/api/v1
```

### 5. Rode as migrations e seed
```bash
php artisan migrate --seed
```
> Cria o usuário demo: `demo@taskflow.dev` / `password`

### 6. Inicie os servidores
```bash
# Terminal 1 — Laravel API
php artisan serve

# Terminal 2 — Vite (Vue)
npm run dev
```

Acesse: **http://localhost:5173**

---

## 🔌 Endpoints da API

Base URL: `/api/v1`

### Auth
```
POST   /auth/register    Criar conta
POST   /auth/login       Login (retorna JWT)
POST   /auth/logout      Logout
POST   /auth/refresh     Renovar token
GET    /auth/me          Usuário autenticado
```

### Tasks
```
GET    /tasks            Listar (filtros: status, priority, search)
POST   /tasks            Criar tarefa
GET    /tasks/{id}       Detalhes
PUT    /tasks/{id}       Atualizar
PATCH  /tasks/{id}/status  Atualizar apenas o status
DELETE /tasks/{id}       Excluir (soft delete)
```

### Clients
```
GET    /clients          Listar (filtros: status, search)
POST   /clients          Criar cliente
GET    /clients/{id}     Detalhes + tarefas vinculadas
PUT    /clients/{id}     Atualizar
DELETE /clients/{id}     Excluir (soft delete)
```

### Dashboard
```
GET    /dashboard        Stats, tarefas recentes e gráfico
```

> 📚 Documentação interativa completa em `/docs/api`

---

## 🧪 Testes

```bash
# Rodar todos os testes
php artisan test

# Com cobertura de código
php artisan test --coverage --min=80

# Testes em paralelo
php artisan test --parallel

# Filtrar por grupo
php artisan test --filter AuthTest
```

### Cobertura atual
| Módulo | Testes |
|--------|--------|
| Auth (register, login, logout) | ✅ 3 |
| Tasks (CRUD + status + auth) | ✅ 6 |
| Clients (CRUD + auth) | ✅ 5 |
| Dashboard | ✅ 1 |

---

## 🔄 CI/CD Pipeline

O pipeline GitHub Actions roda automaticamente em todo `push` e `pull_request`:

```
Push / PR
   │
   ├── 🧪 test      → PHP 8.3 + PostgreSQL 17 + Pest (cobertura mín. 80%)
   ├── 🎨 lint      → Laravel Pint (PSR-12)
   └── 🏗️ frontend  → Node 20 + Vite build
```

Merge bloqueado se qualquer job falhar.

---

## 📁 Estrutura do Projeto

```
TaskFlow-Kanban/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    # AuthController, TaskController, ClientController, DashboardController
│   │   └── Requests/           # Form Requests com validação
│   ├── Models/                 # User, Task, Client (com SoftDeletes)
│   └── Policies/               # TaskPolicy, ClientPolicy (autorização por dono)
├── database/
│   ├── migrations/             # clients e tasks com índices otimizados
│   ├── factories/              # ClientFactory, TaskFactory
│   └── seeders/                # DatabaseSeeder com dados demo
├── routes/
│   └── api.php                 # Rotas versionadas em /api/v1
├── tests/
│   └── Feature/CrmTest.php     # 16 testes de integração
├── resources/js/
│   ├── views/                  # DashboardView, TasksView, ClientsView, ClientDetailView
│   ├── stores/                 # Pinia: auth, tasks, clients
│   ├── router/                 # Vue Router com guards de autenticação
│   ├── services/api.js         # Axios com interceptors JWT
│   └── App.vue                 # Layout principal + tema claro/escuro
└── .github/workflows/
    └── ci.yml                  # Pipeline CI/CD completo
```

---

## 🔒 Segurança

- **JWT stateless** — tokens com expiração configurável, sem sessão no servidor
- **Policies Laravel** — cada usuário acessa apenas seus próprios recursos
- **Form Requests** — validação centralizada com mensagens de erro padronizadas
- **Soft Deletes** — dados nunca são removidos permanentemente
- **SSL obrigatório** — conexão com Neon.tech sempre criptografada
- **CORS configurado** — apenas origens permitidas acessam a API


