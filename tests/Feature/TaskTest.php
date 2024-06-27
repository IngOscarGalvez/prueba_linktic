<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        return $token;
    }

    public function test_create_task()
    {
        $token = $this->authenticate();

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'due_date' => '2023-01-01',
        ], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at']);
    }

    public function test_get_all_tasks()
    {
        $token = $this->authenticate();

        Task::factory()->count(5)->create();

        $response = $this->getJson('/api/tasks', ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_get_task_by_id()
    {
        $token = $this->authenticate();

        $task = Task::factory()->create();

        $response = $this->getJson('/api/tasks/' . $task->id, ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at']);
    }

    public function test_update_task()
    {
        $token = $this->authenticate();

        $task = Task::factory()->create();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'status' => 'in_progress',
            'due_date' => '2024-01-01',
        ], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Task', 'status' => 'in_progress']);
    }

    public function test_delete_task()
    {
        $token = $this->authenticate();

        $task = Task::factory()->create();

        $response = $this->deleteJson('/api/tasks/' . $task->id, [], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_filter_tasks_by_status()
    {
        $token = $this->authenticate();

        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/tasks?status=pending', ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_filter_tasks_by_due_date()
    {
        $token = $this->authenticate();

        Task::factory()->create(['due_date' => '2023-01-01']);
        Task::factory()->create(['due_date' => '2023-12-31']);

        $response = $this->getJson('/api/tasks?due_date=2023-01-01', ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }
}
