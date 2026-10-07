@if (session('success'))
    <div class="notice notice-success notification-alert" role="alert">
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="notice notice-error notification-alert" role="alert">
        <p>{{ session('error') }}</p>
    </div>
@endif

