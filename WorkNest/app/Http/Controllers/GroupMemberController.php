<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupMemberController extends Controller
{
    // Owner adds someone by email
    public function store(Request $request, Group $group): RedirectResponse
    {
        $this->mustBeOwner($request, $group);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));

        // Look the email up ignoring capital letters
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'No WorkNest account uses that email yet. Ask them to create an account (or sign in with Microsoft once), then add them again.']);
        }

        if ($group->hasMember($user)) {
            return back()
                ->withInput()
                ->withErrors(['email' => $user->name . ' is already in this group.']);
        }

        $group->members()->attach($user->id);

        return back()->with('status', $user->name . ' was added to the group.');
    }

    // Owner removes a member
    public function destroy(Request $request, Group $group, User $user): RedirectResponse
    {
        $this->mustBeOwner($request, $group);

        abort_unless($group->hasMember($user), 404);

        if ($group->isOwnedBy($user)) {
            return back()->withErrors(['members' => 'The owner cannot be removed from the group.']);
        }

        $this->removeFromGroup($group, $user);

        return back()->with('status', $user->name . ' was removed from the group.');
    }

    // A member leaves by themselves
    public function leave(Request $request, Group $group): RedirectResponse
    {
        $this->mustBeMember($request, $group);

        $user = $request->user();

        if ($group->isOwnedBy($user)) {
            return back()->withErrors(['members' => 'The owner cannot leave the group. Delete the group instead.']);
        }

        $this->removeFromGroup($group, $user);

        return redirect()->route('groups.index')->with('status', 'You left the group.');
    }

    // Take someone out of the group. Their tasks stay, but become "unassigned".
    private function removeFromGroup(Group $group, User $user): void
    {
        DB::transaction(function () use ($group, $user) {
            $group->tasks()->where('assigned_to', $user->id)->update(['assigned_to' => null]);
            $group->members()->detach($user->id);
        });
    }
}