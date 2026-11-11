<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskSubtask;
use App\Models\User;
use App\Modules\Gomsn\Services\TaskService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
    ) {}

    /**
     * Display list of tasks (§8.3).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Task::query()->with(['assigner', 'assignee', 'subtasks', 'comments.user']);

        // Filter based on role or tab
        $tab = $request->input('tab', 'assigned_to_me');
        if ($tab === 'created_by_me') {
            $query->where('assigner_id', $user->id);
        } elseif ($tab === 'assigned_to_me') {
            $query->where('assignee_id', $user->id);
        } else {
            if (! $user->hasRole(['super_admin', 'manager_teknisi', 'atasan'])) {
                $query->where(function ($q) use ($user) {
                    $q->where('assignee_id', $user->id)->orWhere('assigner_id', $user->id);
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::query()->where('is_active', true)->orderBy('name')->get();

        return view('gomsn.tasks.index', compact('tasks', 'users', 'tab'));
    }

    /**
     * Store new task (§8.3).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assignee_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'tiket_id' => ['nullable', 'exists:tiket,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*' => ['nullable', 'string', 'max:255'],
        ]);

        $assignee = User::findOrFail($validated['assignee_id']);
        $subtasks = array_filter($validated['subtasks'] ?? []);

        try {
            $task = $this->taskService->createTask(
                assigner: $request->user(),
                assignee: $assignee,
                data: $validated,
                subtasks: $subtasks
            );

            return redirect()->route('gomsn.tasks.index')
                ->with('success', "Penugasan '{$task->title}' berhasil dibuat untuk {$assignee->name}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update task status (§8.3).
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pending Approval,In Progress,Completed,Rejected'],
        ]);

        try {
            $this->taskService->updateStatus($task, $request->user(), $validated['status']);

            return redirect()->route('gomsn.tasks.index')
                ->with('success', "Status penugasan diubah menjadi {$validated['status']}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle subtask completion status (§8.3).
     */
    public function toggleSubtask(Request $request, TaskSubtask $subtask): JsonResponse|RedirectResponse
    {
        $this->taskService->toggleSubtask($subtask, $request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $subtask->is_completed,
            ]);
        }

        return back()->with('success', 'Status subtask diperbarui.');
    }

    /**
     * Add comment to task (§8.3).
     */
    public function addComment(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $this->taskService->addComment($task, $request->user(), $validated['comment']);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}

