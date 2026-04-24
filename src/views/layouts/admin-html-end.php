<script>
    const currentDateElement = document.getElementById('current-date');
    if (currentDateElement) {
        currentDateElement.textContent = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
    }

    const sidebarClose = document.querySelector('.sidebar-close');
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.remove('active');
            }
        });
    }

    document.addEventListener('click', function(event) {
        const target = event.target;
        if (!target || !target.classList) return;

        const isModalOverlay = target.classList.contains('modal') || target.classList.contains('modal-overlay');
        if (!isModalOverlay) return;

        if (target.classList.contains('active')) {
            target.classList.remove('active');
        }

        if (target.style && (target.style.display === 'flex' || target.style.display === 'block')) {
            target.style.display = 'none';
        }
    });
</script>
</body>
</html>
