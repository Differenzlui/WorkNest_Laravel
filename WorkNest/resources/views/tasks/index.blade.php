@extends('layouts.app')

@section('title', 'Home - WorkNest')

@section('content')

    {{-- Success message --}}
    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-leaf px-5 py-3 font-bold">
            {{ session('status') }}
        </div>
    @endif

    {{-- Top bar: add button + search --}}
    <div class="flex flex-col gap-3 sm:flex-row">
        <button type="button" id="open-task-modal"
                class="shrink-0 rounded-full bg-cream px-8 py-4 font-bold hover:brightness-95">
            + Add new task
        </button>

        <form method="GET" action="{{ route('tasks.index') }}" class="flex-1">
            <label class="flex items-center gap-3 rounded-full bg-cream px-5 py-4">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Search tasks and press Enter"
                       class="w-full bg-transparent outline-none">
            </label>
        </form>
    </div>

    {{-- Task list card --}}
    <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
        <h1 class="text-center text-xl font-bold">Task List</h1>

        @if ($tasks->isEmpty())
            <div class="py-10 text-center opacity-70">
                @if ($search !== '')
                    <p>No tasks match "{{ $search }}".</p>
                    <a href="{{ route('tasks.index') }}" class="mt-2 inline-block underline">Clear search</a>
                @else
                    <p>You have no tasks yet. Click "+ Add new task" to create one.</p>
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

                        <div class="grid flex-1 grid-cols-[1fr_6rem_6rem] divide-x-2 divide-bark border-2 border-bark sm:grid-cols-[1fr_10rem_9rem]">
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
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Group tasks assigned to me --}}
    @include('groups._assigned-list', ['groupTasks' => $groupTasks, 'heading' => 'Assigned to me in groups'])

    {{-- New Task popup --}}
    <dialog id="task-modal"
            class="m-auto w-full max-w-md rounded-3xl bg-cream p-8 text-bark backdrop:bg-black/50">
        <h2 class="text-center text-2xl font-bold">New Task</h2>

        <form method="POST" action="{{ route('tasks.store') }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <label for="task-name" class="font-bold">Task name:</label>
                <input id="task-name" type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                @error('name')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="task-subject" class="font-bold">Subject:</label>
                <input id="task-subject" type="text" name="subject" value="{{ old('subject') }}" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                @error('subject')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="task-priority" class="font-bold">Priority:</label>
                <select id="task-priority" name="priority" required
                        class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                    <option value="low" @selected(old('priority') === 'low')>Low</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                    <option value="high" @selected(old('priority') === 'high')>High</option>
                </select>
                @error('priority')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="task-deadline" class="font-bold">Deadline:</label>
                <input id="task-deadline" type="date" name="deadline" value="{{ old('deadline') }}" required
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf sm:w-48">
                @error('deadline')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Add
                </button>
                <button type="button" id="cancel-task"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Cancel
                </button>
            </div>
        </form>
    </dialog>

    <script>
        const modal = document.getElementById('task-modal');

        document.getElementById('open-task-modal').addEventListener('click', () => modal.showModal());
        document.getElementById('cancel-task').addEventListener('click', () => modal.close());

        @if ($errors->hasAny(['name', 'subject', 'priority', 'deadline']))
            modal.showModal();
        @endif
    </script>

@endsection