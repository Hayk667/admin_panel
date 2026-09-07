@props([
    'languages' => null,
    'current' => null,
    'align' => 'right',
])

@php
    $languages = $languages ?? ($activeLanguages ?? collect());
    $currentCode = $current ?? ($langCode ?? \App\Models\Language::currentCode());
    $canSwitch = $languages->count() > 1;
    $menuAlignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="relative" data-lang-switcher>
    @if ($canSwitch)
        <button type="button"
            data-lang-switcher-btn
            class="inline-flex items-center gap-1 px-2.5 py-2 rounded-md text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="{{ __('Language') }}">
            <span>{{ strtoupper($currentCode) }}</span>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        <div data-lang-switcher-menu
            class="hidden absolute {{ $menuAlignClass }} top-full mt-1 z-50 min-w-[5.5rem] py-1 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-200 dark:border-gray-600"
            role="menu"
            aria-label="{{ __('Language') }}">
            @foreach ($languages as $language)
                <a href="{{ route('locale.switch', ['code' => $language->code, 'redirect' => url()->full()]) }}"
                    role="menuitem"
                    class="block px-4 py-2 text-sm font-semibold uppercase tracking-wide {{ $language->code === $currentCode ? 'bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600' }}"
                    @if ($language->code === $currentCode) aria-current="true" @endif>
                    {{ strtoupper($language->code) }}
                </a>
            @endforeach
        </div>
    @else
        <span class="inline-flex items-center px-2.5 py-2 rounded-md text-sm font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 cursor-default select-none"
            aria-disabled="true"
            title="{{ __('Language') }}">
            {{ strtoupper($currentCode) }}
        </span>
    @endif
</div>
