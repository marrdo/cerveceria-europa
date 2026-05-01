<button {{ $attributes->merge(['type' => 'button', 'class' => 'admin-btn-outline disabled:opacity-50']) }}>
    {{ $slot }}
</button>
