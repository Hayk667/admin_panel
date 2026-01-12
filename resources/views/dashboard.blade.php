<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Posts Statistics -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Posts</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $postsTotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600 dark:text-green-400">Active:</span>
                                <span class="font-bold text-green-600 dark:text-green-400">{{ $postsActive }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400">Inactive:</span>
                                <span class="font-bold text-red-600 dark:text-red-400">{{ $postsInactive }}</span>
                            </div>
                        </div>
                        <canvas id="postsChart" class="mt-4"></canvas>
                    </div>
                </div>

                <!-- Categories Statistics -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Categories</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $categoriesTotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600 dark:text-green-400">Active:</span>
                                <span class="font-bold text-green-600 dark:text-green-400">{{ $categoriesActive }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400">Inactive:</span>
                                <span class="font-bold text-red-600 dark:text-red-400">{{ $categoriesInactive }}</span>
                            </div>
                        </div>
                        <canvas id="categoriesChart" class="mt-4"></canvas>
                    </div>
                </div>

                <!-- Languages Statistics -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Languages</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $languagesTotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600 dark:text-green-400">Active:</span>
                                <span class="font-bold text-green-600 dark:text-green-400">{{ $languagesActive }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400">Inactive:</span>
                                <span class="font-bold text-red-600 dark:text-red-400">{{ $languagesInactive }}</span>
                            </div>
                        </div>
                        <canvas id="languagesChart" class="mt-4"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to get text color based on dark mode
            function getTextColor() {
                const htmlElement = document.documentElement;
                return htmlElement.classList.contains('dark') ? '#e5e7eb' : '#374151';
            }

            const textColor = getTextColor();

            // Posts Chart
            const postsCtx = document.getElementById('postsChart');
            if (postsCtx) {
                new Chart(postsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [{{ $postsActive }}, {{ $postsInactive }}],
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });
            }

            // Categories Chart
            const categoriesCtx = document.getElementById('categoriesChart');
            if (categoriesCtx) {
                new Chart(categoriesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [{{ $categoriesActive }}, {{ $categoriesInactive }}],
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });
            }

            // Languages Chart
            const languagesCtx = document.getElementById('languagesChart');
            if (languagesCtx) {
                new Chart(languagesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [{{ $languagesActive }}, {{ $languagesInactive }}],
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
