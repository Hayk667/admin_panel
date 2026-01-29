<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Page Permissions') }} - {{ $pagePermission->page_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-notification />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.page-permissions.update', $pagePermission) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Select Roles Allowed to Access: {{ $pagePermission->page_name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Route: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $pagePermission->page_route }}</code>
                            </p>
                            
                            <div class="space-y-4">
                                @foreach ($roles as $role)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="role_{{ $role->id }}" 
                                                name="roles[]" 
                                                type="checkbox" 
                                                value="{{ $role->id }}"
                                                {{ in_array($role->id, $allowedRoles) ? 'checked' : '' }}
                                                class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_{{ $role->id }}" class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ $role->name }}
                                            </label>
                                            @if ($role->description)
                                                <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ $role->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                                <strong>Note:</strong> Admin role automatically has access to all pages and cannot be restricted.
                            </p>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Update Page Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

