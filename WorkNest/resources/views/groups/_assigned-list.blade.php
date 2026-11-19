@if ($groupTasks->isNotEmpty())
    <div class="mt-6 rounded-3xl bg-cream p-6 sm:p-8">
        <h2 class="text-lg font-bold">{{ $heading }}</h2>

        <ul class="mt-4 divide-y-2 divide-bark/10">
            @foreach ($groupTasks as $task)
                @php
                    $done = $task->progress >= 100;
                    $overdue = $task->deadline && ! $done && $task->deadline->lt(today());
                    $priorityColor = match ($task->priority) {
                        'high' => 'text-red-700',
                        'low' => 'text-green-700',
                        default => 'text-yellow-700',
                    };
                @endphp

                <li class="py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('groups.show', $task->group) }}"
                               class="font-bold hover:underline {{ $done ? 'line-through opacity-50' : '' }}">
                                {{ $task->name }}
                            </a>

                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span class="rounded-full bg-leaf px-2 py-0.5 font-bold">{{ $task->group->name }}</span>
                                <span class="font-bold {{ $priorityColor }}">{{ ucfirst($task->priority) }} priority</span>

                                @if ($task->deadline)
                                    <span class="{{ $overdue ? 'font-bold text-red-700' : '' }}">
                                        Due {{ $task->deadline->format('M j, Y') }}@if ($overdue) (overdue)@endif
                                    </span>
                                @else
                                    <span class="opacity-60">No deadline</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('groups.show', $task->group) }}"
                           class="shrink-0 rounded-full bg-bark px-4 py-1.5 text-sm font-bold text-cream hover:opacity-90">
                            {{ $done ? 'View' : 'Update progress' }}
                        </a>
                    </div>

                    <div class="mt-3">
                        <x-progress-bar :value="$task->progress" />
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif