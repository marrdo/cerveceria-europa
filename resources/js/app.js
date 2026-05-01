import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_STORAGE_KEY = 'cerveceria-theme-preference';

const getStoredThemePreference = () => {
    const storedPreference = window.localStorage.getItem(THEME_STORAGE_KEY);

    if (['light', 'dark', 'system'].includes(storedPreference)) {
        return storedPreference;
    }

    return 'system';
};

let themePreference = getStoredThemePreference();

const getSystemTheme = () =>
    window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

const resolveTheme = () =>
    themePreference === 'system' ? getSystemTheme() : themePreference;

const applyTheme = () => {
    const resolvedTheme = resolveTheme();
    const isDarkTheme = resolvedTheme === 'dark';

    document.documentElement.classList.toggle('dark', isDarkTheme);
    document.documentElement.dataset.theme = resolvedTheme;
    document.documentElement.style.colorScheme = resolvedTheme;

    window.dispatchEvent(
        new CustomEvent('theme:change', {
            detail: {
                preference: themePreference,
                resolvedTheme,
            },
        }),
    );
};

window.__themeControl = {
    getPreference: () => themePreference,
    getResolvedTheme: () => resolveTheme(),
    setPreference: (nextPreference) => {
        themePreference = ['light', 'dark', 'system'].includes(nextPreference)
            ? nextPreference
            : 'system';

        window.localStorage.setItem(THEME_STORAGE_KEY, themePreference);
        applyTheme();
    },
};

const systemThemeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

if (typeof systemThemeMediaQuery.addEventListener === 'function') {
    systemThemeMediaQuery.addEventListener('change', () => {
        if (themePreference === 'system') {
            applyTheme();
        }
    });
}

const initializeThemeToggles = () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const icon = button.querySelector('[data-theme-toggle-icon]');
        const label = button.querySelector('[data-theme-label]');

        if (!icon || !label) {
            return;
        }

        const labelByPreference = {
            system: 'Sistema',
            light: 'Claro',
            dark: 'Oscuro',
        };

        const renderThemeToggle = () => {
            const preference = window.__themeControl.getPreference();
            const resolvedTheme = window.__themeControl.getResolvedTheme();
            const modeLabel = labelByPreference[preference] ?? 'Sistema';
            const resolvedLabel = resolvedTheme === 'dark' ? 'oscuro' : 'claro';
            const text =
                preference === 'system'
                    ? `Tema: ${modeLabel} (${resolvedLabel})`
                    : `Tema: ${modeLabel}`;

            label.textContent = text;
            button.setAttribute('aria-label', `${text}. Pulsa para cambiar.`);
            button.setAttribute('title', text);
            button.setAttribute('aria-pressed', String(resolvedTheme === 'dark'));
            icon.classList.toggle('is-dark', resolvedTheme === 'dark');
        };

        button.addEventListener('click', () => {
            const nextTheme =
                window.__themeControl.getResolvedTheme() === 'dark' ? 'light' : 'dark';

            window.__themeControl.setPreference(nextTheme);
            renderThemeToggle();
        });

        window.addEventListener('theme:change', renderThemeToggle);
        renderThemeToggle();
    });
};

Alpine.data('themeToggle', () => ({
    dark: resolveTheme() === 'dark',
    toggle() {
        const nextTheme = resolveTheme() === 'dark' ? 'light' : 'dark';

        window.__themeControl.setPreference(nextTheme);
        this.dark = resolveTheme() === 'dark';
    },
}));

applyTheme();
Alpine.start();
initializeThemeToggles();
