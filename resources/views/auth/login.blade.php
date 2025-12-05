<<<<<<< HEAD
<form method="POST" action="/login">
    @csrf
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
=======
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Title for improved context -->
        <h2 class="text-2xl font-bold text-center text-green-700 dark:text-green-400 mb-6">{{ __('My Campus Login') }}</h2>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full border-green-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                autocomplete="username" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input 
                id="password" 
                class="block mt-1 w-full border-green-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm"
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- User Role Selection -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Login as')" />

            <select id="role" name="role" required 
                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 
                       focus:border-green-500 dark:focus:border-green-600 focus:ring-green-500 dark:focus:ring-green-600 
                       rounded-lg shadow-sm transition duration-150 ease-in-out">
                
                <option value="" disabled {{ old('role') == '' ? 'selected' : '' }}>Select Your Role</option>
                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
            </select>

            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>
        
        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <!-- Checkbox color changed to green -->
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-green-600 shadow-sm focus:ring-green-500 dark:focus:ring-green-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <!-- Primary button color changed to green -->
            <x-primary-button class="bg-green-600 hover:bg-green-700 focus:ring-green-500 active:bg-green-800 transition duration-150 ease-in-out">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
>>>>>>> c653165ac55551c0049960c40ff51e122d18c651
