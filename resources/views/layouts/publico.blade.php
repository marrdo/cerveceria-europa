<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $pageTitle = filled($title) ? $title.' | '.$negocio->nombre_comercial : ($negocio->seo_titulo ?: $negocio->nombre_comercial);
            $pageDescription = $description ?: ($negocio->seo_descripcion ?: ($negocio->descripcion_corta ?: $negocio->eslogan));
            $canonicalUrl = $canonical ?: $negocio->urlCanonica();
            $socialImage = $ogImage ?: $negocio->urlRecurso($negocio->imagen_social_path, $negocio->urlRecurso($negocio->logo_path, asset('favicon.svg')));
            $faviconUrl = $negocio->urlRecurso($negocio->favicon_path, asset('favicon.svg'));
            $logoUrl = $negocio->urlRecurso($negocio->logo_path);
            $reservasUrl = $negocio->reservas_url ?: route('web.contacto');
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="robots" content="{{ $negocio->seo_indexar ? 'index, follow' : 'noindex, nofollow' }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">

        <meta property="og:site_name" content="{{ $negocio->nombre_comercial }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $socialImage }}">
        <meta property="og:locale" content="es_ES">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        <meta name="twitter:image" content="{{ $socialImage }}">

        <meta name="theme-color" content="{{ $negocio->color_fondo ?: '#0F0A06' }}">
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="manifest" href="{{ route('web.manifest') }}">
        <style>:root { @foreach ($negocio->variablesCssPublicas() as $variable => $valor){{ $variable }}: {{ $valor }}; @endforeach --v2-line: rgb(var(--color-ink) / .10); --v2-line-2: rgb(var(--color-ink) / .18); }</style>
        <script type="application/ld+json">{!! json_encode($seoEstructurado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="flex min-h-screen flex-col bg-public-background font-sans text-public-foreground">
        <header class="sticky top-0 z-40 border-b border-public-border/15 bg-public-background/90 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('web.inicio') }}" class="flex min-w-0 items-center gap-3">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $negocio->nombre_comercial }}" class="h-11 max-w-44 object-contain object-left">
                        @else
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-amber-bright text-stout"><x-brand.hospitality-icon class="h-6 w-6" /></span>
                        @endif
                        <span class="min-w-0">
                            <span class="block max-w-48 truncate text-lg font-black leading-5 text-public-foreground">{{ $negocio->nombre_comercial }}</span>
                            @if ($negocio->localidad)<span class="block text-xs font-semibold uppercase tracking-[0.18em] text-public-primary">{{ $negocio->localidad }}</span>@endif
                        </span>
                    </a>
                    <nav class="hidden items-center gap-6 text-sm font-semibold text-public-muted md:flex" aria-label="Navegación principal">
                        <a href="{{ route('web.carta') }}" class="hover:text-public-primary">Carta</a>
                        <a href="{{ route('web.cervezas') }}" class="hover:text-public-primary">Cervezas</a>
                        <a href="{{ route('web.fuera-carta') }}" class="hover:text-public-primary">Fuera de carta</a>
                        <a href="{{ route('web.recomendaciones') }}" class="hover:text-public-primary">Recomendaciones</a>
                        @if (\App\Models\Modulo::activo('blog'))<a href="{{ route('web.blog') }}" class="hover:text-public-primary">Blog</a>@endif
                        <a href="{{ route('web.contacto') }}" class="hover:text-public-primary">Contacto</a>
                    </nav>
                    <a href="{{ $reservasUrl }}" @if($negocio->reservas_url) target="_blank" rel="noopener noreferrer" @endif class="v2-btn v2-btn-primary hidden shrink-0 sm:inline-flex">Reservar</a>
                </div>
                <nav class="mt-3 flex gap-5 overflow-x-auto border-t border-public-border/10 pt-3 text-xs font-semibold text-public-muted md:hidden" aria-label="Navegación móvil">
                    <a href="{{ route('web.carta') }}" class="shrink-0">Carta</a><a href="{{ route('web.cervezas') }}" class="shrink-0">Cervezas</a><a href="{{ route('web.fuera-carta') }}" class="shrink-0">Fuera de carta</a><a href="{{ route('web.recomendaciones') }}" class="shrink-0">Recomendaciones</a>@if (\App\Models\Modulo::activo('blog'))<a href="{{ route('web.blog') }}" class="shrink-0">Blog</a>@endif<a href="{{ route('web.contacto') }}" class="shrink-0">Contacto</a>
                </nav>
            </div>
        </header>

        <main class="flex-1">{{ $slot }}</main>

        <footer class="border-t bg-stout px-8 pb-6 pt-20" style="border-color: var(--v2-line);" aria-labelledby="footer-heading">
            <h2 id="footer-heading" class="sr-only">Información de {{ $negocio->nombre_comercial }}</h2>
            <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-10 pb-14 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]" style="border-bottom: 1px solid var(--v2-line);">
                <section><div class="max-w-[12ch] font-display text-[56px] leading-[0.9] text-ink">{{ $negocio->nombre_comercial }}</div><p class="my-4 max-w-[36ch] text-sm leading-6 text-ink-mute">{{ $negocio->eslogan ?: $negocio->descripcion_corta }}</p><a href="{{ $reservasUrl }}" class="v2-btn v2-btn-primary">Reservar mesa</a></section>
                <nav aria-labelledby="f-carta"><h4 id="f-carta" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Carta</h4><ul class="flex flex-col gap-2.5 text-sm text-ink"><li><a href="{{ route('web.carta') }}">Carta completa</a></li><li><a href="{{ route('web.cervezas') }}">Cervezas</a></li><li><a href="{{ route('web.fuera-carta') }}">Fuera de carta</a></li><li><a href="{{ route('web.recomendaciones') }}">Recomendaciones</a></li>@if (\App\Models\Modulo::activo('blog'))<li><a href="{{ route('web.blog') }}">Blog</a></li>@endif</ul></nav>
                <section aria-labelledby="f-local"><h4 id="f-local" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Local</h4><ul class="flex flex-col gap-2.5 text-sm text-ink">@if ($negocio->direccionCompleta())<li>{{ $negocio->direccionCompleta() }}</li>@elseif ($negocio->localidad)<li>{{ $negocio->localidad }}</li>@endif @if ($negocio->horario)<li class="whitespace-pre-line">{{ $negocio->horario }}</li>@endif</ul></section>
                <section aria-labelledby="f-contact"><h4 id="f-contact" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Hablamos</h4><ul class="flex flex-col gap-2.5 text-sm text-ink">@if ($negocio->telefono)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $negocio->telefono) }}">{{ $negocio->telefono }}</a></li>@endif @if ($negocio->email)<li><a href="mailto:{{ $negocio->email }}">{{ $negocio->email }}</a></li>@endif @if ($negocio->instagram_url)<li><a href="{{ $negocio->instagram_url }}" target="_blank" rel="noopener noreferrer">Instagram ↗</a></li>@endif @if ($negocio->google_maps_url)<li><a href="{{ $negocio->google_maps_url }}" target="_blank" rel="noopener noreferrer">Google Maps ↗</a></li>@endif</ul></section>
            </div>
            <div class="mx-auto mt-6 flex max-w-[1440px] flex-wrap items-center justify-between gap-3 text-[11px] uppercase tracking-[0.08em] text-ink-mute"><span>&copy; {{ now()->year }} {{ $negocio->nombre_comercial }}@if($negocio->localidad) · {{ $negocio->localidad }}@endif</span><span class="font-mono text-[10.5px] normal-case">Gestión conectada con el local</span></div>
        </footer>
    </body>
</html>
