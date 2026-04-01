<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * Display a paginated listing of the authenticated user's tasks.
     *
     * Query params:
     *   - status       (todo|in_progress|done)
     *   - category_id  (integer)
     *   - search       (string, matches title)
     *   - overdue      (boolean-ish)
     *   - page         (integer)
     */
    public function index(Request $request)
    {
        $query = Task::forUser($request->user())
            ->with('category')
            ->latest();

        if ($request->status === 'overdue') {
            $query
                ->whereDate('due_date', '<', Carbon::today())
                ->where('status', '!=', 'done');
        }else if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where('title', 'like', $search);
        }        

        $tasks = $query->paginate(10)->withQueryString();

        return new TaskCollection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->tasks()->create($request->validated());

        $task->load('category');

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $task->load('category');

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        $task->load('category');

        return new TaskResource($task);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent(); // 204
    }

    /**
     * Update only the status of a task (PATCH /tasks/{task}/status).
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => ['required', 'in:todo,in_progress,done'],
        ]);

        $task->update(['status' => $request->status]);

        $task->load('category');

        return new TaskResource($task);
    }
}
