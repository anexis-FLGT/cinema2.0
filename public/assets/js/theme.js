/**
 * Система переключения темы
 * Применяется сразу при загрузке, чтобы избежать мигания
 */

(function() {
    'use strict';

    // Получаем сохраненную тему из localStorage или используем темную по умолчанию
    const getStoredTheme = () => {
        const stored = localStorage.getItem('theme');
        return stored || 'dark';
    };
    
    // Устанавливаем тему
    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateToggleButton(theme);
    };

    // Обновляем иконку кнопки переключения
    const updateToggleButton = (theme) => {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (theme === 'light') {
                icon.className = 'bi bi-moon-fill';
                toggleBtn.setAttribute('title', 'Переключить на темную тему');
            } else {
                icon.className = 'bi bi-sun-fill';
                toggleBtn.setAttribute('title', 'Переключить на светлую тему');
            }
        }
    };

    // Переключение темы
    const toggleTheme = () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    };

    // Применяем тему сразу, до загрузки DOM (чтобы избежать мигания)
    const storedTheme = getStoredTheme();
    document.documentElement.setAttribute('data-theme', storedTheme);

    // Инициализация при загрузке страницы
    const initTheme = () => {
        const storedTheme = getStoredTheme();
        setTheme(storedTheme);
        
        // Добавляем обработчик клика на кнопку переключения
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleTheme);
        }
    };

    // Запускаем инициализацию после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();

