<script>
    (function() {
        const theme = localStorage.getItem('theme') || 'light';
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();

    window.toggleTheme = function() {
        const isDark = document.documentElement.classList.toggle('dark');
        const newTheme = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', newTheme);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: newTheme }));
    };

    // For compatibility with older code that might not use window. prefix
    window.toggleThemeAlias = window.toggleTheme;
</script>
