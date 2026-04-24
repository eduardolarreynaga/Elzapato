function openDevModal() {
    const modal = document.getElementById('devModal');
    if (!modal) return;
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
}

function closeDevModal() {
    const modal = document.getElementById('devModal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
}

window.addEventListener('click', function (event) {
    const modal = document.getElementById('devModal');
    if (modal && event.target === modal) {
        closeDevModal();
    }
});

window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeDevModal();
    }
});
