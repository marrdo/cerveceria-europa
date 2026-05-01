<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-foreground">Confirmar acceso</h1>
        <p class="mt-2 text-sm text-muted-foreground">Esta zona es sensible. Confirma tu contrasena para continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Contrasena" />
            <x-text-input id="password" class="mt-1 block h-10 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>Confirmar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
