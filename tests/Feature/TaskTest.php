<?php
use App\Models\User;
use App\Models\Task;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'api');
});

it('can list tasks', function () {
    Task::factory(3)->create(['user_id' => $this->user->id]);

    $this->getJson('/api/v1/tasks')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create a task', function () {
    $payload = ['title' => 'Nova Tarefa', 'priority' => 'high', 'status' => 'todo'];

    $this->postJson('/api/v1/tasks', $payload)
        ->assertCreated()
        ->assertJsonFragment(['title' => 'Nova Tarefa']);
});

it('can update task status', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id, 'status' => 'todo']);

    $this->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'done'])
        ->assertOk()
        ->assertJsonFragment(['status' => 'done']);
});

it('cannot access other users tasks', function () {
    $otherUser = User::factory()->create();
    $task      = Task::factory()->create(['user_id' => $otherUser->id]);

    $this->getJson("/api/v1/tasks/{$task->id}")->assertForbidden();
});