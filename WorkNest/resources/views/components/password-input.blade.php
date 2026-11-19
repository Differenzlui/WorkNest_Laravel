@props([
    'name',
    'placeholder' => 'Password',
    'autocomplete' => 'current-password',
])

<div class="relative">
    <input type="password" id="{{ $name }}" name="{{ $name }}"
           placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete }}" required
           class="w-full rounded-2xl bg-white py-4 pl-5 pr-14 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">

    <button type="button" data-toggle-password
            aria-label="Show password" aria-pressed="false"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-bark/70 hover:text-bark">

        {{-- Eye: shown while the password is hidden --}}
        <svg data-eye class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        {{-- Crossed-out eye: shown while the password is visible --}}
        <svg data-eye-off class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>
    </button>
</div>