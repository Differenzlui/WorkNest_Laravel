@extends('layouts.app')

@section('title', $group->name . ' - WorkNest')

@section('content')

    @php
        $isOwner = $group->owner_id === auth()->id();
        $members = $group->members->sortBy(fn ($m) => [$m->id === $group->owner_id ? 0 : 1, strtolower($m->name)]);
        $addOld = $errors->addTask->any();
    @endphp

    {{-- Messages --}}
    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-leaf px-5 py-3 font-bold">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('members'))
        <div class="mb-4 rounded-2xl bg-red-100 px-5 py-3 text-red-800">
            {{ $errors->first('members') }}
        </div>
    @endif

    @if ($errors->editTask->any())
        <div class="mb-4 rounded-2xl bg-red-100 px-5 py-3 text-red-800">
            <p class="font-bold">Could not save your changes:</p>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->editTask->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Leader: updates are waiting --}}
    @if ($isOwner && $pendingCount > 0)
        <div class="mb-4 rounded-2xl bg-amber-100 px-5 py-3 text-amber-900">
            <strong>{{ $pendingCount }}</strong>
            progress {{ $pendingCount === 1 ? 'update is' : 'updates are' }} waiting for your approval.
            Look for the tasks marked "Waiting for leader approval".
        </div>
    @endif

    {{-- Title + Back --}}
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="truncate text-2xl font-bold sm:text-3xl">{{ $group->name }}</h1>
            @if ($group->description)
                <p class="mt-1 opacity-80">{{ $group->description }}</p>
            @endif
        </div>

        <a href="{{ route('groups.index') }}"
           class="shrink-0 rounded-xl bg-bark px-5 py-2 font-bold text-cream hover:opacity-90">
            &lsaquo; Back
        </a>
    </div>

    {{-- Whole-group progress (approved progress only) --}}
    @if ($tasks->isNotEmpty())
        <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold">Group progress</h2>
                <p class="text-sm font-bold">
                    {{ $doneCount }} of {{ $tasks->count() }} {{ $tasks->count() === 1 ? 'task' : 'tasks' }} complete
                </p>
            </div>
            <div class="mt-3">
                <x-progress-bar :value="$overall" :big="true" />
            </div>
        </div>
    @endif

    {{-- Tasks --}}
    <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">Tasks ({{ $tasks->count() }})</h2>

            @if ($isOwner)
                <button type="button" id="open-add-task"
                        class="shrink-0 rounded-full bg-bark px-5 py-2 font-bold text-cream hover:opacity-90">
                    + Add task
                </button>
            @endif
        </div>

        @if ($tasks->isEmpty())
            <p class="py-8 text-center opacity-70">
                No tasks yet.@if ($isOwner) Click "+ Add task" to create the first one.@endif
            </p>
        @else
            <ul class="mt-4 divide-y-2 divide-bark/10">
                @foreach ($tasks as $task)
                    @php
                        $priorityColor = match ($task->priority) {
                            'high' => 'text-red-700',
                            'low' => 'text-green-700',
                            default => 'text-yellow-700',
                        };
                        $overdue = $task->deadline && $task->progress < 100 && $task->deadline->lt(today());

                        // The newest progress report of this task
                        $latest = $task->latestUpdate;
                        $pending = $latest && $latest->status === 'pending' ? $latest : null;
                        $rejected = $latest
                            && $latest->status === 'rejected'
                            && $latest->user_id === auth()->id()
                            && $task->assigned_to === auth()->id()
                                ? $latest : null;
                    @endphp

                    <li class="py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold">{{ $task->name }}</p>

                                @if ($task->description)
                                    <p class="mt-0.5 whitespace-pre-line text-sm opacity-80">{{ $task->description }}</p>
                                @endif

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                    <span class="font-bold {{ $priorityColor }}">{{ ucfirst($task->priority) }} priority</span>

                                    @if ($task->assignee)
                                        <span>
                                            Assigned to <strong>{{ $task->assignee->name }}</strong>@if ($task->assigned_to === auth()->id()) (you)@endif
                                        </span>
                                    @else
                                        <span class="opacity-60">Unassigned</span>
                                    @endif

                                    @if ($task->deadline)
                                        <span class="{{ $overdue ? 'font-bold text-red-700' : '' }}">
                                            Due {{ $task->deadline->format('M j, Y') }}@if ($overdue) (overdue)@endif
                                        </span>
                                    @else
                                        <span class="opacity-60">No deadline</span>
                                    @endif
                                </div>
                            </div>

                            @if ($isOwner)
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" title="Edit task" aria-label="Edit task"
                                            class="edit-task-btn rounded p-1 hover:bg-leaf"
                                            data-id="{{ $task->id }}"
                                            data-name="{{ $task->name }}"
                                            data-description="{{ $task->description }}"
                                            data-assigned="{{ $task->assigned_to }}"
                                            data-priority="{{ $task->priority }}"
                                            data-deadline="{{ $task->deadline?->toDateString() }}">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('groups.tasks.destroy', [$group, $task]) }}"
                                          onsubmit="return confirm('Delete this task? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete task" aria-label="Delete task"
                                                class="rounded p-1 hover:bg-red-100">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6"/>
                                                <path d="M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Approved progress: the bar everyone can trust --}}
                        <div class="mt-3">
                            <x-progress-bar :value="$task->progress" />

                            @if ($task->progress_note)
                                <p class="mt-1 whitespace-pre-line text-sm italic opacity-80">&ldquo;{{ $task->progress_note }}&rdquo;</p>
                            @endif

                            @if ($task->progress_updated_at)
                                <p class="mt-0.5 text-xs opacity-60">Last approved {{ $task->progress_updated_at->diffForHumans() }}</p>
                            @endif
                        </div>

                        {{-- An update is waiting for the leader --}}
                        @if ($pending)
                            <div class="mt-3 rounded-2xl bg-amber-100 px-4 py-3 text-sm text-amber-900">
                                <p class="font-bold">Waiting for leader approval: {{ $pending->progress }}%</p>

                                @if ($pending->note)
                                    <p class="mt-0.5 whitespace-pre-line italic">&ldquo;{{ $pending->note }}&rdquo;</p>
                                @endif

                                <p class="mt-0.5 text-xs opacity-70">
                                    Sent by {{ $task->assignee?->name }} {{ $pending->updated_at->diffForHumans() }}
                                </p>

                                {{-- Only the leader sees the buttons --}}
                                @if ($isOwner)
                                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-start">
                                        <form method="POST"
                                              action="{{ route('groups.tasks.approve', [$group, $task, $pending]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-full bg-bark px-5 py-1.5 font-bold text-cream hover:opacity-90">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('groups.tasks.reject', [$group, $task, $pending]) }}"
                                              class="flex flex-1 flex-col gap-2 sm:flex-row">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="review_note" maxlength="500"
                                                   placeholder="Reason (optional)"
                                                   class="w-full rounded-full bg-white px-4 py-1.5 outline-none focus:ring-2 focus:ring-leaf">
                                            <button type="submit"
                                                    class="shrink-0 rounded-full bg-red-700 px-5 py-1.5 font-bold text-white hover:opacity-90">
                                                Reject
                                            </button>
                                        </form>
                                    </div>

                                    @error('review_note', 'review_' . $pending->id)
                                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endif

                        {{-- The leader said no to the assigned person's last update --}}
                        @if ($rejected)
                            <div class="mt-3 rounded-2xl bg-red-100 px-4 py-3 text-sm text-red-800">
                                <p class="font-bold">Your update of {{ $rejected->progress }}% was not approved.</p>

                                @if ($rejected->review_note)
                                    <p class="mt-0.5 whitespace-pre-line italic">&ldquo;{{ $rejected->review_note }}&rdquo;</p>
                                @endif

                                <p class="mt-0.5 text-xs opacity-70">You can send a new one below.</p>
                            </div>
                        @endif

                        {{-- Only the person this task is assigned to sees this form --}}
                        @if ($task->assigned_to === auth()->id())
                            @php
                                $bag = 'progress_' . $task->id;
                                $hasError = $errors->getBag($bag)->any();
                                $baseValue = $pending ? $pending->progress : $task->progress;
                                $baseNote = $pending ? $pending->note : null;
                                $sliderValue = $hasError ? (int) old('progress', $baseValue) : $baseValue;
                                $noteValue = $hasError ? old('progress_note', $baseNote) : $baseNote;
                            @endphp

                            <details class="mt-3 rounded-2xl bg-white/60 px-4 py-3" @if ($hasError) open @endif>
                                <summary class="cursor-pointer font-bold">
                                    {{ $isOwner ? 'Update my progress' : ($pending ? 'Change what I sent' : 'Send my progress') }}
                                </summary>

                                <form method="POST" action="{{ route('groups.tasks.progress', [$group, $task]) }}"
                                      class="mt-3 space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    @unless ($isOwner)
                                        <p class="text-sm opacity-80">
                                            Your leader reviews this first. The bars change only after it is approved.
                                        </p>
                                    @endunless

                                    <div class="flex items-center gap-3">
                                        <input type="range" name="progress" min="0" max="100" step="1"
                                               value="{{ $sliderValue }}"
                                               aria-label="Progress percentage"
                                               oninput="this.nextElementSibling.textContent = this.value + '%'"
                                               class="w-full accent-[#3E9B4F]">
                                        <output class="w-12 shrink-0 text-right font-bold">{{ $sliderValue }}%</output>
                                    </div>

                                    <input type="text" name="progress_note" value="{{ $noteValue }}" maxlength="500"
                                           placeholder="What did you finish? (optional)"
                                           class="w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">

                                    @error('progress', $bag)
                                        <p class="px-2 text-sm text-red-700">{{ $message }}</p>
                                    @enderror
                                    @error('progress_note', $bag)
                                        <p class="px-2 text-sm text-red-700">{{ $message }}</p>
                                    @enderror

                                    <button type="submit"
                                            class="rounded-full bg-bark px-6 py-2 font-bold text-cream hover:opacity-90">
                                        {{ $isOwner ? 'Save progress' : ($pending ? 'Replace what I sent' : 'Send for approval') }}
                                    </button>
                                </form>
                            </details>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Members --}}
    <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
        <h2 class="text-lg font-bold">Members ({{ $members->count() }})</h2>

        {{-- Add member (owner only) --}}
        @if ($isOwner)
            <form method="POST" action="{{ route('groups.members.store', $group) }}"
                  class="mt-4 flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="Add a member by email"
                       class="w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                <button type="submit"
                        class="shrink-0 rounded-full bg-bark px-6 py-2 font-bold text-cream hover:opacity-90">
                    Add member
                </button>
            </form>
            @error('email')
                <p class="mt-2 px-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        @endif

        <ul class="mt-4 divide-y-2 divide-bark/10">
            @foreach ($members as $member)
                @php $stat = $memberStats[$member->id]; @endphp

                <li class="py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-bold">
                                {{ $member->name }}
                                @if ($member->id === auth()->id())
                                    <span class="text-xs font-normal opacity-60">(you)</span>
                                @endif
                            </p>
                            <p class="truncate text-sm opacity-70">{{ $member->email }}</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($member->id === $group->owner_id)
                                <span class="rounded-full bg-leaf px-3 py-0.5 text-xs font-bold">Owner</span>
                            @elseif ($isOwner)
                                <form method="POST" action="{{ route('groups.members.destroy', [$group, $member]) }}"
                                      onsubmit="return confirm('Remove this member from the group?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-full px-3 py-1 text-sm font-bold text-red-700 hover:bg-red-100">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- This member's approved progress --}}
                    <div class="mt-2">
                        @if ($stat['total'] > 0)
                            <p class="mb-1 text-xs opacity-80">
                                {{ $stat['done'] }} of {{ $stat['total'] }} {{ $stat['total'] === 1 ? 'task' : 'tasks' }} complete
                            </p>
                            <x-progress-bar :value="$stat['avg']" />
                        @else
                            <p class="text-xs opacity-60">No tasks assigned</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Leave / Delete --}}
    <div class="mt-6 flex justify-end">
        @if ($isOwner)
            <form method="POST" action="{{ route('groups.destroy', $group) }}"
                  onsubmit="return confirm('Delete this group and all of its tasks? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-full bg-red-700 px-6 py-2 font-bold text-white hover:opacity-90">
                    Delete group
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('groups.leave', $group) }}"
                  onsubmit="return confirm('Leave this group?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-full bg-bark px-6 py-2 font-bold text-cream hover:opacity-90">
                    Leave group
                </button>
            </form>
        @endif
    </div>

    {{-- Owner-only popups --}}
    @if ($isOwner)

        {{-- Add task popup --}}
        <dialog id="add-task-modal"
                class="m-auto max-h-[90vh] w-full max-w-md overflow-y-auto rounded-3xl bg-cream p-8 text-bark backdrop:bg-black/50">
            <h2 class="text-center text-2xl font-bold">New Group Task</h2>

            @php
                $addAssigned = $addOld ? old('assigned_to') : null;
                $addPriority = $addOld ? old('priority', 'medium') : 'medium';
            @endphp

            <form method="POST" action="{{ route('groups.tasks.store', $group) }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="add-task-name" class="font-bold">Task name:</label>
                    <input id="add-task-name" type="text" name="name" value="{{ $addOld ? old('name') : '' }}" required
                           class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                    @error('name', 'addTask')
                        <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="add-task-description" class="font-bold">Details (optional):</label>
                    <textarea id="add-task-description" name="description" rows="3" maxlength="1000"
                              class="mt-1 w-full rounded-2xl bg-white px-5 py-3 outline-none focus:ring-2 focus:ring-leaf">{{ $addOld ? old('description') : '' }}</textarea>
                    @error('description', 'addTask')
                        <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="add-task-assigned" class="font-bold">Assign to:</label>
                    <select id="add-task-assigned" name="assigned_to"
                            class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                        <option value="">Unassigned</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((string) $addAssigned === (string) $member->id)>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to', 'addTask')
                        <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="add-task-priority" class="font-bold">Priority:</label>
                    <select id="add-task-priority" name="priority" required
                            class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                        <option value="low" @selected($addPriority === 'low')>Low</option>
                        <option value="medium" @selected($addPriority === 'medium')>Medium</option>
                        <option value="high" @selected($addPriority === 'high')>High</option>
                    </select>
                    @error('priority', 'addTask')
                        <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="add-task-deadline" class="font-bold">Deadline (optional):</label>
                    <input id="add-task-deadline" type="date" name="deadline" value="{{ $addOld ? old('deadline') : '' }}"
                           class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                    @error('deadline', 'addTask')
                        <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                        Add
                    </button>
                    <button type="button" id="cancel-add-task"
                            class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                        Cancel
                    </button>
                </div>
            </form>
        </dialog>

        {{-- Edit task popup (filled in by the script below) --}}
        <dialog id="edit-task-modal"
                class="m-auto max-h-[90vh] w-full max-w-md overflow-y-auto rounded-3xl bg-cream p-8 text-bark backdrop:bg-black/50">
            <h2 class="text-center text-2xl font-bold">Edit Task</h2>

            <form id="edit-task-form" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit-task-name" class="font-bold">Task name:</label>
                    <input id="edit-task-name" type="text" name="name" required
                           class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                </div>

                <div>
                    <label for="edit-task-description" class="font-bold">Details (optional):</label>
                    <textarea id="edit-task-description" name="description" rows="3" maxlength="1000"
                              class="mt-1 w-full rounded-2xl bg-white px-5 py-3 outline-none focus:ring-2 focus:ring-leaf"></textarea>
                </div>

                <div>
                    <label for="edit-task-assigned" class="font-bold">Assign to:</label>
                    <select id="edit-task-assigned" name="assigned_to"
                            class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                        <option value="">Unassigned</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-task-priority" class="font-bold">Priority:</label>
                    <select id="edit-task-priority" name="priority" required
                            class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <div>
                    <label for="edit-task-deadline" class="font-bold">Deadline (optional):</label>
                    <input id="edit-task-deadline" type="date" name="deadline"
                           class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                        Save
                    </button>
                    <button type="button" id="cancel-edit-task"
                            class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                        Cancel
                    </button>
                </div>
            </form>
        </dialog>

        <script>
            const addModal = document.getElementById('add-task-modal');
            const editModal = document.getElementById('edit-task-modal');
            const editForm = document.getElementById('edit-task-form');
            const updateUrl = @json(route('groups.tasks.update', [$group, '__ID__']));

            document.getElementById('open-add-task').addEventListener('click', () => addModal.showModal());
            document.getElementById('cancel-add-task').addEventListener('click', () => addModal.close());
            document.getElementById('cancel-edit-task').addEventListener('click', () => editModal.close());

            // Clicking a pencil copies that task's details into the edit popup
            document.querySelectorAll('.edit-task-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const d = button.dataset;

                    editForm.action = updateUrl.replace('__ID__', d.id);
                    document.getElementById('edit-task-name').value = d.name;
                    document.getElementById('edit-task-description').value = d.description;
                    document.getElementById('edit-task-assigned').value = d.assigned;
                    document.getElementById('edit-task-priority').value = d.priority;
                    document.getElementById('edit-task-deadline').value = d.deadline;

                    editModal.showModal();
                });
            });

            @if ($errors->addTask->any())
                addModal.showModal();
            @endif
        </script>
    @endif

@endsection