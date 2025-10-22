<x-guest-layout>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff, #f8fafc);
            font-family: 'Poppins', sans-serif;
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem 2.5rem;
        }

        .login-title {
            font-weight: 600;
            color: #1f2937;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.2);
        }

        .btn-primary {
            background-color: #4f46e5;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .forgot-link {
            text-decoration: none;
            color: #6b7280;
        }

        .forgot-link:hover {
            color: #4f46e5;
        }
    </style>

    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="login-card">
            <h3 class="text-center mb-4 login-title">{{ __('Welcome Back') }}</h3>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="form-control mt-1" type="email" name="email"
                        :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="text-danger mt-2" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="form-control mt-1" type="password" name="password"
                        required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="text-danger mt-2" />
                </div>

                <div class="form-check mb-3">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label">
                        {{ __('Remember me') }}
                    </label>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    @if (Route::has('password.request'))
                        <a class="forgot-link small" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</x-guest-layout>
