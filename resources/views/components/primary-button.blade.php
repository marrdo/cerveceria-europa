<button {{ $attributes->merge(['type' => 'submit', 'class' => 'admin-btn-primary']) }}>
    {{ $slot }}
</button>
