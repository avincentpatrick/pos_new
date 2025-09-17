<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('error') === 'inactive')
            <div x-data="{ showModal: true }" x-show="showModal" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-75 z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg text-center">
                    <h2 class="text-2xl font-bold mb-4">Account Inactive</h2>
                    <p class="mb-4">Your account is not yet activated. Please contact the administrator for assistance.</p>
                    <button @click="showModal = false" class="px-4 py-2 bg-custom-dark-blue text-white rounded hover:bg-blue-900">Close</button>
                </div>
            </div>
        @endif

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember"/>
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-custom-orange rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-custom-orange" href="{{ route('register') }}">
                    {{ __('Not registered yet?') }}
                </a>

                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-custom-orange ms-4" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ms-4 bg-custom-dark-blue hover:bg-custom-dark-blue focus:bg-custom-dark-blue active:bg-custom-dark-blue">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
