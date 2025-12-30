import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function() {
    const hub = document.getElementById('CommunicationHub');
    const openBtn = document.getElementById('openHub');
    const closeBtn = document.getElementById('hubClose');

    if (hub && openBtn && closeBtn) {
        // Open/Close hub
        openBtn.addEventListener('click', () => hub.style.display = 'flex');
        closeBtn.addEventListener('click', () => hub.style.display = 'none');

        // Tab switching
        document.querySelectorAll('.hub-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.hub-content > div').forEach(content => content.style.display = 'none');
                const selected = document.getElementById(tab.dataset.tab);
                if(selected) selected.style.display = 'block';
            });
        });

        // Show first tab by default
        const firstTab = document.querySelector('.hub-tab');
        if(firstTab) firstTab.click();
    }
});
