<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-foreground">Recuperar contrasena</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Indica tu correo y te enviaremos un enlace para crear una contrasena nueva.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block h-10 w-full" type="email" name="email" :value="old('email')" required autofocus maxlength="191" title="Introduce un correo electronico valido." autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <a class="text-sm font-medium text-primary hover:underline" href="{{ route('login') }}">Volver al login</a>
            <x-primary-button>Enviar enlace</x-primary-button>
        </div>
    </form>
</x-guest-layout>
