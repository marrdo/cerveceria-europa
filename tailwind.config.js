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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
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
            },
        },
    },

    plugins: [forms],
};
