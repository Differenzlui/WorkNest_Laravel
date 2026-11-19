<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupTask;
use App\Models\GroupTaskUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupTaskController extends Controller
{
    // Owner creates a task in the group
    public function store(Request $request, Group $group): RedirectResponse
    {
        $this->mustBeOwner($request, $group);

        $data = $request->validateWithBag('addTask', $this->rules($group));

        $group->tasks()->create($data + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Task added to the group!');
    }

    // Owner changes a task (name, assignee, priority, deadline...)
    public function update(Request $request, Group $group, GroupTask $task): RedirectResponse
    {
        $this->mustBeOwner($request, $group);
        $this->taskMustBelongTo($group, $task);

        $data = $request->validateWithBag('editTask', $this->rules($group));

        DB::transaction(function () use ($task, $data) {
            // If the task goes to someone else, the old person's waiting update no longer applies
            if ((string) ($data['assigned_to'] ?? '') !== (string) ($task->assigned_to ?? '')) {
                $task->updates()->where('status', 'pending')->delete();
            }

            $task->update($data);
        });

        return back()->with('status', 'Task updated!');
    }

    // Owner deletes a task
    public function destroy(Request $request, Group $group, GroupTask $task): RedirectResponse
    {
        $this->mustBeOwner($request, $group);
        $this->taskMustBelongTo($group, $task);

        $task->delete();

        return back()->with('status', 'Task deleted.');
    }

    // The assigned person reports their progress
    public function progress(Request $request, Group $group, GroupTask $task): RedirectResponse
    {
        $this->mustBeMember($request, $group);
        $this->taskMustBelongTo($group, $task);

        $user = $request->user();

        // Only the person this task is assigned to may report progress
        abort_unless($task->assigned_to === $user->id, 403);

        $data = $request->validateWithBag('progress_' . $task->id, [
            'progress' => ['required', 'integer', 'between:0,100'],
            'progress_note' => ['nullable', 'string', 'max:500'],
        ]);

        $note = $data['progress_note'] ?? null;

        // The leader has nobody above them, so the leader's own updates count right away
        if ($group->isOwnedBy($user)) {
            DB::transaction(function () use ($task, $user, $data, $note) {
                $task->updates()->where('status', 'pending')->delete();

                $task->updates()->create([
                    'user_id' => $user->id,
                    'progress' => $data['progress'],
                    'note' => $note,
                    'status' => 'approved',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                ]);

                $task->update([
                    'progress' => $data['progress'],
                    'progress_note' => $note,
                    'progress_updated_at' => now(),
                ]);
            });

            return back()->with(
                'status',
                $data['progress'] >= 100 ? 'Great job, the task is complete!' : 'Progress updated!'
            );
        }

        // Everyone else: save it as "pending". Sending again replaces the one that is waiting.
        $task->updates()->updateOrCreate(
            ['status' => 'pending'],
            ['user_id' => $user->id, 'progress' => $data['progress'], 'note' => $note]
        );

        return back()->with('status', 'Sent to the leader for approval. The bars change once it is approved.');
    }

    // Leader approves a waiting update: now it counts
    public function approve(Request $request, Group $group, GroupTask $task, GroupTaskUpdate $update): RedirectResponse
    {
        $this->mustBeOwner($request, $group);
        $this->taskMustBelongTo($group, $task);
        abort_unless($update->group_task_id === $task->id, 404);

        $done = $this->review($request, $task, $update, 'approved', null);

        return back()->with(
            'status',
            $done ? 'Progress approved. The bars are updated.' : 'That update was already reviewed or is no longer valid.'
        );
    }

    // Leader rejects a waiting update (with an optional reason)
    public function reject(Request $request, Group $group, GroupTask $task, GroupTaskUpdate $update): RedirectResponse
    {
        $this->mustBeOwner($request, $group);
        $this->taskMustBelongTo($group, $task);
        abort_unless($update->group_task_id === $task->id, 404);

        $data = $request->validateWithBag('review_' . $update->id, [
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $done = $this->review($request, $task, $update, 'rejected', $data['review_note'] ?? null);

        return back()->with(
            'status',
            $done ? 'Update rejected. The member can send a new one.' : 'That update was already reviewed or is no longer valid.'
        );
    }

    // Approve or reject, safely (a second click can never count twice)
    private function review(Request $request, GroupTask $task, GroupTaskUpdate $update, string $status, ?string $reviewNote): bool
    {
        return DB::transaction(function () use ($request, $task, $update, $status, $reviewNote) {
            $fresh = GroupTaskUpdate::whereKey($update->id)->lockForUpdate()->first();

            // Already reviewed, or no longer valid (for example the task was given to someone else)
            if (! $fresh || $fresh->status !== 'pending' || $fresh->user_id !== $task->assigned_to) {
                return false;
            }

            $fresh->update([
                'status' => $status,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $status === 'rejected' ? $reviewNote : null,
            ]);

            // Only an approval changes the real progress of the task
            if ($status === 'approved') {
                $task->update([
                    'progress' => $fresh->progress,
                    'progress_note' => $fresh->note,
                    'progress_updated_at' => now(),
                ]);
            }

            return true;
        });
    }

    // The form rules, shared by "add" and "edit"
    private function rules(Group $group): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            // The task may only be given to someone who is in THIS group
            'assigned_to' => [
                'nullable',
                Rule::exists('group_user', 'user_id')->where('group_id', $group->id),
            ],
            'priority' => ['required', 'in:low,medium,high'],
            'deadline' => ['nullable', 'date'],
        ];
    }

    // The address contains a group AND a task, so make sure they match
    private function taskMustBelongTo(Group $group, GroupTask $task): void
    {
        abort_unless($task->group_id === $group->id, 404);
    }
}