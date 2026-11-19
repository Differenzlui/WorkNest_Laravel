@extends('layouts.app')

@section('title', 'Calendar - WorkNest')

@section('content')

    <div class="overflow-hidden rounded-3xl bg-cream">

        {{-- Month title bar --}}
        <div class="bg-bark py-3 text-center text-xl text-cream">
            {{ $current->format('F Y') }}
        </div>

        {{-- Scrolls sideways on small phones instead of squishing --}}
        <div class="overflow-x-auto">
            <div class="min-w-[40rem] p-4">

                <div class="grid grid-cols-7 border-l-2 border-t-2 border-bark">

                    {{-- Weekday names --}}
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $label)
                        <div class="border-b-2 border-r-2 border-bark py-2 text-center text-sm font-bold">
                            {{ $label }}
                        </div>
                    @endforeach

                    {{-- Days --}}
                    @foreach ($weeks as $week)
                        @foreach ($week as $day)
                            @php
                                $key = $day->toDateString();
                                $inMonth = $day->month === $current->month;

                                // Mix my own tasks and my group tasks, unfinished ones first
                                $items = $tasks->get($key, collect())
                                    ->map(fn ($t) => ['kind' => 'personal', 'task' => $t, 'done' => (bool) $t->is_done])
                                    ->concat(
                                        $groupTasks->get($key, collect())
                                            ->map(fn ($t) => ['kind' => 'group', 'task' => $t, 'done' => $t->progress >= 100])
                                    )
                                    ->sortBy(fn ($item) => $item['done'] ? 1 : 0)
                                    ->values();
                            @endphp

                            <a href="{{ url('/tasks/date/' . $key) }}"
                               class="block min-h-24 border-b-2 border-r-2 border-bark p-1.5 hover:bg-leaf/30 {{ $inMonth ? '' : 'opacity-40' }}">

                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold {{ $day->isToday() ? 'bg-leaf' : '' }}">
                                    {{ $day->day }}
                                </span>

                                @foreach ($items->take(2) as $item)
                                    <p class="mt-1 truncate rounded px-1.5 py-0.5 text-xs
                                              {{ $item['kind'] === 'group' ? 'bg-leaf text-bark ring-1 ring-bark/30' : 'bg-bark text-cream' }}
                                              {{ $item['done'] ? 'line-through opacity-60' : '' }}">
                                        {{ $item['task']->name }}
                                    </p>
                                @endforeach

                                @if ($items->count() > 2)
                                    <p class="mt-1 text-xs font-bold">+{{ $items->count() - 2 }} more</p>
                                @endif
                            </a>
                        @endforeach
                    @endforeach

                </div>

                {{-- Legend --}}
                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1 text-xs">
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-3 w-6 rounded bg-bark"></span> My tasks
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-3 w-6 rounded bg-leaf ring-1 ring-bark/30"></span> Group tasks assigned to me
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Previous / Today / Next --}}
    <div class="mt-4 flex items-center justify-between">
        <a href="{{ route('calendar', ['month' => $current->copy()->subMonth()->format('Y-m')]) }}"
           class="rounded-xl bg-bark px-5 py-2 font-bold text-cream hover:opacity-90">
            &lsaquo; Previous
        </a>

        <a href="{{ route('calendar') }}" class="text-sm font-bold underline">Today</a>

        <a href="{{ route('calendar', ['month' => $current->copy()->addMonth()->format('Y-m')]) }}"
           class="rounded-xl bg-bark px-5 py-2 font-bold text-cream hover:opacity-90">
            Next &rsaquo;
        </a>
    </div>

@endsection