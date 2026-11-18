<form method="POST" action="{{ route('tasks.toggle', $task) }}" class="shrink-0">
    @csrf
    @method('PATCH')

    <button type="submit"
            title="{{ $task->is_done ? 'Mark as not done' : 'Mark as done' }}"
            aria-label="{{ $task->is_done ? 'Mark as not done' : 'Mark as done' }}"
            class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-bark {{ $task->is_done ? 'bg-bark text-cream' : 'hover:bg-leaf' }}">
        @if ($task->is_done)
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        @endif
    </button>
</form>