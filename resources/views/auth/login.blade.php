<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <h2 class="text-center text-lg font-semibold text-foreground">Iniciar sesion</h2>

            <div>
                <x-input-label for="email" :value="__('Correo electronico')" />
                <x-text-input id="email" class="mt-1 block h-10 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" maxlength="191" title="Introduce un correo electronico valido." placeholder="admin@cerveceria-europa.local" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Contrasena')" />
                <x-text-input id="password" class="mt-1 block h-10 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="********" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-input bg-background text-primary shadow-sm focus:ring-ring" name="remember">
                    <span class="ms-2 text-sm text-muted-foreground">{{ __('Recordarme') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="rounded text-sm text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-ring" href="{{ route('password.request') }}">
                        {{ __('He olvidado mi contrasena') }}
                    </a>
                @endif
            </div>

            <x-primary-button class="w-full">
                {{ __('Iniciar sesion') }}
            </x-primary-button>

        <p class="text-center text-xs text-muted-foreground">Si necesitas acceso, contacta con el administrador del sistema.</p>
    </form>
</x-guest-layout>
