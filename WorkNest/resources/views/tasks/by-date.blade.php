@extends('layouts.app')

@section('title', 'Tasks due ' . $day->format('M j, Y') . ' - WorkNest')

@section('content')

    {{-- Messages --}}
    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-leaf px-5 py-3 font-bold">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl bg-red-100 px-5 py-3 text-red-800">
            <p class="font-bold">Could not save your changes:</p>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Title + Back button --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold sm:text-3xl">Tasks due on {{ $day->format('F j, Y') }}</h1>

        <a href="{{ route('calendar', ['month' => $day->format('Y-m')]) }}"
           class="shrink-0 rounded-xl bg-bark px-5 py-2 font-bold text-cream hover:opacity-90">
            &lsaquo; Back
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('tasks.by-date', $date) }}" class="mt-4">
        <label class="flex items-center gap-3 rounded-full bg-cream px-5 py-4">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/>
                <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
            </svg>
            <input type="search" name="search" value="{{ $search }}"
                   placeholder="Search this day's tasks and press Enter"
                   class="w-full bg-transparent outline-none">
        </label>
    </form>

    {{-- Task card --}}
    <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
        <h2 class="text-lg font-bold">All Tasks</h2>

        @if ($tasks->isEmpty())
            <div class="py-10 text-center opacity-70">
                @if ($search !== '')
                    <p>No tasks match "{{ $search }}".</p>
                    <a href="{{ route('tasks.by-date', $date) }}" class="mt-2 inline-block underline">Clear search</a>
                @else
                    <p>No personal tasks are due on this day.</p>
                @endif
            </div>
        @else
            <ul class="mt-6">
                @foreach ($tasks as $task)
                    @php
                        $priorityColor = match ($task->priority) {
                            'high' => 'text-red-700',
                            'low' => 'text-green-700',
                            default => 'text-yellow-700',
                        };
                    @endphp

                    <li class="-mt-0.5 flex items-center gap-3 first:mt-0">
                        @include('tasks._toggle', ['task' => $task])

                        <div class="grid flex-1 grid-cols-[1fr_5rem_6rem] divide-x-2 divide-bark border-2 border-bark sm:grid-cols-[1fr_10rem_9rem]">
                            <div class="min-w-0 px-4 py-3">
                                <p class="truncate font-bold {{ $task->is_done ? 'line-through opacity-50' : '' }}">
                                    {{ $task->name }}
                                </p>
                                <p class="text-xs font-bold {{ $priorityColor }}">
                                    {{ ucfirst($task->priority) }} priority
                                </p>
                            </div>

                            <div class="flex items-center justify-center px-2 text-center text-sm">
                                {{ $task->subject }}
                            </div>

                            <div class="flex items-center justify-center px-2 text-center text-sm">
                                {{ $task->deadline->format('m/d/Y') }}
                            </div>
                        </div>

                        {{-- Edit and delete icons --}}
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" title="Edit task" aria-label="Edit task"
                                    class="edit-task-btn rounded p-1 hover:bg-leaf"
                                    data-action="{{ route('tasks.update', $task) }}"
                                    data-name="{{ $task->name }}"
                                    data-subject="{{ $task->subject }}"
                                    data-priority="{{ $task->priority }}"
                                    data-deadline="{{ $task->deadline->toDateString() }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                </svg>
                            </button>

                            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
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
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Group tasks assigned to me on this day --}}
    @include('groups._assigned-list', ['groupTasks' => $groupTasks, 'heading' => 'Group tasks due on this day'])

    {{-- Edit popup --}}
    <dialog id="edit-modal"
            class="m-auto w-full max-w-md rounded-3xl bg-cream p-8 text-bark backdrop:bg-black/50">
        <h2 class="text-center text-2xl font-bold">Edit Task</h2>

        <form id="edit-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-name" class="font-bold">Task name:</label>
                <input id="edit-name" type="text" name="name" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
            </div>

            <div>
                <label for="edit-subject" class="font-bold">Subject:</label>
                <input id="edit-subject" type="text" name="subject" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
            </div>

            <div>
                <label for="edit-priority" class="font-bold">Priority:</label>
                <select id="edit-priority" name="priority" required
                        class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div>
                <label for="edit-deadline" class="font-bold">Deadline:</label>
                <input id="edit-deadline" type="date" name="deadline" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Save
                </button>
                <button type="button" id="cancel-edit"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Cancel
                </button>
            </div>
        </form>
    </dialog>

    <script>
        const editModal = document.getElementById('edit-modal');
        const editForm = document.getElementById('edit-form');

        document.querySelectorAll('.edit-task-btn').forEach((button) => {
            button.addEventListener('click', () => {
                editForm.action = button.dataset.action;
                document.getElementById('edit-name').value = button.dataset.name;
                document.getElementById('edit-subject').value = button.dataset.subject;
                document.getElementById('edit-priority').value = button.dataset.priority;
                document.getElementById('edit-deadline').value = button.dataset.deadline;
                editModal.showModal();
            });
        });

        document.getElementById('cancel-edit').addEventListener('click', () => editModal.close());
    </script>

@endsection