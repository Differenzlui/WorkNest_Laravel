<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high'],
            'deadline' => ['required', 'date'],
        ]);

        $request->user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('status', 'Task added!');
    }
}