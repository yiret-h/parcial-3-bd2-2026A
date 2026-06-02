/* huellitas-shared.js */

// ── Dark mode ──
(function() {
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
    }
})();

document.addEventListener('DOMContentLoaded', function() {

    // Toggle dark mode
    const btn = document.getElementById('darkModeToggle');
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? '1' : '0');
        });
    }

    // User dropdown toggle
    const wrapper = document.querySelector('.user-menu-wrapper');
    if (wrapper) {
        wrapper.querySelector('.user-menu-trigger').addEventListener('click', function(e) {
            e.stopPropagation();
            wrapper.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            wrapper.classList.remove('open');
        });
    }
});