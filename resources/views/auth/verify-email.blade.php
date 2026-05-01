<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-foreground">Verifica tu correo</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Antes de entrar al panel, verifica tu correo con el enlace que acabamos de enviarte.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-3 text-sm font-medium text-success">
            Se ha enviado un nuevo enlace de verificacion.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>Reenviar correo</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-medium text-primary hover:underline">
                Cerrar sesion
            </button>
        </form>
    </div>
</x-guest-layout>
