@extends('layouts.app')

@section('title', $group->name . ' - WorkNest')

@section('content')

    @php
        $isOwner = $group->owner_id === auth()->id();
        $members = $group->members->sortBy(fn ($m) => [$m->id === $group->owner_id ? 0 : 1, strtolower($m->name)]);
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
                <li class="flex items-center justify-between gap-3 py-3">
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
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Placeholder for the next parts --}}
    <div class="mt-6 rounded-3xl bg-cream p-6 text-center opacity-80 sm:p-8">
        Group tasks and progress will appear here.
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

@endsection