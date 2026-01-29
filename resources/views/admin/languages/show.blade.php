<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Language Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <strong>Code:</strong> {{ $language->code }}
                    </div>
                    <div class="mb-4">
                        <strong>Name:</strong> {{ $language->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Default:</strong> {{ $language->is_default ? 'Yes' : 'No' }}
                    </div>
                    <div class="mb-4">
                        <strong>Status:</strong> {{ $language->is_active ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.languages.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Back to list</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

