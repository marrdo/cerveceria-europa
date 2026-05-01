<section>
    <header>
        <h2 class="text-base font-semibold text-foreground">
            {{ __('Datos del perfil') }}
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            {{ __('Actualiza el nombre visible y el correo de acceso.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $user->nombre)" required autofocus autocomplete="name" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" maxlength="191" title="Introduce un correo electronico valido." />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-foreground">
                        {{ __('Tu correo electronico no esta verificado.') }}

                        <button form="send-verification" class="text-sm font-medium text-primary hover:underline">
                            {{ __('Reenviar correo de verificacion') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-success">
                            {{ __('Se ha enviado un nuevo enlace de verificacion.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-muted-foreground"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>
