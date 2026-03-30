<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Index — pagination
    // -------------------------------------------------------------------------

    public function test_tasks_index_returns_paginated_list(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                     'links',
                 ]);

        // Default page size is 10
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_tasks_index_filters_by_status(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'todo']);
        Task::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'done']);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/tasks?status=todo');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    public function test_tasks_index_searches_by_title(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Buy groceries']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Write report']);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/tasks?search=groceries');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    public function test_tasks_index_is_scoped_to_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Task::factory()->count(4)->create(['user_id' => $userA->id]);
        Task::factory()->count(3)->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA, 'sanctum')
                         ->getJson('/api/tasks');

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('meta.total'));
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_task_and_returns_201(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tasks', [
                             'title'    => 'My new task',
                             'priority' => 'high',
                             'status'   => 'todo',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'My new task')
                 ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('tasks', [
            'title'   => 'My new task',
            'user_id' => $user->id,
        ]);
    }

    public function test_store_returns_422_when_title_is_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/tasks', ['priority' => 'low'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['title']);
    }

    public function test_store_returns_422_for_invalid_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/tasks', [
                 'title'  => 'Task',
                 'status' => 'invalid-status',
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['status']);
    }

    public function test_store_returns_422_for_past_due_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/tasks', [
                 'title'    => 'Task',
                 'due_date' => '2020-01-01',
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['due_date']);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_200_for_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
             ->getJson("/api/tasks/{$task->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $task->id);
    }

    public function test_show_returns_403_for_another_user(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $task   = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
             ->getJson("/api/tasks/{$task->id}")
             ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_task_and_returns_200(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Old title']);

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/tasks/{$task->id}", [
                             'title'  => 'New title',
                             'status' => 'in_progress',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.title', 'New title')
                 ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_update_returns_403_for_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
             ->putJson("/api/tasks/{$task->id}", ['title' => 'Hack'])
             ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_task_and_returns_204(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
             ->deleteJson("/api/tasks/{$task->id}")
             ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns_403_for_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
             ->deleteJson("/api/tasks/{$task->id}")
             ->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    // -------------------------------------------------------------------------
    // Update Status
    // -------------------------------------------------------------------------

    public function test_update_status_changes_only_status(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'todo']);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson("/api/tasks/{$task->id}/status", [
                             'status' => 'done',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'done');
    }

    public function test_update_status_returns_422_for_invalid_value(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
             ->patchJson("/api/tasks/{$task->id}/status", [
                 'status' => 'completed',
             ])
             ->assertStatus(422);
    }
}
