<x-app-layout>
    <x-slot name="header">
        Perfil
    </x-slot>

    <x-admin.page-header
        titulo="Perfil"
        subtitulo="Gestiona los datos de acceso de tu usuario del panel."
    />

    <div class="space-y-6">
        <div class="admin-card p-4 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="admin-card p-4 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="admin-card p-4 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
