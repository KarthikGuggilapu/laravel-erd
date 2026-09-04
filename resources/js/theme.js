export function createThemeManager() {
    const root = document.documentElement;
    const button = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');

    function current() {
        return root.dataset.theme === 'dark' ? 'dark' : 'light';
    }

    function apply(theme) {
        root.dataset.theme = theme;
        localStorage.setItem('laravel-erd-theme', theme);

        icon.textContent = theme === 'dark' ? '☀' : '☾';
        label.textContent = theme === 'dark' ? 'Light' : 'Dark';
        button.title = theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
    }

    function bind() {
        const stored = localStorage.getItem('laravel-erd-theme');
        const preferred = stored || (
            window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark'
                : 'light'
        );

        apply(preferred);

        button.addEventListener('click', () => {
            apply(current() === 'dark' ? 'light' : 'dark');
        });
    }

    return { bind, current, apply };
}
