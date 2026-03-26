<?php

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
/*
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'api');
});

// ── Auth ──────────────────────────────────────────────────────────

it('registers a new user', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertCreated()->assertJsonStructure(['user', 'token']);
});

it('logs in with valid credentials', function () {
    $this->postJson('/api/v1/auth/login', [
        'email'    => $this->user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['user', 'token', 'expires_in']);
});

it('rejects invalid credentials', function () {
    $this->postJson('/api/v1/auth/login', [
        'email'    => $this->user->email,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

// ── Tasks ─────────────────────────────────────────────────────────

it('lists only own tasks', function () {
    Task::factory(3)->create(['user_id' => $this->user->id]);
    Task::factory(2)->create(); // other user

    $this->getJson('/api/v1/tasks')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates a task', function () {
    $this->postJson('/api/v1/tasks', [
        'title'    => 'Minha tarefa',
        'priority' => 'high',
        'status'   => 'todo',
    ])->assertCreated()->assertJsonFragment(['title' => 'Minha tarefa']);
});

it('updates a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $this->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Novo título'])
        ->assertOk()
        ->assertJsonFragment(['title' => 'Novo título']);
});

it('updates task status', function () {
    $task = Task::factory()->todo()->create(['user_id' => $this->user->id]);

    $this->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'done'])
        ->assertOk()
        ->assertJsonFragment(['status' => 'done']);
});

it('deletes a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);
    $this->deleteJson("/api/v1/tasks/{$task->id}")->assertNoContent();
    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

it('cannot access another users task', function () {
    $task = Task::factory()->create(); // other user
    $this->getJson("/api/v1/tasks/{$task->id}")->assertForbidden();
});

// ── Clients ───────────────────────────────────────────────────────

it('lists only own clients', function () {
    Client::factory(4)->create(['user_id' => $this->user->id]);
    Client::factory(2)->create();

    $this->getJson('/api/v1/clients')
        ->assertOk()
        ->assertJsonCount(4, 'data');
});

it('creates a client', function () {
    $this->postJson('/api/v1/clients', [
        'name'    => 'Empresa XPTO',
        'email'   => 'contato@xpto.com',
        'status'  => 'prospect',
    ])->assertCreated()->assertJsonFragment(['name' => 'Empresa XPTO']);
});

it('updates a client', function () {
    $client = Client::factory()->create(['user_id' => $this->user->id]);

    $this->putJson("/api/v1/clients/{$client->id}", ['status' => 'active'])
        ->assertOk()
        ->assertJsonFragment(['status' => 'active']);
});

it('soft deletes a client', function () {
    $client = Client::factory()->create(['user_id' => $this->user->id]);
    $this->deleteJson("/api/v1/clients/{$client->id}")->assertNoContent();
    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

it('cannot access another users client', function () {
    $client = Client::factory()->create();
    $this->getJson("/api/v1/clients/{$client->id}")->assertForbidden();
});

// ── Dashboard ─────────────────────────────────────────────────────

it('returns dashboard data', function () {
    Task::factory(5)->create(['user_id' => $this->user->id]);
    Client::factory(3)->create(['user_id' => $this->user->id]);

    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonStructure(['stats', 'recent_tasks', 'overdue_tasks', 'completed_by_day']);
});*/