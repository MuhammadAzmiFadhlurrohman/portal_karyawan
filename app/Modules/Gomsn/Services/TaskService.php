<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskSubtask;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Create a task (§8.3).
     *
     * @param  array{title: string, description?: ?string, priority?: string, due_date?: ?string, tiket_id?: ?int}  $data
     * @param  list<string>  $subtasks
     */
    public function createTask(User $assigner, User $assignee, array $data, array $subtasks = []): Task
    {
        return DB::transaction(function () use ($assigner, $assignee, $data, $subtasks): Task {
            $task = Task::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assigner_id' => $assigner->id,
                'assignee_id' => $assignee->id,
                'status' => 'Pending Approval',
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
                'tiket_id' => $data['tiket_id'] ?? null,
            ]);

            foreach ($subtasks as $title) {
                if (! empty(trim($title))) {
                    TaskSubtask::query()->create([
                        'task_id' => $task->id,
                        'title' => trim($title),
                        'is_completed' => false,
                    ]);
                }
            }

            AuditLogger::record(
                action: 'task.create',
                auditable: $task,
                newValues: ['title' => $task->title, 'assignee' => $assignee->name],
                reason: "Penugasan kerja dibuat oleh {$assigner->name}",
                actorId: $assigner->id
            );

            return $task;
        });
    }

    /**
     * Update task status based on role rules (§8.3).
     */
    public function updateStatus(Task $task, User $user, string $newStatus): Task
    {
        $validStatuses = ['Pending Approval', 'In Progress', 'Completed', 'Rejected'];
        if (! in_array($newStatus, $validStatuses, true)) {
            throw new Exception("Status '{$newStatus}' tidak valid.");
        }

        $isAssigner = ($task->assigner_id === $user->id || $user->hasRole(['super_admin', 'manager_teknisi']));
        $isAssignee = ($task->assignee_id === $user->id);

        if (! $isAssigner && ! $isAssignee) {
            throw new Exception('Anda tidak memiliki hak akses untuk mengubah status penugasan ini.');
        }

        // Assigner approves or rejects
        if (in_array($newStatus, ['Rejected'], true) && ! $isAssigner) {
            throw new Exception('Hanya pemberi tugas yang dapat menolak tugas.');
        }

        $task->status = $newStatus;
        if ($newStatus === 'Completed') {
            $task->completed_at = Carbon::now();
        }
        $task->save();

        AuditLogger::record(
            action: 'task.status.update',
            auditable: $task,
            newValues: ['status' => $newStatus],
            reason: "Status tugas diubah ke {$newStatus} oleh {$user->name}",
            actorId: $user->id
        );

        return $task;
    }

    /**
     * Toggle subtask completion status (§8.3).
     */
    public function toggleSubtask(TaskSubtask $subtask, User $user): TaskSubtask
    {
        $subtask->is_completed = ! $subtask->is_completed;
        $subtask->save();

        return $subtask;
    }

    /**
     * Add comment to task (§8.3).
     */
    public function addComment(Task $task, User $user, string $comment, ?string $attachmentPath = null): TaskComment
    {
        return TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $comment,
            'attachment_path' => $attachmentPath,
        ]);
    }
}

