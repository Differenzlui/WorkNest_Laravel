@extends('layouts.app')

@section('title', 'Groups - WorkNest')

@section('content')

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-leaf px-5 py-3 font-bold">
            {{ session('status') }}
        </div>
    @endif

    {{-- Title + create button --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold sm:text-3xl">My Groups</h1>

        <button type="button" id="open-group-modal"
                class="shrink-0 rounded-full bg-cream px-6 py-3 font-bold hover:brightness-95 sm:px-8">
            + Create group
        </button>
    </div>

    {{-- Group cards --}}
    @if ($groups->isEmpty())
        <div class="mt-6 rounded-3xl bg-cream p-10 text-center opacity-80">
            <p>You are not in any group yet.</p>
            <p class="mt-1 text-sm">Click "+ Create group" to start one, or ask a group owner to add your email.</p>
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($groups as $group)
                <a href="{{ route('groups.show', $group) }}"
                   class="block rounded-3xl bg-cream p-6 hover:brightness-95">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="truncate text-lg font-bold">{{ $group->name }}</h2>

                        @if ($group->owner_id === auth()->id())
                            <div class="flex shrink-0 items-center gap-1">
                                @if ($group->pending_updates_count > 0)
                                    <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-bold text-amber-900">
                                        {{ $group->pending_updates_count }} to review
                                    </span>
                                @endif
                                <span class="rounded-full bg-leaf px-2 py-0.5 text-xs font-bold">Owner</span>
                            </div>
                        @endif
                    </div>

                    @if ($group->description)
                        <p class="mt-1 line-clamp-2 text-sm opacity-80">{{ $group->description }}</p>
                    @endif

                    <p class="mt-4 text-sm font-bold">
                        {{ $group->members_count }} {{ $group->members_count === 1 ? 'member' : 'members' }}
                        &middot;
                        {{ $group->tasks_count }} {{ $group->tasks_count === 1 ? 'task' : 'tasks' }}
                    </p>
                    @if ($group->tasks_count > 0)
                        <div class="mt-3">
                            <p class="mb-1 text-xs opacity-80">{{ $group->done_tasks_count }} of {{ $group->tasks_count }} done</p>
                            <x-progress-bar :value="$group->tasks_avg_progress ?? 0" />
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Create group popup --}}
    <dialog id="group-modal"
            class="m-auto w-full max-w-md rounded-3xl bg-cream p-8 text-bark backdrop:bg-black/50">
        <h2 class="text-center text-2xl font-bold">New Group</h2>

        <form method="POST" action="{{ route('groups.store') }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <label for="group-name" class="font-bold">Group name:</label>
                <input id="group-name" type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="e.g. WebTech Project Team"
                       class="mt-1 w-full rounded-full bg-white px-5 py-2 outline-none focus:ring-2 focus:ring-leaf">
                @error('name')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="group-description" class="font-bold">Description (optional):</label>
                <textarea id="group-description" name="description" rows="3" maxlength="500"
                          class="mt-1 w-full rounded-2xl bg-white px-5 py-3 outline-none focus:ring-2 focus:ring-leaf">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Create
                </button>
                <button type="button" id="cancel-group"
                        class="rounded-full bg-bark px-8 py-2 font-bold text-cream hover:opacity-90">
                    Cancel
                </button>
            </div>
        </form>
    </dialog>

    <script>
        const groupModal = document.getElementById('group-modal');

        document.getElementById('open-group-modal').addEventListener('click', () => groupModal.showModal());
        document.getElementById('cancel-group').addEventListener('click', () => groupModal.close());

        @if ($errors->hasAny(['name', 'description']))
            groupModal.showModal();
        @endif
    </script>

@endsection