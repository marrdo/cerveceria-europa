<section class="space-y-6">
    <header>
        <h2 class="text-base font-semibold text-foreground">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            Esta accion elimina tu usuario. En el proyecto usamos borrado logico cuando aplica, pero conviene confirmar siempre antes de tocar accesos.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="bg-card p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-foreground">
                Confirmar eliminacion de cuenta
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Introduce tu contrasena para confirmar que quieres eliminar esta cuenta.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contrasena" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Contrasena"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
