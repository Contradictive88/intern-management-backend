<?php

namespace Tests\Feature\ProjectTaskSubtaskController;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskSubtask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetSubtaskByIdTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $project_task;
    protected $project_task_subtask;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with known credentials and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create a project
        $this->project = Project::factory()->create();

        // Create some tasks for the project
        $this->project_task = ProjectTask::factory()->count(1)->create(['project_id' => $this->project->project_id]);

        // Create some subtasks for the project tasks
        $this->project_task_subtask = ProjectTaskSubtask::factory()->count(1)->create(['project_task_id' => $this->project_task->first()->project_task_id]);
    }

    protected function tearDown(): void
    {
        $this->user = null;
        $this->project = null;
        $this->project_task = null;
        $this->project_task_subtask = null;
        parent::tearDown();
    }

    public function test_returns_subtask_for_existing_project(): void
    {
        $response = $this->getJson(route(
            'projects.tasks.subtasks.show',
            [
                'projectId' => $this->project->project_id,
                'taskId' => $this->project_task->first()->project_task_id,
                'subtaskId' => $this->project_task_subtask->first()->project_task_subtask_id,
            ]
        ));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'project_task_subtask_id',
                    'project_task_id',
                    'project_task_subtask_name',
                    'project_task_subtask_description',
                    'project_task_subtask_progress',
                    'project_task_subtask_priority_level',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ]);
    }
}