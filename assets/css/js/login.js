const form    = document.getElementById('login-form');
        const btn     = document.getElementById('btn-submit');
        const errBox  = document.getElementById('error-msg');
        const errText = document.getElementById('error-text');

        function showError(msg) {
            errText.textContent = msg;
            errBox.classList.add('show');
        }

        function hideError() {
            errBox.classList.remove('show');
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            hideError();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                showError('Por favor completa todos los campos.');
                return;
            }

            // Estado de carga
            btn.classList.add('loading');
            btn.innerHTML = '<span class="loading-spinner"></span> Ingresando...';

            // Simula petición (reemplazar con fetch/AJAX real)
            setTimeout(() => {
                btn.classList.remove('loading');
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';

                if (username === 'admin' && password === '123') {
                    window.location.href = 'views/inicio.php';
                }if (username === 'seller' && password === '123') {
                    window.location.href = 'views/inicio.php';  
                } else {
                    showError('Usuario o contraseña incorrectos.');
                    document.getElementById('password').value = '';
                    document.getElementById('password').focus();
                }
            }, 800);
        });

        // Ocultar error al escribir
        document.getElementById('username').addEventListener('input', hideError);
        document.getElementById('password').addEventListener('input', hideError);