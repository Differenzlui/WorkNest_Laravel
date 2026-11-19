@props(['value' => 0, 'big' => false])

@php
    // Keep the number between 0 and 100 so the bar can never overflow
    $percent = max(0, min(100, (int) round((float) $value)));
@endphp

<div class="flex items-center gap-3">
    <div class="{{ $big ? 'h-4' : 'h-2.5' }} flex-1 overflow-hidden rounded-full bg-bark/15"
         role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
        <div class="h-full rounded-full transition-all {{ $percent >= 100 ? 'bg-[#3E9B4F]' : 'bg-[#6BBF73]' }}"
             style="width: {{ $percent }}%"></div>
    </div>
    <span class="w-11 shrink-0 text-right text-xs font-bold">{{ $percent }}%</span>
</div>