<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GroupController extends Controller
{
    // The list of my groups
    public function index(Request $request): View
    {
        $groups = $request->user()->groups()
            ->withCount([
                'members',
                'tasks',
                'tasks as done_tasks_count' => fn ($query) => $query->where('progress', '>=', 100),
                'taskUpdates as pending_updates_count' => fn ($query) => $query->where('group_task_updates.status', 'pending'),
            ])
            ->withAvg('tasks', 'progress')
            ->with('owner')
            ->orderBy('groups.name')
            ->get();

        return view('groups.index', compact('groups'));
    }

    // Create a new group
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $group = DB::transaction(function () use ($request, $data) {
            $group = Group::create([
                'owner_id' => $request->user()->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // The person who creates the group is also a member of it
            $group->members()->attach($request->user()->id);

            return $group;
        });

        return redirect()->route('groups.show', $group)->with('status', 'Group created!');
    }

    // One group's page
    public function show(Request $request, Group $group): View
    {
        $this->mustBeMember($request, $group);

        $group->load(['owner', 'members']);

        // Soonest deadline first, tasks without a deadline last
        $tasks = $group->tasks()
            ->with(['assignee', 'latestUpdate'])
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->orderBy('id')
            ->get();

        // Whole-group progress: the average of every task's APPROVED progress
        $overall = $tasks->isNotEmpty() ? (int) round($tasks->avg('progress')) : 0;
        $doneCount = $tasks->where('progress', '>=', 100)->count();

        // How many updates are waiting for the leader
        $pendingCount = $tasks->filter(fn ($task) => $task->latestUpdate?->status === 'pending')->count();

        // Per-member progress, based on the tasks assigned to each person
        $memberStats = $group->members->mapWithKeys(function ($member) use ($tasks) {
            $mine = $tasks->where('assigned_to', $member->id);

            return [$member->id => [
                'total' => $mine->count(),
                'done' => $mine->where('progress', '>=', 100)->count(),
                'avg' => $mine->isNotEmpty() ? (int) round($mine->avg('progress')) : 0,
            ]];
        });

        return view('groups.show', compact('group', 'tasks', 'overall', 'doneCount', 'pendingCount', 'memberStats'));
    }

    // Delete the whole group (owner only)
    public function destroy(Request $request, Group $group): RedirectResponse
    {
        $this->mustBeOwner($request, $group);

        $group->delete();

        return redirect()->route('groups.index')->with('status', 'Group deleted.');
    }
}