<x-guest-layout>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff, #f8fafc);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
        }

        .auth-card {
            max-width: 420px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem 2.5rem;
        }

        .auth-title {
            font-weight: 600;
            color: #1f2937;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 1.75rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.65rem 0.75rem;
            width: 100%;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.2);
        }

        .btn-primary {
            background-color: #4f46e5;
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1.2rem;
            width: 100%;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .text-link {
            text-decoration: none;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .text-link:hover {
            color: #4f46e5;
        }
    </style>

    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="auth-card">
            <h3 class="auth-title">Create Account</h3>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('registerdsfgsdf') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" class="form-control mt-1" type="text" name="name"
                        :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="text-danger mt-1" />
                </div>

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="form-control mt-1" type="email" name="email"
                        :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="text-danger mt-1" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="form-control mt-1" type="password" name="password"
                        required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="text-danger mt-1" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="form-control mt-1" type="password"
                        name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger mt-1" />
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Register') }}
                    </button>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('login') }}" class="text-link">
                        {{ __('Already registered? Log in') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</x-guest-layout>
