<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-center fw-bold text-dark">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow-lg rounded-4 p-4 text-center" style="max-width: 450px;">
            <div class="card-body">
                <h3 class="card-title fw-semibold mb-3">
                    {{ __("Welcome, :name!", ['name' => auth()->user()->name]) }}
                </h3>
                <p class="card-text mb-4">
                    {{ __("You're just registered! Please wait for the admin approval.") }}
                </p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary w-100">
                    {{ __('Go to Admin Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</x-guest-layout>
