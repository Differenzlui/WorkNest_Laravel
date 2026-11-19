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
            ->withCount(['members', 'tasks'])
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

        return view('groups.show', compact('group'));
    }

    // Delete the whole group (owner only)
    public function destroy(Request $request, Group $group): RedirectResponse
    {
        $this->mustBeOwner($request, $group);

        $group->delete();

        return redirect()->route('groups.index')->with('status', 'Group deleted.');
    }
}