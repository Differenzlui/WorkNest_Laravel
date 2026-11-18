<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    // Home page: all of my tasks, with search
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $tasks = $request->user()->tasks()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->orderBy('is_done')
            ->orderBy('deadline')
            ->get();

        return view('tasks.index', compact('tasks', 'search'));
    }

    // "Tasks due on ..." page for one day
    public function byDate(Request $request, string $date): View
    {
        // Make sure it is a real date like 2026-11-25 (not 2026-02-31 or abc)
        try {
            $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $e) {
            abort(404);
        }

        abort_unless($day->format('Y-m-d') === $date, 404);

        $search = trim((string) $request->query('search', ''));

        $tasks = $request->user()->tasks()
            ->whereDate('deadline', $date)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->orderBy('is_done')
            ->orderBy('id')
            ->get();

        return view('tasks.by-date', compact('tasks', 'day', 'date', 'search'));
    }

    // Save a new task
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $request->user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('status', 'Task added!');
    }

    // Save changes to a task
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->ownTask($request, $task);

        $data = $request->validate($this->rules());

        $task->update($data);

        return back()->with('status', 'Task updated!');
    }

    // Tick / untick the circle
    public function toggle(Request $request, Task $task): RedirectResponse
    {
        $this->ownTask($request, $task);

        $task->update(['is_done' => ! $task->is_done]);

        return back();
    }

    // Delete a task
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->ownTask($request, $task);

        $task->delete();

        return back()->with('status', 'Task deleted.');
    }

    // The form rules, shared by "add" and "edit"
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high'],
            'deadline' => ['required', 'date'],
        ];
    }

    // Stop people from touching tasks that belong to someone else
    private function ownTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 404);
    }
}