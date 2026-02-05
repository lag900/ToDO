<?php

namespace Tests\Feature;

use App\Jobs\NotifyWorkspaceMembers;
use App\Models\Board;
use App\Models\Plan;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $workspace;
    protected $creator;
    protected $assignee;
    protected $otherMember;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Users (Factory exists)
        $this->creator = User::factory()->create(['email' => 'creator@test.com']);
        $this->assignee = User::factory()->create(['email' => 'assignee@test.com']);
        $this->otherMember = User::factory()->create(['email' => 'other@test.com']);

        // Setup Workspace (Manual)
        $this->workspace = Workspace::create([
            'name' => 'Test Workspace',
            'type' => 'company',
            'intent' => 'manage projects',
            'owner_id' => $this->creator->id
        ]);

        $this->workspace->members()->attach([
            $this->creator->id => ['role' => 'admin'],
            $this->assignee->id => ['role' => 'member'],
            $this->otherMember->id => ['role' => 'member'],
        ]);

        // Setup Task Infrastructure (Manual)
        $plan = Plan::create([
            'name' => 'Test Plan',
            'workspace_id' => $this->workspace->id,
            'description' => 'Desc',
            'user_id' => $this->creator->id
        ]);
        
        $board = Board::create([
            'name' => 'Test Board',
            'plan_id' => $plan->id,
            'user_id' => $this->creator->id
        ]);
    }

    /** @test */
    public function it_notifies_assignee_when_task_created()
    {
        Mail::fake();
        $this->actingAs($this->creator);

        $task = Task::create([
            'title' => 'New Task',
            'created_by' => $this->creator->id,
            'assigned_to' => $this->assignee->id,
            'board_id' => Board::first()->id,
            'status' => 'todo'
        ]);

        // TaskObserver already dispatches the job, but we are testing the logic manually in the handle() 
        // to verify shouldNotify works as expected. 
        // We can just verify if the job WAS dispatched or just check the handle directly.
        // For the sake of this test, we verify the RESULT after the job handles.
        
        (new NotifyWorkspaceMembers($task, 'task_created', [], $this->creator))->handle();

        // Assignee should receive email
        Mail::assertSent(\App\Mail\TaskCreatedNotification::class, function ($mail) {
            return $mail->hasTo('assignee@test.com');
        });

        // Creator (Actor) should NOT receive email (default logic)
        Mail::assertNotSent(\App\Mail\TaskCreatedNotification::class, function ($mail) {
            return $mail->hasTo('creator@test.com');
        });
    }

    /** @test */
    public function it_respects_exclude_self_setting()
    {
        Mail::fake();

        $this->assignee->update(['notification_settings' => [
            'email_enabled' => true,
            'types' => ['task_updated'],
            'exclude_self' => true
        ]]);

        $task = Task::create([
            'title' => 'Task',
            'created_by' => $this->creator->id,
            'assigned_to' => $this->assignee->id,
            'board_id' => Board::first()->id,
            'status' => 'todo'
        ]);

        // Assignee updates their own task
        (new NotifyWorkspaceMembers($task, 'task_updated', [], $this->assignee))->handle();

        // Assignee should NOT receive email
        Mail::assertNotSent(\App\Mail\TaskUpdatedNotification::class, function ($mail) {
            return $mail->hasTo('assignee@test.com');
        });
        
        // Creator should receive email
        Mail::assertSent(\App\Mail\TaskUpdatedNotification::class, function ($mail) {
            return $mail->hasTo('creator@test.com');
        });
    }

    /** @test */
    public function it_respects_exclude_created_by_me_setting()
    {
        Mail::fake();

        $this->creator->update(['notification_settings' => [
            'email_enabled' => true,
            'types' => ['task_completed'],
            'exclude_tasks_created_by_me' => true 
        ]]);

        $task = Task::create([
            'title' => 'Task',
            'created_by' => $this->creator->id,
            'assigned_to' => $this->assignee->id,
            'board_id' => Board::first()->id,
            'status' => 'todo'
        ]);

        // Assignee completes the task
        (new NotifyWorkspaceMembers($task, 'task_completed', [], $this->assignee))->handle();

        // Creator opted out
        Mail::assertNotSent(\App\Mail\TaskCompletedNotification::class, function ($mail) {
            return $mail->hasTo('creator@test.com');
        });
    }
}

