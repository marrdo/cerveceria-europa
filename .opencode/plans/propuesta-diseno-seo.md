# Propuesta de Rediseño — Cerveceria Europa
## Design System + Estrategia SEO

---

## 1. VISION GENERAL

### 1.1 Concepto creativo
**"Del barril a la pantalla"** — Un diseño que traslada la experiencia sensorial de una cerveceria artesanal al entorno digital. Texturas, colores y microinteracciones que evocan espuma, cobre, madera de barril y el ritual de servir una cerveza.

### 1.2 Principios de diseño
- **Autenticidad**: Materiales reales (cobre, madera, vidrio, espuma), no artificiales
- **Legibilidad ante todo**: La carta se lee facil, los alergenos son visibles, los precios claros
- **Mobile-first**: El 80%+ de las consultas de carta de restaurante son desde movil
- **Performance como feature**: La web carga antes de que te sirvan la primera ronda

---

## 2. DESIGN SYSTEM

### 2.1 Paleta de colores

#### Colores primarios — Inspirados en la cerveza

| Token | Light | Dark | Referencia |
|-------|-------|------|------------|
| `--color-amber-50` | `#FFF8E1` | `#2A2008` | Espuma clara |
| `--color-amber-100` | `#FFECB3` | `#3D2E0A` | Pilsner |
| `--color-amber-400` | `#D4A017` | `#E8B830` | Cerveza rubia |
| `--color-amber-600` | `#B8860B` | `#C9971A` | Amber Ale |
| `--color-amber-800` | `#6B4404` | `#8B5E1A` | Cobre del barril |
| `--color-amber-950` | `#2B1A00` | `#1A0F00` | Stout |

#### Colores de soporte

| Token | Light | Dark | Uso |
|-------|-------|------|-----|
| `--color-foam` | `#FAF7F0` | `#1C1914` | Fondo principal (espuma) |
| `--color-malt` | `#F5EDE0` | `#2A2218` | Superficies/cards |
| `--color-hops` | `#4A7C59` | `#5D9B6E` | Acentos positivos/destacados |
| `--color-barrel` | `#8B6F47` | `#A68B5B` | Texto secundario, bordes |
| `--color-ink` | `#1A1208` | `#F0E8D8` | Texto principal |
| `--color-glass` | `rgba(255,255,255,0.6)` | `rgba(0,0,0,0.3)` | Glassmorphism overlays |

#### Colores semanticos

| Token | Light | Dark | Uso |
|-------|-------|------|-----|
| `--color-stock-ok` | `#4A7C59` | `#5D9B6E` | Stock disponible |
| `--color-stock-low` | `#D4A017` | `#E8B830` | Stock bajo |
| `--color-stock-out` | `#C0392B` | `#E74C3C` | Sin stock |
| `--color-allergen` | `#E67E22` | `#F39C12` | Alergenos |

### 2.2 Tipografia

#### Primaria — Display y headings
**"Bebas Neue"** (Google Fonts) — Condensada, bold, evoca rotulos de cervecerias artesanales y etiquetas de botella.

| Nivel | Tamaño | Peso | Line-height | Uso |
|-------|--------|------|-------------|-----|
| H1 Hero | `clamp(3rem, 8vw, 6rem)` | 400 | 0.9 | Titulo principal homepage |
| H1 Page | `clamp(2.5rem, 5vw, 4rem)` | 400 | 1.0 | Titulos de seccion |
| H2 | `clamp(1.75rem, 3vw, 2.5rem)` | 400 | 1.1 | Subsecciones |
| H3 | `clamp(1.25rem, 2vw, 1.75rem)` | 400 | 1.2 | Cards, articulos |
| H4 | `1.125rem` | 400 | 1.3 | Items de carta |

#### Secundaria — Body y UI
**"Inter"** (Google Fonts) — Limpia, legible, excelente para menus y texto largo.

| Nivel | Tamaño | Peso | Line-height | Uso |
|-------|--------|------|-------------|-----|
| Body | `1rem` (16px) | 400 | 1.6 | Texto general |
| Body Large | `1.125rem` | 400 | 1.7 | Descripciones, blog |
| Small | `0.875rem` | 400 | 1.5 | Metadata, precios |
| Caption | `0.75rem` | 500 | 1.4 | Alergenos, notas |
| Label | `0.8125rem` | 600 | 1.4 | Botones, nav |

#### Numeros y precios
**"JetBrains Mono"** (Google Fonts) — Para precios y cantidades, alineacion perfecta.

### 2.3 Espaciado

Sistema basado en 4px con escala cervecera:

| Token | Valor | Referencia |
|-------|-------|------------|
| `space-1` | `4px` | Gota |
| `space-2` | `8px` | Burbuja |
| `space-3` | `12px` | — |
| `space-4` | `16px` | Base |
| `space-6` | `24px` | — |
| `space-8` | `32px` | — |
| `space-12` | `48px` | — |
| `space-16` | `64px` | — |
| `space-20` | `80px` | — |
| `space-24` | `96px` | Hero padding |

### 2.4 Bordes y sombras

#### Border radius
| Token | Valor | Uso |
|-------|-------|-----|
| `radius-sm` | `4px` | Inputs, badges |
| `radius-md` | `8px` | Cards, botones |
| `radius-lg` | `12px` | Contenedores |
| `radius-xl` | `20px` | Modales, overlays |
| `radius-full` | `9999px` | Pills, avatares |

#### Sombras — Inspiradas en profundidad de vidrio
```css
--shadow-glass: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
--shadow-card: 0 2px 8px rgba(0,0,0,0.06), 0 8px 24px rgba(0,0,0,0.08);
--shadow-elevated: 0 4px 16px rgba(0,0,0,0.1), 0 16px 48px rgba(0,0,0,0.12);
--shadow-amber-glow: 0 0 24px rgba(212,160,23,0.15); /* Hover states */
```

### 2.5 Componentes principales

#### 2.5.1 Header / Navegacion
```
┌─────────────────────────────────────────────────────────┐
│  [Logo]  Cerveceria          Carta  Cervezas  Blog  ☰  │
│            EUROPA                                      │
└─────────────────────────────────────────────────────────┘
```
- **Desktop**: Logo + nombre a la izquierda, nav centrado, CTA "Reservar" a la derecha
- **Mobile**: Logo compacto, hamburger menu con animacion de vertido de cerveza
- **Efecto scroll**: Glassmorphism con backdrop-blur, fondo semi-transparente con textura sutil de espuma

#### 2.5.2 Hero Section
```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│   ████████  Background: video/imagen de barril          │
│   ██      ██  sirviendo cerveza con overlay oscuro      │
│   ██ CERVECERIA ██                                      │
│   ██   EUROPA  ██                                       │
│   ████████  "Cervezas de importacion y artesanas        │
│             en el corazon de Sevilla"                    │
│                                                         │
│   [ Ver Carta ]  [ Recomendaciones ]                    │
│                                                         │
└─────────────────────────────────────────────────────────┘
```
- **Background**: Imagen/video hero de barril sirviendo o primer plano de cerveza con espuma
- **Overlay**: Gradiente oscuro con textura sutil de grano de malta
- **Animacion**: Burbujas CSS sutiles subiendo en el fondo
- **CTAs**: Boton primario amber con hover glow, secundario outline

#### 2.5.3 Carta — Menu interactivo
```
┌─────────────────────────────────────────────────────────┐
│  [Cervezas] [Tapas] [Platos] [Postres] [Sin Alcohol]    │  ← Tabs sticky
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────────────────┐  ┌─────────────────────┐ │
│  │ 🍺 IPA Artesana          │  │ 🍺 Pilsner Checa    │ │
│  │ ──────────────── 4,50€  │  │ ─────────── 3,80€   │ │
│  │ Notas citricas y amargor │  │ Ligera y refrescante│ │
│  │ [Alergenos: gluten]      │  │                     │ │
│  └──────────────────────────┘  └─────────────────────┘ │
│                                                         │
└─────────────────────────────────────────────────────────┘
```
- **Tabs**: Sticky con scroll horizontal en mobile, indicador amber animado
- **Cards**: Layout en grid responsive (1 col mobile, 2 tablet, 3 desktop)
- **Item de carta**:
  - Icono/emoji de tipo de producto
  - Nombre en Bebas Neue
  - Precio en JetBrains Mono alineado a la derecha
  - Descripcion corta en Inter
  - Badges de alergenos con iconos (gluten, lacteos, frutos secos...)
  - Badge "Fuera de carta" con color amber
  - Badge "Destacado" con color hops verde
  - Indicador de stock (punto verde/amarillo/rojo)

#### 2.5.4 Tarjeta de producto (carta)
```
┌──────────────────────────────────────┐
│  ┌────────────────────────────────┐  │
│  │                                │  │
│  │        Imagen del plato        │  │
│  │                                │  │
│  │  [FUERA DE CARTA]  [DESTACADO] │  │
│  └────────────────────────────────┘  │
│                                      │
│  IPA Artesana              4,50€    │
│  Notas citricas y amargor            │
│  equilibrado con final seco          │
│                                      │
│  🌾 Gluten  🥛 Lacteos               │
└──────────────────────────────────────┘
```

#### 2.5.5 Footer
```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  Cerveceria    Carta        Horario      Contactar      │
│  EUROPA                                                    │
│                  Cervezas    Mar 12-00   [Instagram]     │
│  Cervezas de   Tapas        Jue 12-00   [Google Maps]   │
│  importacion   Platos       Vie 12-01   [Telefono]      │
│  y artesanas   Postres      Sab 12-01                    │
│                  Sin alcohol  Dom 12-16                  │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  © 2026 Cerveceria Europa  ·  Sevilla  ·  Hecho con 🍺 │
└─────────────────────────────────────────────────────────┘
```

#### 2.5.6 Badge de alergenos
Sistema de iconos + texto para alergenos:
```
🌾 Gluten    🥛 Lacteos    🥚 Huevos    🥜 Frutos secos
🐟 Pescado   🦐 Crustaceos 🌿 Apio     🌱 Mostaza
⚫ Sesamo    🫘 Soja        🐚 Moluscos 🫒 Sulfitos
```

#### 2.5.7 Botones
| Variante | Estilo | Uso |
|----------|--------|-----|
| Primary | `bg-amber-600 text-white hover:bg-amber-700` | CTAs principales |
| Secondary | `border-2 border-amber-600 text-amber-600 hover:bg-amber-600 hover:text-white` | Acciones secundarias |
| Ghost | `text-amber-600 hover:bg-amber-50` | Links, acciones menores |
| Danger | `bg-red-600 text-white` | Eliminar, cancelar |

### 2.6 Microinteracciones y animaciones

| Elemento | Animacion | Duracion |
|----------|-----------|----------|
| Hover card | Scale 1.02 + shadow amber glow | 200ms ease-out |
| Tab switch | Indicador slide + fade content | 300ms ease-in-out |
| Scroll reveal | Fade up 20px | 400ms ease-out |
| Button hover | Background shift + subtle glow | 150ms ease-out |
| Menu open | Slide down + stagger children | 300ms ease-out |
| Burbujas hero | Float up con opacity fade | 3s infinite loop |
| Stock indicator | Pulse sutil en rojo/amarillo | 2s infinite |

### 2.7 Iconografia
- **Set principal**: Lucide Icons o Heroicons (outline style)
- **Iconos custom**: Cerveza, barril, trigo, lúpulo, espuma, botella, vaso
- **Alergenos**: Set de iconos SVG custom para cada alergeno

### 2.8 Imagenes y medios
- **Hero**: Fotografias propias del local con tratamiento cálido
- **Productos**: Fotos cenitales o 3/4 con fondo neutro (madera clara)
- **Formato**: WebP con fallback JPEG, lazy loading, srcset para responsive
- **Placeholders**: Gradientes amber-to-barrel mientras carga

---

## 3. ESTRATEGIA SEO + CONTENIDOS

### 3.1 Arquitectura de URLs

```
/                           → Homepage
/carta                      → Carta completa
/carta/cervezas             → Seccion cervezas
/carta/tapas                → Seccion tapas
/carta/platos               → Seccion platos
/fuera-de-carta             → Temporales/edicion limitada
/recomendaciones            → Recomendaciones del bar
/blog                       → Blog index
/blog/{slug}                → Post individual
/blog/categoria/{slug}      → Categoria blog
/contacto                   → Pagina de contacto
/reservas                   → Pagina de reservas (futuro)
```

### 3.2 Meta tags dinamicos por pagina

| Pagina | Title Template | Description Template |
|--------|---------------|---------------------|
| Home | `Cerveceria Europa — Cervezas artesanas en Sevilla` | `Bar especializado en cervezas de importacion y artesanas en Sevilla. Carta de tapas y platos para maridar.` |
| Carta | `Carta — Cerveceria Europa \| Sevilla` | `Descubre nuestra carta de cervezas artesanas, tapas y platos para compartir en Sevilla.` |
| Cervezas | `Cervezas artesanas — Cerveceria Europa` | `IPA, Pilsner, Stout, Weiss... Cervezas de importacion y artesanas con rotacion semanal.` |
| Blog post | `{titulo} — Blog Cerveceria Europa` | `{resumen del post}` |
| Contacto | `Contacto y reservas — Cerveceria Europa \| Sevilla` | `Visitanos en Sevilla. Consulta horarios y reserva tu mesa en Cerveceria Europa.` |

### 3.3 Schema.org / Datos estructurados

#### 3.3.1 Restaurant (todas las paginas)
```json
{
  "@context": "https://schema.org",
  "@type": "Restaurant",
  "name": "Cerveceria Europa",
  "description": "Bar especializado en cervezas de importacion y artesanas en Sevilla",
  "url": "https://cerveceriaeuropa.es",
  "telephone": "+34-XXX-XXX-XXX",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Calle XXX",
    "addressLocality": "Sevilla",
    "postalCode": "4100X",
    "addressCountry": "ES"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": XX.XXXX,
    "longitude": -X.XXXX
  },
  "servesCuisine": ["Spanish", "Tapas", "Beer"],
  "priceRange": "€€",
  "openingHoursSpecification": [...],
  "image": "https://cerveceriaeuropa.es/og-image.jpg",
  "sameAs": [
    "https://instagram.com/cerveceriaeuropa",
    "https://www.google.com/maps/..."
  ]
}
```

#### 3.3.2 Menu + MenuItem (pagina /carta)
```json
{
  "@context": "https://schema.org",
  "@type": "Menu",
  "name": "Carta Cerveceria Europa",
  "hasMenuSection": [
    {
      "@type": "MenuSection",
      "name": "Cervezas",
      "hasMenuItem": [
        {
          "@type": "MenuItem",
          "name": "IPA Artesana",
          "description": "Notas citricas y amargor equilibrado",
          "offers": {
            "@type": "Offer",
            "price": "4.50",
            "priceCurrency": "EUR"
          }
        }
      ]
    }
  ]
}
```

#### 3.3.3 BlogPosting (posts del blog)
```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{titulo}",
  "description": "{resumen}",
  "image": "{imagen_url}",
  "datePublished": "{publicado_at}",
  "author": {
    "@type": "Organization",
    "name": "Cerveceria Europa"
  }
}
```

#### 3.3.4 BreadcrumbList (todas las paginas internas)
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "/"},
    {"@type": "ListItem", "position": 2, "name": "Carta", "item": "/carta"},
    {"@type": "ListItem", "position": 3, "name": "Cervezas", "item": "/carta/cervezas"}
  ]
}
```

### 3.4 Sitemap XML

Generacion automatica con `spatie/laravel-sitemap` o similar:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://cerveceriaeuropa.es/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://cerveceriaeuropa.es/carta</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://cerveceriaeuropa.es/blog</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
  <!-- Posts del blog -->
  <!-- Paginas de categorias de carta -->
</urlset>
```

### 3.5 Open Graph y Twitter Cards

**Implementacion en layout publico:**
```blade
<!-- Open Graph -->
<meta property="og:site_name" content="Cerveceria Europa">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $ogImage ?? asset('og-default.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:locale" content="es_ES">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage ?? asset('og-default.jpg') }}">
```

### 3.6 Estrategia de contenidos para blog

#### Pilares de contenido
1. **Guia de estilos de cerveza** — IPA, Stout, Pilsner, Weiss, Sour...
2. **Maridaje** — Que cerveza con que plato
3. **Cervecerias invitadas** — Colaboraciones con marcas artesanas
4. **Detras de barra** — Procesos, equipo, novedades del local
5. **Eventos** — Cata, presentaciones, noches tematicas

#### Internal linking
- Cada post enlaza a 2-3 productos de la carta relacionados
- La carta enlaza a posts del blog sobre maridaje
- Homepage muestra ultimos 3 posts del blog
- Sidebar del blog muestra "Cervezas mencionadas en este post"

### 3.7 Core Web Vitals — Optimizaciones

| Metrica | Objetivo | Estrategia |
|---------|----------|------------|
| LCP < 2.5s | **1.8s** | Hero image optimizada, preload font, SSR |
| INP < 200ms | **<100ms** | Alpine.js ligero, sin frameworks pesados |
| CLS < 0.1 | **<0.05** | Dimensiones explicitas en imagenes, font-display swap |

#### Tecnicas especificas:
1. **Imagenes**: `srcset` + `sizes`, WebP con fallback, lazy loading nativo
2. **Fuentes**: `font-display: swap`, preload de fuentes criticas, subset de caracteres
3. **CSS**: Critical CSS inline, resto diferido, purge con Tailwind
4. **JS**: Defer en scripts, Alpine.js solo donde se necesita
5. **Caching**: Cache-Control headers, ETag, stale-while-revalidate
6. **CDN**: Cloudflare o similar para assets estaticos

### 3.8 SEO On-Page

#### Estructura de headings
```
Homepage:
  H1: Cerveceria Europa — Cervezas artesanas en Sevilla
  H2: Fuera de carta
  H2: Cervezas para descubrir y platos para compartir
  H3: (nombres de productos destacados)
  H2: En el local

Carta:
  H1: Carta
  H2: (nombre de categoria: Cervezas, Tapas...)
  H3: (nombre de subcategoria si existe)
  H4: (nombre de producto)

Blog post:
  H1: Titulo del post
  H2: (secciones del contenido)
  H3: (subsecciones)
```

#### URLs amigables
- Slugs en espanol: `/carta/cervezas-artesanas`
- Sin stop words innecesarias
- Lowercase, guiones como separador

#### Internal linking
- Breadcrumbs en todas las paginas internas
- "Productos relacionados" en cada item de carta
- "Articulos relacionados" en posts del blog
- Footer con enlaces a secciones principales

### 3.9 SEO Local

1. **Google Business Profile** — Optimizado con fotos, horarios, menu
2. **NAP consistente** — Nombre, direccion, telefono igual en toda la web
3. **Embed de Google Maps** en pagina de contacto
4. **Resenas** — Widget de Google Reviews en homepage (futuro)
5. **Pagina de contacto** con schema `LocalBusiness`

---

## 4. IMPLEMENTACION — FASES

### Fase 1: Fundaciones (Semana 1-2)
- [ ] Configurar nueva paleta de colores en Tailwind
- [ ] Instalar fuentes (Bebas Neue, Inter, JetBrains Mono)
- [ ] Crear tokens de diseño en CSS custom properties
- [ ] Configurar sistema de espaciado y bordes
- [ ] Crear componentes base: Button, Badge, Card

### Fase 2: Layout y navegacion (Semana 2-3)
- [ ] Redisenar header con glassmorphism
- [ ] Crear footer nuevo con 4 columnas
- [ ] Implementar menu mobile con animacion
- [ ] Crear sistema de breadcrumbs

### Fase 3: Homepage (Semana 3-4)
- [ ] Nuevo hero con imagen/video de fondo
- [ ] Animacion de burbujas CSS
- [ ] Seccion "Fuera de carta" con cards nuevas
- [ ] Seccion de experiencia (3 columnas)

### Fase 4: Carta (Semana 4-5)
- [ ] Tabs de categorias con scroll sticky
- [ ] Cards de producto redisenadas
- [ ] Sistema de badges (alergenos, stock, destacado)
- [ ] Vista detalle de producto (modal o pagina)

### Fase 5: Blog (Semana 5-6)
- [ ] Layout de posts redisenado
- [ ] Pagina de post individual con tipografia editorial
- [ ] Sidebar con categorias y posts relacionados
- [ ] Sistema de internal linking automatico

### Fase 6: SEO tecnico (Semana 6-7)
- [ ] Implementar Schema.org (Restaurant, Menu, BlogPosting)
- [ ] Generar sitemap XML automatico
- [ ] Meta tags dinamicos por pagina
- [ ] Open Graph + Twitter Cards
- [ ] Optimizar Core Web Vitals
- [ ] Configurar robots.txt avanzado

### Fase 7: Testing y lanzamiento (Semana 7-8)
- [ ] Testing en dispositivos reales
- [ ] Lighthouse audit (objetivo: 95+ en todo)
- [ ] Testing de accesibilidad (WCAG 2.1 AA)
- [ ] Validacion de schema markup
- [ ] Lanzamiento gradual

---

## 5. STACK TECNICO PROPUESTO

| Capa | Tecnologia | Motivo |
|------|-----------|--------|
| Framework | Laravel 12 + Blade | Ya en uso, SSR nativo (bueno para SEO) |
| CSS | Tailwind CSS 4 | Ya en uso, utility-first, purge automatico |
| JS | Alpine.js | Ya en uso, ligero, perfecto para interactividad |
| Fuentes | Google Fonts (self-hosted) | Performance + privacidad |
| Imagenes | Intervention Image + WebP | Optimizacion automatica |
| SEO | spatie/laravel-sitemap | Sitemap automatico |
| Schema | JSON-LD inline en Blade | Simple, sin dependencias |
| Analytics | Plausible o Fathom | Privado, ligero, GDPR-friendly |

---

## 6. METRICAS DE EXITO

| Metrica | Actual | Objetivo |
|---------|--------|----------|
| Lighthouse Performance | TBD | 95+ |
| Lighthouse SEO | TBD | 100 |
| Lighthouse Accessibility | TBD | 95+ |
| LCP | TBD | < 1.8s |
| INP | TBD | < 100ms |
| CLS | TBD | < 0.05 |
| Posicion "cerveceria artesana Sevilla" | TBD | Top 3 |
| Posicion "bar cervezas Sevilla" | TBD | Top 5 |
| Trafico organico mensual | TBD | +50% en 3 meses |

---

## 7. REFERENCIAS VISUALES

### Inspiracion
- **BrewDog** — Estetica industrial, tipografia bold
- **To Øl** — Diseño danes minimalista con toques de color
- **Mikkeller** — Tipografia experimental, identidad fuerte
- **Garage Beer Co (Barcelona)** — Web de cerveceria artesana local
- **La Cibeles (Madrid)** — Referencia espana de cerveceria con web moderna

### Paleta de referencia
```
Espuma:     #FAF7F0  ████████
Pilsner:    #FFECB3  ████████
Amber:      #D4A017  ████████
Copper:     #B8860B  ████████
Barrel:     #8B6F47  ████████
Stout:      #2B1A00  ████████
Hops:       #4A7C59  ████████
```
