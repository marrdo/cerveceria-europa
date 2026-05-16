import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                /* v2: Bebas Neue para display, Inter para body, JetBrains Mono para precios */
                display: ['"Bebas Neue"', ...defaultTheme.fontFamily.serif],
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                mono:    ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                /* === Tokens existentes (admin) — NO TOCAR === */
                background: 'rgb(var(--color-background) / <alpha-value>)',
                foreground: 'rgb(var(--color-foreground) / <alpha-value>)',
                card: 'rgb(var(--color-card) / <alpha-value>)',
                'card-foreground': 'rgb(var(--color-card-foreground) / <alpha-value>)',
                primary: 'rgb(var(--color-primary) / <alpha-value>)',
                'primary-foreground': 'rgb(var(--color-primary-foreground) / <alpha-value>)',
                secondary: 'rgb(var(--color-secondary) / <alpha-value>)',
                'secondary-foreground': 'rgb(var(--color-secondary-foreground) / <alpha-value>)',
                muted: 'rgb(var(--color-muted) / <alpha-value>)',
                'muted-foreground': 'rgb(var(--color-muted-foreground) / <alpha-value>)',
                accent: 'rgb(var(--color-accent) / <alpha-value>)',
                'accent-foreground': 'rgb(var(--color-accent-foreground) / <alpha-value>)',
                destructive: 'rgb(var(--color-destructive) / <alpha-value>)',
                'destructive-foreground': 'rgb(var(--color-destructive-foreground) / <alpha-value>)',
                border: 'rgb(var(--color-border) / <alpha-value>)',
                input: 'rgb(var(--color-input) / <alpha-value>)',
                ring: 'rgb(var(--color-ring) / <alpha-value>)',
                warning: 'rgb(var(--color-warning) / <alpha-value>)',
                'warning-foreground': 'rgb(var(--color-warning-foreground) / <alpha-value>)',
                success: 'rgb(var(--color-success) / <alpha-value>)',
                'success-foreground': 'rgb(var(--color-success-foreground) / <alpha-value>)',
                sidebar: 'rgb(var(--color-sidebar) / <alpha-value>)',
                'sidebar-foreground': 'rgb(var(--color-sidebar-foreground) / <alpha-value>)',
                'sidebar-accent': 'rgb(var(--color-sidebar-accent) / <alpha-value>)',
                'sidebar-border': 'rgb(var(--color-sidebar-border) / <alpha-value>)',
                'public-background': 'rgb(var(--color-public-background) / <alpha-value>)',
                'public-foreground': 'rgb(var(--color-public-foreground) / <alpha-value>)',
                'public-surface': 'rgb(var(--color-public-surface) / <alpha-value>)',
                'public-muted': 'rgb(var(--color-public-muted) / <alpha-value>)',
                'public-primary': 'rgb(var(--color-public-primary) / <alpha-value>)',
                'public-border': 'rgb(var(--color-public-border) / <alpha-value>)',

                /* === Tokens NUEVOS v2 (web pública rompedora) === */
                stout:          'rgb(var(--color-stout) / <alpha-value>)',
                tile:           'rgb(var(--color-tile) / <alpha-value>)',
                ink:            'rgb(var(--color-ink) / <alpha-value>)',
                'ink-mute':     'rgb(var(--color-ink-mute) / <alpha-value>)',
                'amber-bright': 'rgb(var(--color-amber-bright) / <alpha-value>)',
                'amber-glow':   'rgb(var(--color-amber-glow) / <alpha-value>)',
                'hops-bright':  'rgb(var(--color-hops-bright) / <alpha-value>)',
            },
            animation: {
                'v2-pulse-dot': 'v2PulseDot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'v2-marquee':   'v2Marquee 38s linear infinite',
            },
            keyframes: {
                v2PulseDot: {
                    '0%':   { boxShadow: '0 0 0 0 rgba(93,155,110,.7)' },
                    '70%':  { boxShadow: '0 0 0 10px rgba(93,155,110,0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(93,155,110,0)' },
                },
                v2Marquee: {
                    '0%':   { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
            boxShadow: {
                'v2-sticker': '-4px 4px 0 rgb(var(--color-amber-bright))',
                'v2-amber-glow': '0 0 24px rgba(227,161,58,.25)',
            },
            transitionTimingFunction: {
                'v2-out': 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
        },
    },

    plugins: [forms],
};
