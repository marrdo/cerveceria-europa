<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-foreground">Crear usuario</h1>
        <p class="mt-2 text-sm text-muted-foreground">Alta de acceso para el panel de Cerveceria Europa.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" class="mt-1 block h-10 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block h-10 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contrasena" />
            <x-text-input id="password" class="mt-1 block h-10 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contrasena" />
            <x-text-input id="password_confirmation" class="mt-1 block h-10 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <a class="text-sm font-medium text-primary hover:underline" href="{{ route('login') }}">
                Ya tengo cuenta
            </a>

            <x-primary-button>Registrar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
