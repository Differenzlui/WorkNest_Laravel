<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

abstract class Controller
{
    // Only people inside the group may continue (outsiders get a 404)
    protected function mustBeMember(Request $request, Group $group): void
    {
        abort_unless($group->hasMember($request->user()), 404);
    }

    // Only the group's owner may continue (other members get a 403)
    protected function mustBeOwner(Request $request, Group $group): void
    {
        $this->mustBeMember($request, $group);

        abort_unless($group->isOwnedBy($request->user()), 403);
    }
}