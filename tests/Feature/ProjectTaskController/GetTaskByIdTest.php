<?php

namespace Tests\Feature\ProjectTaskController;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetTaskByIdTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with known credentials and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    protected function tearDown(): void
    {
        $this->user = null;
        parent::tearDown();
    }

    public function test_returns_specific_task()
    {
        // Create a project and a task for that project
        $project = Project::factory()->create();
        $task = ProjectTask::factory()->create(['project_id' => $project->project_id]);

        // Make a GET request to fetch the task
        $response = $this->getJson(route('projects.tasks.getTaskById', ['projectId' => $project->project_id, 'id' => $task->project_task_id]));

        // Assert response status is 200 and structure of returned JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'project_task_id',
                    'project_id',
                    'project_task_name',
                    'project_task_description',
                    'project_task_progress',
                    'project_task_priority_level',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ]);
    }

    public function test_returns_not_found_if_project_does_not_exist()
    {
        // Make a GET request with non-existent project ID
        $response = $this->getJson(route('projects.tasks.getTaskById', ['projectId' => 999, 'id' => 1]));

        // Assert response status is 404
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Project not found.',
            ]);
    }

    public function test_returns_not_found_if_task_does_not_exist()
    {
        // Create a project
        $project = Project::factory()->create();

        // Make a GET request with existing project ID but non-existent task ID
        $response = $this->getJson(route('projects.tasks.getTaskById', ['projectId' => $project->project_id, 'id' => 'invalidId']));

        // Assert response status is 404
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Task not found.',
            ]);
    }
}